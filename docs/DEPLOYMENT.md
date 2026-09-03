# Panduan Deployment Produksi

Panduan ringkas menjalankan aplikasi ini di server produksi (VPS/shared
hosting dengan akses shell). Lihat `docs/PERENCANAAN.md` untuk keputusan
arsitektur (§ 10) dan strategi keamanan (§ 7) yang mendasari beberapa langkah
di bawah.

## 1. Kebutuhan Server

- PHP 8.3+ dengan ekstensi: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`,
  `gd` atau `imagick` (untuk resize gambar produk), `bcmath`, `intl`.
- MySQL 8+ (atau MariaDB 10.6+).
- Composer 2, Node.js 20+ & npm (hanya dibutuhkan saat build, tidak di
  runtime — hasil `npm run build` bisa di-deploy sebagai artefak statis).
- Web server: Nginx (direkomendasikan) atau Apache, dengan PHP-FPM.
- Akses shell untuk menjalankan migrasi & `artisan` command sekali per rilis.

## 2. Checklist `.env` Produksi

Salin dari `.env.example`, lalu pastikan:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-toko-anda.com

DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...          # kredensial kuat, jangan commit ke repo

SESSION_SECURE_COOKIE=true   # wajib di HTTPS, cookie sesi tidak terkirim lewat HTTP
SESSION_HTTP_ONLY=true       # default sudah true, jangan diubah
SESSION_SAME_SITE=lax

MAIL_MAILER=smtp             # atau ses/postmark/resend — jangan pakai 'log' di produksi
MAIL_FROM_ADDRESS=...

MIDTRANS_SERVER_KEY=...      # kredensial PRODUCTION dari Midtrans Dashboard
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=true

RECAPTCHA_SITE_KEY=...       # opsional, aktifkan reCAPTCHA v3 di form registrasi
RECAPTCHA_SECRET_KEY=...
```

**Jangan pernah** meng-commit `.env` ke repository. Backup `.env` produksi
secara terpisah (mis. password manager tim / secret manager), bukan di git.

## 3. Langkah Deploy (rilis pertama & setiap update)

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build

php artisan migrate --force      # --force wajib karena APP_ENV=production
php artisan storage:link         # sekali saja, wajib agar gambar produk/banner terbaca

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setelah `config:cache`, perubahan `.env` tidak akan terbaca sampai
`php artisan config:cache` dijalankan ulang — jangan lupa ini setiap kali
`.env` berubah.

### Zero-downtime sederhana

Untuk shared hosting/VPS tanpa orchestrator: deploy ke direktori baru
(`releases/<timestamp>`), jalankan langkah di atas, lalu pindahkan symlink
`current` ke rilis baru setelah migrasi sukses. Jika tidak memungkinkan,
minimal jalankan migrasi di luar jam sibuk dan pantau log setelah deploy.

## 4. Web Server (Nginx + PHP-FPM)

```nginx
server {
    listen 443 ssl http2;
    server_name domain-toko-anda.com;
    root /var/www/olshop/public;

    ssl_certificate     /etc/letsencrypt/live/domain-toko-anda.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain-toko-anda.com/privkey.pem;

    index index.php;
    client_max_body_size 20m;   # unggah gambar produk/bukti transfer

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}

server {
    listen 80;
    server_name domain-toko-anda.com;
    return 301 https://$host$request_uri;
}
```

Gunakan `certbot --nginx` (Let's Encrypt) untuk sertifikat HTTPS — **wajib**,
lihat `docs/PERENCANAAN.md` § 7 (session cookie hanya aman lewat HTTPS).

## 5. Webhook Midtrans

Setelah domain live dengan HTTPS, daftarkan
`https://domain-toko-anda.com/webhook/midtrans` sebagai **Payment
Notification URL** di Midtrans Dashboard (Settings → Configuration). Endpoint
ini sudah diverifikasi signature server-side dan idempotent (lihat
`app/Http/Controllers/Public/MidtransNotificationController.php`), serta
di-throttle 60 request/menit untuk mencegah banjir notifikasi.

## 6. Queue & Notifikasi Email

Semua notifikasi (`App\Notifications\*`: update status pesanan, update
komplain, pesanan baru, stok menipis) memakai `ShouldQueue`, jadi **queue
worker wajib dijalankan** di produksi — tanpanya email/notifikasi in-app
tidak akan pernah terkirim (job hanya menumpuk di tabel `jobs`, tidak
dijalankan otomatis). Jalankan:

```bash
php artisan queue:work --tries=3 --daemon
```

di bawah supervisor (systemd/Supervisor) agar otomatis restart bila crash
atau server reboot. Setelah deploy kode baru, restart worker (`php artisan
queue:restart`) supaya proses lama yang masih memakai kode versi sebelumnya
berhenti dan digantikan yang baru.

Di lingkungan development/testing, `QUEUE_CONNECTION=sync` (lihat
`phpunit.xml`) atau menjalankan `php artisan queue:work` manual sudah cukup
— job langsung dieksekusi tanpa perlu proses worker terpisah.

## 7. Backup

- **Database**: backup harian (`mysqldump` terjadwal via cron, disimpan di
  luar server yang sama — mis. S3/object storage) — data pesanan & pembayaran
  adalah data paling kritikal untuk direstore bila terjadi masalah.
- **Storage**: `storage/app/public` (gambar produk, banner, bukti transfer)
  di luar git — sertakan dalam strategi backup terpisah dari database.
- Uji proses restore secara berkala, bukan hanya proses backup-nya.

## 8. Setelah Deploy Pertama

1. Login sebagai Super Admin (ubah password default seeder segera).
2. Isi **Pengaturan Toko** (`/admin/pengaturan`): nama toko, kontak,
   rekening transfer manual, pajak, ambang batas stok menipis.
3. Aktifkan **2FA** (`/akun-saya/keamanan`) — wajib untuk akun Super
   Admin/Admin, sistem akan otomatis mengarahkan ke halaman ini sampai
   diaktifkan (lihat `app/Http/Middleware/EnsureTwoFactorEnabled.php`).
4. Verifikasi email transaksional benar-benar terkirim (bukan `MAIL_MAILER=log`).
5. Lakukan satu transaksi uji end-to-end (tambah produk → checkout → bayar
   sandbox Midtrans/transfer manual → admin proses → email diterima).

## 9. Observability Dasar

- Log aplikasi: `storage/logs/laravel.log` — pantau khususnya error dari
  `MidtransNotificationController` (kegagalan verifikasi signature/webhook).
- Log aktivitas admin kritikal (hapus produk/kupon, verifikasi/tolak
  pembayaran, batalkan pesanan, ubah pengaturan) tersimpan di database, bisa
  dilihat Super Admin di `/admin/log-aktivitas`.
- Pertimbangkan layanan log/error tracking eksternal (mis. Sentry, Flare)
  untuk produksi skala lebih besar — belum diintegrasikan di kode saat ini.
