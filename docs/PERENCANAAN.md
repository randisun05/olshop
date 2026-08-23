# Perencanaan Proyek: Platform Toko Online Multi-Guna

Status: **Draft perencanaan (belum ada kode)**
Referensi arsitektur: `randisun05/asprov1` (Laravel 12 + Inertia + Vue 3, pola Admin/User/Public, service layer, Fortify+2FA, Midtrans, export Excel/PDF, QR code)

## 1. Tujuan Proyek

Membangun platform toko online yang:

- **Multi-guna** — bisa dipakai untuk berbagai jenis produk (fisik dengan varian, dan siap diperluas ke produk digital/jasa), bukan toko yang sangat spesifik ke satu kategori.
- **Usable** — alur belanja (browse → keranjang → checkout → pembayaran → tracking) simpel dan cepat untuk pembeli; panel admin jelas dan efisien untuk pengelola toko.
- **Gampang dikembangkan** — struktur modular (Controller + Service + Request + Policy per domain), penamaan konsisten, mudah menambah modul baru tanpa merombak yang lama.
- **Fitur lengkap** — mencakup katalog, keranjang, checkout, pembayaran, pengiriman, promo, review, hingga laporan.
- **Manajemen lengkap** — panel admin untuk semua entitas: produk, stok, pesanan, pembayaran, pengguna, konten, promo, laporan, pengaturan toko.
- **Aman** — otentikasi kuat, RBAC granular, validasi ketat, proteksi pembayaran, audit trail.

## 2. Tech Stack

| Layer | Pilihan | Alasan |
|---|---|---|
| Backend framework | Laravel 12 (PHP 8.2+) | Sesuai instruksi, matang, ekosistem luas |
| Frontend | Vue 3 + Inertia.js | BE-FE menyatu dalam satu repo Laravel, tanpa API terpisah, SPA-like UX |
| Build tool | Vite | Default Laravel modern, cepat |
| Database | MySQL 8 | Sesuai instruksi |
| Auth | Laravel Fortify | Login, register, reset password, verifikasi email, 2FA opsional untuk admin (pola sama dgn asprov1) |
| Otorisasi (role/permission) | `spatie/laravel-permission` | Lebih granular & scalable dibanding kolom `role` string biasa (yang dipakai asprov1) — penting untuk "manajemen lengkap" yang mudah dikembangkan (tambah role/permission baru tanpa migrasi ulang skema inti) |
| Payment gateway | Midtrans Snap | Sudah terbukti dipakai di asprov1, populer & lengkap utk pasar Indonesia (VA, e-wallet, kartu, QRIS). Manual transfer + upload bukti sebagai fallback |
| Ongkos kirim | Mulai manual (admin set tarif per kota/berat), dengan titik ekstensi untuk integrasi RajaOngkir/Biteship di fase lanjut | Menghindari dependency eksternal yang mahal di MVP |
| Export/Import | `maatwebsite/excel` | Import produk massal, export laporan/pesanan |
| PDF (invoice/label) | `barryvdh/laravel-dompdf` | Invoice, packing list |
| Gambar produk | `intervention/image` | Resize/crop/optimasi gambar produk otomatis |
| Notifikasi | Laravel Notification (mail + database) | In-app notif & email, extensible ke WhatsApp/Fonnte nanti |
| Queue | Database driver (MVP) → Redis (produksi) | Kirim email, generate PDF, proses gambar di background |
| Cache | File (MVP) → Redis (produksi) | Cache katalog/kategori |
| Testing | Pest atau PHPUnit (feature test) | Uji alur kritikal: checkout, stok, pembayaran |
| CI | GitHub Actions | Lint (Pint) + test otomatis setiap push |

## 3. Prinsip Arsitektur

Monolit modular ala asprov1, per domain terdiri dari:

```
Controller (tipis, orkestrasi)
  -> FormRequest (validasi + otorisasi form)
  -> Service (logika bisnis: mis. CheckoutService, StockService, PaymentService)
  -> Model + Policy (data + aturan akses objek)
  -> Resource/Inertia props (bentuk data ke frontend)
```

Controller dipisah per konteks akses, meniru asprov1:

```
app/Http/Controllers/
  Admin/      -> panel pengelolaan toko (produk, pesanan, laporan, dst)
  Customer/   -> area akun pembeli (profil, alamat, riwayat pesanan)
  Storefront/ -> halaman publik (katalog, detail produk, keranjang, checkout)
  Api/        -> (opsional) endpoint untuk webhook (Midtrans) & mobile app di masa depan
```

Frontend Inertia mengikuti struktur yang sama:

```
resources/js/Pages/
  Admin/...
  Customer/...
  Storefront/...
resources/js/Components/   (komponen shared: Button, Modal, DataTable, dst)
resources/js/Layouts/      (AdminLayout, CustomerLayout, StorefrontLayout)
resources/js/Composables/  (useCart, useNotify, dst)
```

Backend business logic:

