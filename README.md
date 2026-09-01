# Toko Online

Platform toko online multi-guna berbasis **Laravel + Inertia.js + Vue 3** (backend & frontend
menyatu dalam satu aplikasi Laravel), dengan MySQL sebagai basis data.

Lihat [`docs/PERENCANAAN.md`](docs/PERENCANAAN.md) untuk rencana arsitektur lengkap: skema
database, daftar fitur, strategi keamanan, dan roadmap pengembangan. Untuk deploy ke produksi,
lihat [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md); untuk hasil uji beban ringan, lihat
[`docs/LOAD_TEST.md`](docs/LOAD_TEST.md).

## Stack

- Laravel 13 (PHP 8.3+)
- Inertia.js + Vue 3, Vite, Tailwind CSS 4
- MySQL 8
- Laravel Fortify (autentikasi, 2FA) + `spatie/laravel-permission` (role & permission)

## Instalasi Lokal

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Sesuaikan kredensial MySQL di .env, lalu:
php artisan migrate --seed
php artisan storage:link   # wajib, agar gambar produk/brand bisa diakses browser

npm run build   # atau `npm run dev` untuk mode pengembangan
php artisan serve
```

Seeder membuat dua akun contoh (password: `password`):

- `admin@tokoonline.test` — role **Super Admin**
- `customer@tokoonline.test` — role **Customer**

2FA wajib untuk Super Admin/Admin: setelah login sebagai `admin@tokoonline.test`, Anda akan
otomatis diarahkan ke `/akun-saya/keamanan` untuk mengaktifkan 2FA sebelum bisa mengakses halaman
admin lainnya — ini yang diharapkan (bukan bug), lihat § Pengerasan Keamanan di bawah.

## Struktur Kode

- `app/Http/Controllers/{Admin,Customer,Storefront}` — controller dipisah per konteks akses
- `app/Services` — logika bisnis (mis. `ProductService` untuk create/update produk beserta gambar & varian)
- `resources/js/Pages/{Admin,Customer,Storefront,Auth}` — halaman Inertia per konteks yang sama
- `resources/js/Layouts` — layout bersama per area (Admin/Customer/Storefront)

Detail prinsip arsitektur (Service layer, Policy, dsb.) ada di `docs/PERENCANAAN.md`.

## Fitur yang Sudah Tersedia (Fase 0-7)

- Autentikasi lengkap (register, verifikasi email, login, reset password, 2FA/passkeys)
- RBAC granular via role + permission (Super Admin, Admin, Staff Gudang, Staff CS, Customer)
- Manajemen katalog di admin: kategori (bertingkat), brand, atribut & nilai atribut, produk
  dengan banyak gambar dan banyak varian (kombinasi atribut, harga & stok per varian)
- Katalog publik: pencarian, filter kategori, sorting, halaman detail produk dengan pemilihan varian
- Keranjang belanja (tamu & login, otomatis digabung saat tamu login)
- Checkout: alamat baru/tersimpan, pilih wilayah pengiriman (tarif diatur admin), catatan pesanan
- Pembayaran: Midtrans Snap (kartu/VA/e-wallet/QRIS) atau transfer bank manual dengan unggah bukti
- Pengurangan stok aman (row locking) saat checkout, dikembalikan otomatis bila pembayaran
  gagal/ditolak/dibatalkan
- Manajemen pesanan admin: proses pesanan (dibayar → diproses → dikirim + input resi → selesai),
  batalkan pesanan dengan pengembalian stok otomatis
- Notifikasi email ke pembeli (terdaftar maupun tamu) di setiap perubahan status pesanan
- Pelanggan bisa konfirmasi sendiri "pesanan diterima" setelah status dikirim
- Lacak pesanan untuk tamu (nomor pesanan + email), riwayat pesanan untuk pelanggan login
- Invoice PDF, riwayat status pesanan lengkap dengan siapa yang mengubah
- Admin: verifikasi/tolak pembayaran transfer manual, kelola wilayah & tarif pengiriman
- Kupon/diskon: admin kelola kode kupon (persen/nominal, minimum belanja, kuota, masa berlaku),
  pelanggan menerapkannya saat checkout dengan validasi & perhitungan diskon di server
- Wishlist: pelanggan login bisa menyimpan/menghapus produk favorit dan melihatnya di halaman akun
- Review & rating: pelanggan bisa mengulas produk yang sudah dibeli & pesanannya selesai (satu
  ulasan per item pesanan), rating rata-rata tampil di katalog & halaman detail produk, admin bisa
  moderasi (hapus) ulasan
- Dashboard analitik admin: ringkasan omzet & pesanan bulan berjalan, grafik tren penjualan 30 hari
  (Chart.js), produk terlaris, dan daftar varian stok menipis
- Laporan: penjualan per periode (filter tanggal), produk terlaris per periode, dan stok — masing-
  masing bisa diekspor ke Excel (`maatwebsite/excel`), laporan penjualan juga bisa diekspor PDF
- Manajemen konten: banner beranda (gambar, tautan, urutan tampil) dan halaman statis (mis. FAQ,
  syarat & ketentuan) yang bisa diakses publik di `/halaman/{slug}` dan tampil otomatis di footer
- Pengaturan toko: identitas (nama, email, telepon, alamat), rekening bank untuk transfer manual,
  pajak, dan ambang batas stok menipis — tersimpan sebagai key-value dan di-cache
- Pengerasan keamanan: header keamanan dasar (X-Frame-Options, X-Content-Type-Options, dst) di
  semua respons, rate limiting pada endpoint sensitif (checkout, cek kupon, tulis ulasan, wishlist,
  webhook Midtrans), **2FA wajib** untuk Super Admin/Admin (halaman aktivasi di
  `/akun-saya/keamanan`, otomatis diarahkan ke sana sampai diaktifkan), log aktivitas admin untuk
  aksi kritikal (hapus produk/kupon/banner/halaman, verifikasi/tolak pembayaran, batalkan pesanan,
  ubah pengaturan toko — bisa dilihat Super Admin di `/admin/log-aktivitas`), dan hook reCAPTCHA v3
  opsional di form registrasi (tidak aktif tanpa `RECAPTCHA_SECRET_KEY`)
- Retur/komplain: pelanggan bisa mengajukan retur/komplain (dengan foto bukti opsional) untuk
  pesanan yang sudah selesai, satu pengajuan terbuka per pesanan, admin/Staff CS meninjau &
  merespons (proses/selesai/tolak + catatan) dan pelanggan mendapat notifikasi email
- Login sosial: tombol "Masuk/Daftar dengan Google" opsional (tidak aktif tanpa
  `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET`) — akun baru otomatis dibuat & email terverifikasi,
  atau ditautkan ke akun existing bila emailnya sudah terdaftar

Untuk mengaktifkan Midtrans, isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` di `.env`
dengan kredensial sandbox/production dari [Midtrans Dashboard](https://dashboard.midtrans.com/),
lalu daftarkan URL `/webhook/midtrans` sebagai Payment Notification URL di sana. Untuk email
notifikasi sungguhan, isi `MAIL_MAILER` dkk di `.env` (default `log`, notifikasi hanya dicatat
ke `storage/logs/laravel.log`).

Seluruh 7 fase di roadmap `docs/PERENCANAAN.md` § 9 sudah selesai, ditambah Fase 7 (retur/komplain
& login sosial) atas permintaan lanjutan. Satu-satunya item di § 6 yang masih terbuka: ekstensi
multi-vendor (skema sudah didesain agar mudah ditambah tanpa merombak struktur inti — lihat § 10).
