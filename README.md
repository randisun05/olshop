# Toko Online

Platform toko online multi-guna berbasis **Laravel + Inertia.js + Vue 3** (backend & frontend
menyatu dalam satu aplikasi Laravel), dengan MySQL sebagai basis data.

Lihat [`docs/PERENCANAAN.md`](docs/PERENCANAAN.md) untuk rencana arsitektur lengkap: skema
database, daftar fitur, strategi keamanan, dan roadmap pengembangan.

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

## Struktur Kode

- `app/Http/Controllers/{Admin,Customer,Storefront}` — controller dipisah per konteks akses
- `app/Services` — logika bisnis (mis. `ProductService` untuk create/update produk beserta gambar & varian)
- `resources/js/Pages/{Admin,Customer,Storefront,Auth}` — halaman Inertia per konteks yang sama
- `resources/js/Layouts` — layout bersama per area (Admin/Customer/Storefront)

Detail prinsip arsitektur (Service layer, Policy, dsb.) ada di `docs/PERENCANAAN.md`.

## Fitur yang Sudah Tersedia (Fase 0-4)

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

Untuk mengaktifkan Midtrans, isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` di `.env`
dengan kredensial sandbox/production dari [Midtrans Dashboard](https://dashboard.midtrans.com/),
lalu daftarkan URL `/webhook/midtrans` sebagai Payment Notification URL di sana. Untuk email
notifikasi sungguhan, isi `MAIL_MAILER` dkk di `.env` (default `log`, notifikasi hanya dicatat
ke `storage/logs/laravel.log`).

Belum tersedia (lihat roadmap di `docs/PERENCANAAN.md`): laporan/dashboard analitik, manajemen
konten (banner/halaman statis).