```
app/Services/     (CartService, CheckoutService, StockService, PaymentService, PromoService, ReportService)
app/Actions/       (Fortify actions + aksi kecil reusable)
app/Notifications/ (OrderPaid, OrderShipped, StockLow, dst)
app/Policies/       (ProductPolicy, OrderPolicy, dst — sejalan dgn spatie permission)
```

## 4. Manajemen Peran & Hak Akses

Role default (via `spatie/laravel-permission`, mudah tambah role baru dari admin):

- **Super Admin** — akses penuh termasuk pengaturan sistem & manajemen role.
- **Admin/Owner** — kelola produk, pesanan, promo, laporan, tapi tidak ubah pengaturan sistem inti.
- **Staff Gudang (Inventory)** — kelola stok, terima/proses pesanan, cetak label kirim.
- **Staff CS/Finance** — verifikasi pembayaran manual, tangani komplain/retur, lihat laporan keuangan.
- **Customer** — akses area akun pribadi (guard terpisah `web` biasa; admin/staff pakai kombinasi role, bukan guard terpisah, supaya satu tabel `users` — beda dengan asprov1 yang split `User` vs `Member`).

2FA (via Fortify) diwajibkan untuk role Super Admin & Admin (pola sama seperti asprov1: `EnsureTwoFactorIsEnabled` middleware).

## 5. Desain Basis Data (inti)

Nama tabel & field kunci (disederhanakan, migration detail dibuat saat implementasi):

**Identitas & Akses**
- `users` (id, name, email, password, phone, email_verified_at, two_factor_*, is_active)
- `roles`, `permissions`, `model_has_roles`, dst (dari spatie)
- `addresses` (user_id, label, penerima, telp, provinsi/kota/kecamatan, kode_pos, alamat_lengkap, is_default)

**Katalog**
- `categories` (id, parent_id nullable → mendukung kategori bertingkat, name, slug, image)
- `brands` (id, name, slug, logo)
- `products` (id, category_id, brand_id, name, slug, description, base_price, weight, is_active, is_featured)
- `product_images` (product_id, path, sort_order)
- `attributes` (id, name — mis. "Warna", "Ukuran")
- `attribute_values` (attribute_id, value)
- `product_variants` (product_id, sku, price, stock, attribute_value_ids/json atau pivot `variant_attribute_values`)
- `stock_movements` (variant_id, type: in/out/adjustment, qty, reference, note, created_by) — audit trail stok

**Transaksi**
- `carts`, `cart_items` (user_id/session_id, variant_id, qty) — dukung guest cart via session lalu merge saat login
- `orders` (order_number, user_id, status, subtotal, discount, shipping_cost, total, address snapshot, notes)
- `order_items` (order_id, product_id, variant_id, name snapshot, price snapshot, qty)
- `order_status_histories` (order_id, status, note, changed_by, created_at) — audit perjalanan status
- `payments` (order_id, method, provider_ref, amount, status, paid_at, proof_path utk transfer manual)
- `shipments` (order_id, courier, tracking_number, shipped_at, delivered_at)

**Promo & Interaksi**
- `coupons` (code, type: percent/fixed, value, min_purchase, quota, expired_at)
- `coupon_usages` (coupon_id, order_id, user_id)
- `reviews` (product_id, user_id, order_item_id, rating, comment, images) — hanya boleh review produk yang sudah dibeli & diterima
- `wishlists` (user_id, product_id)

**Konten & Sistem**
- `banners`, `pages` (CMS statis: tentang kami, syarat & ketentuan)
- `settings` (key-value: nama toko, logo, kontak, rekening, konfigurasi ongkir/pajak)
- `notifications` (bawaan Laravel)
- `activity_logs` (audit trail aksi admin — pakai `spatie/laravel-activitylog` atau tabel custom sederhana)

## 6. Daftar Fitur

### Sisi Pembeli (Storefront)
- Registrasi/login, verifikasi email, lupa password, (opsional) login sosial
- Jelajah produk: kategori, pencarian, filter (harga, brand, rating), sorting
- Detail produk: galeri gambar, varian (pilih warna/ukuran → cek stok & harga otomatis), deskripsi, review & rating
- Keranjang belanja (guest & login, auto-merge saat login)
- Wishlist
- Checkout: pilih/alamat baru, metode pengiriman, ringkasan biaya, kupon, metode pembayaran (Midtrans/manual transfer)
- Riwayat pesanan & tracking status, invoice PDF, unggah bukti transfer (jika manual)
- Retur/komplain sederhana (ajukan → admin proses)
- Profil & manajemen alamat
- Notifikasi (email + in-app): status pesanan, promo

