# Uji Beban Ringan

Bagian dari Fase 6 — Pengerasan (lihat `docs/PERENCANAAN.md` § 9). Tujuannya
memastikan alur baca storefront yang paling sering diakses (beranda, katalog,
detail produk) tidak punya masalah performa dasar (mis. N+1 query, blocking
I/O) di bawah beban ringan bersamaan — bukan uji beban skala produksi.

## Cara Menjalankan

```bash
php artisan serve --port=8123 &
./scripts/load-test.sh http://127.0.0.1:8123 <concurrency> <total-request-per-endpoint>

# contoh:
./scripts/load-test.sh http://127.0.0.1:8123 10 40
```

Script `scripts/load-test.sh` memakai `curl` + `xargs -P` untuk mengirim
request bersamaan ke tiga endpoint baca (tanpa menyentuh endpoint yang sudah
di-rate-limit seperti checkout/cek kupon — lihat § 7 Strategi Keamanan di
`docs/PERENCANAAN.md`), lalu melaporkan jumlah berhasil (HTTP 200), rata-rata,
dan waktu respons terlambat.

## Hasil Terakhir (dijalankan di sandbox dev, `php artisan serve`)

Concurrency 10, 40 request per endpoint, database SQLite hasil `migrate:fresh --seed`:

| Endpoint | Berhasil | Rata-rata | Terlambat |
|---|---|---|---|
| `GET /` (beranda) | 40/40 | 0.187s | 0.215s |
| `GET /produk` (katalog) | 40/40 | 0.265s | 0.319s |
| `GET /produk/{slug}` (detail produk) | 40/40 | 0.237s | 0.279s |

Tidak ada error (semua 200), tidak ada request yang jauh lebih lambat dari
rata-rata (indikasi tidak ada N+1 query atau lock kontensi pada level ini).

## Batasan

- `php artisan serve` adalah server pengembangan single-process — angka di
  atas **bukan** representasi performa produksi (di produksi pakai
  PHP-FPM + Nginx dengan worker banyak, OPcache aktif, dan `config:cache`).
  Tujuan tes ini hanya mendeteksi regresi performa yang jelas di level kode
  (mis. query berulang per baris), bukan mengukur kapasitas server nyata.
- SQLite dipakai di sandbox; performa MySQL di produksi bisa berbeda
  (biasanya lebih baik untuk konkurensi tulis, lihat catatan queue/cache di
  `docs/PERENCANAAN.md` § 10).
- Untuk uji beban yang representatif produksi, jalankan skrip ini (atau tool
  seperti `k6`/`wrk`) terhadap server yang sudah dikonfigurasi sesuai
  `docs/DEPLOYMENT.md` (php-fpm, `php artisan optimize`, MySQL).

## Menjalankan Ulang Setelah Perubahan Besar

Jalankan lagi skrip ini setiap kali ada perubahan besar ke query katalog
(mis. filter/sorting baru, kolom hasil `withCount`/`withAvg` baru) untuk
memastikan tidak ada regresi N+1.
