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

## Fitur yang Sudah Tersedia (Fase 0-1)

- Autentikasi lengkap (register, verifikasi email, login, reset password, 2FA/passkeys)
- RBAC granular via role + permission (Super Admin, Admin, Staff Gudang, Staff CS, Customer)
- Manajemen katalog di admin: kategori (bertingkat), brand, atribut & nilai atribut, produk
  dengan banyak gambar dan banyak varian (kombinasi atribut, harga & stok per varian)
- Katalog publik: pencarian, filter kategori, sorting, halaman detail produk dengan pemilihan varian

Belum tersedia (lihat roadmap di `docs/PERENCANAAN.md`): keranjang & checkout, pembayaran,
manajemen pesanan, diskon/kupon/promo, review, laporan.