### Sisi Admin/Manajemen
- Dashboard: ringkasan penjualan, grafik (chart.js seperti asprov1), produk terlaris, stok menipis
- Manajemen produk: CRUD, kategori & brand, varian & atribut, upload gambar, stok, import/export Excel massal
- Manajemen pesanan: daftar & filter status, ubah status (proses → dikirim → selesai), cetak invoice/label pengiriman (PDF)
- Manajemen pembayaran: verifikasi transfer manual, riwayat transaksi Midtrans, webhook otomatis
- Manajemen pengguna & role/permission (tambah staff baru, atur hak akses granular)
- Manajemen promo: kupon diskon, produk unggulan/flash sale
- Manajemen konten: banner beranda, halaman statis (FAQ, kebijakan)
- Laporan: penjualan per periode, produk terlaris, stok, export Excel/PDF
- Pengaturan toko: identitas, kontak, rekening pembayaran, ongkir dasar, pajak
- Log aktivitas admin (audit trail)

## 7. Strategi Keamanan

- **Autentikasi**: Fortify (rate-limited login, password policy kuat), 2FA wajib untuk Super Admin/Admin.
- **Otorisasi**: RBAC granular via spatie/laravel-permission + Policy per model — cek di Controller **dan** middleware route, tidak hanya UI (pelajaran dari catatan asprov1: dulu aturan join/absen hanya di UI lalu diperkuat ke server-side).
- **Validasi**: semua input lewat `FormRequest`, whitelisting field via `$fillable`, tidak pernah trust data harga/stok dari client saat checkout (hitung ulang harga & stok di server).
- **State-changing actions** pakai POST/PUT/DELETE, bukan GET (hindari CSRF via link, sesuai temuan hardening di asprov1).
- **Pembayaran**: verifikasi signature/notifikasi webhook Midtrans di server, status pembayaran final ditentukan server bukan redirect client; idempotent handling notifikasi (hindari double-processing race condition — pelajaran dari histori asprov1).
- **Race condition stok**: gunakan DB transaction + row locking (`lockForUpdate`) saat kurangi stok di checkout agar tidak oversell saat traffic tinggi bersamaan.
- **Upload file**: validasi mime/size, simpan di luar akses langsung eksekusi, generate nama unik.
- **reCAPTCHA** di form publik sensitif (register, form komplain) seperti asprov1.
- **Rate limiting** pada endpoint sensitif (login, checkout, apply-coupon).
- **Audit log** untuk aksi admin kritikal (ubah harga, hapus produk, refund).
- **HTTPS** wajib di produksi, cookie secure+httponly, `.env` tidak pernah masuk repo.
- **Mass assignment & XSS**: Eloquent `$fillable` ketat; Vue otomatis escape output.

## 8. Non-Fungsional

- **Performa**: eager loading relasi (hindari N+1), cache daftar kategori/produk populer, pagination di semua listing.
- **Skalabilitas kode**: setiap modul baru cukup tambah folder Controller/Service/Page baru mengikuti pola yang sama — tidak mengubah modul lain.
- **Testing**: feature test untuk alur kritikal (tambah ke keranjang, checkout, kurangi stok, webhook pembayaran, otorisasi role).
- **CI/CD**: GitHub Actions menjalankan Pint (format) + PHPUnit/Pest pada setiap push/PR ke branch ini.
- **Observability**: logging terstruktur untuk error pembayaran & webhook.

## 9. Roadmap Implementasi (bertahap)

1. **Fase 0 — Fondasi**: install Laravel 12 + Inertia + Vue 3 + Vite, setup Fortify, spatie/laravel-permission, layout dasar (Admin/Customer/Storefront), seeder role & user awal.
2. **Fase 1 — Katalog**: kategori, brand, produk, varian/atribut, upload gambar, halaman admin CRUD, halaman publik listing & detail produk.
3. **Fase 2 — Transaksi Inti**: keranjang, checkout, alamat, integrasi Midtrans + manual transfer, kurangi stok aman (locking), invoice PDF.
4. **Fase 3 — Manajemen Pesanan**: admin proses pesanan, update status, tracking, shipment, notifikasi email/in-app ke pembeli.
5. **Fase 4 — Promo & Interaksi**: kupon/diskon, wishlist, review & rating.
6. **Fase 5 — Laporan & Konten**: dashboard analitik, export Excel/PDF, manajemen banner/halaman statis, pengaturan toko.
7. **Fase 6 — Pengerasan**: audit keamanan menyeluruh, uji beban ringan, penambahan test coverage, deployment guide.

## 10. Yang Belum Diputuskan (butuh konfirmasi sebelum implementasi)

- Apakah checkout mendukung **guest checkout** (tanpa akun) atau wajib login?
- Apakah perlu **multi-vendor** (banyak penjual dalam satu platform) atau single-store saja untuk awal? (Skema di atas didesain single-store, tapi bisa diperluas dengan `vendor_id` nullable jika multi-vendor dibutuhkan nanti.)
- Ongkir: cukup input manual per admin dulu, atau langsung integrasi API kurir (RajaOngkir/Biteship) sejak Fase 2?
- Skala data/traffic yang ditarget (menentukan kebutuhan Redis/queue worker sejak awal atau boleh nanti)?

---

Setelah rencana ini disetujui/disesuaikan, langkah berikutnya adalah eksekusi **Fase 0** (scaffolding project Laravel + Inertia + Vue).
