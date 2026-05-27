# Aplikasi QRIS Dinamis

Aplikasi Laravel + Inertia + Vue untuk menampilkan QRIS dinamis, mengelola profil QRIS dari dasbor, dan membaca data transaksi dari file CSV yang disimpan di folder `public`.

## Fitur

- Halaman publik untuk menampilkan QRIS dan nominal pembayaran.
- Nama merchant diambil dari profil aktif atau dari `public/qris.csv`.
- Dasbor CRUD untuk menyimpan beberapa profil QRIS.
- Aktivasi profil QRIS yang sedang dipakai di halaman publik.
- Unggah gambar QR untuk membaca payload QRIS secara otomatis.

## Kebutuhan Sistem

- PHP 8.3 atau lebih baru
- Composer
- Node.js 18+ dan npm/pnpm
- Basis data SQLite, MySQL, atau yang didukung Laravel

## Instalasi

### 1. Kloning repositori

```bash
git clone <repo-url>
cd qris
```

### 2. Instal dependensi PHP

```bash
composer install
```

### 3. Instal dependensi frontend

```bash
npm install
```

### 4. Siapkan file lingkungan

Jika file `.env` belum ada, salin dari `.env.example`.

```bash
copy .env.example .env
```

Lalu sesuaikan konfigurasi basis data dan aplikasi di `.env`.

### 5. Buat kunci aplikasi

```bash
php artisan key:generate
```

### 6. Jalankan migrasi basis data

```bash
php artisan migrate
```

### 7. Bangun aset frontend

```bash
npm run build
```

## Menjalankan Aplikasi

### Mode pengembangan

Jalankan server Laravel dan Vite secara bersamaan:

```bash
composer run dev
```

Atau jalankan secara manual:

```bash
php artisan serve
npm run dev
```

## Cara Penggunaan

### Halaman publik

- Buka halaman utama `/`.
- Aplikasi akan menampilkan QRIS dari profil aktif.
- Jika belum ada profil aktif, aplikasi memakai nilai bawaan dari konfigurasi dan membaca nama merchant dari `public/qris.csv`.

### Dasbor

- Buka `/dashboard` setelah masuk.
- Buat profil QRIS baru melalui formulir yang tersedia.
- Ubah nama merchant atau payload statis untuk mengganti QRIS yang dipakai.
- Klik tombol aktivasi untuk menjadikan profil tersebut aktif di halaman publik.
- Hapus profil jika sudah tidak dipakai.

### Unggah QR

- Di dasbor, pengguna dapat mengunggah gambar QR.
- Payload QR akan dibaca otomatis dan formulir akan terisi.
- Fitur ini tersedia saat membuat profil baru maupun saat mengubah profil yang sudah ada.

## Format File CSV

Letakkan file CSV di `public/qris.csv`.

Kolom yang dibaca oleh aplikasi antara lain:

- `Merchant/Store Name`
- `Transaction Type`
- `Transaction Amount`
- `Transaction ID`
- `Reference ID`
- `Create Time`
- `Update Time`
- `Payment Method`

Nama merchant akan diambil dari kolom `Merchant/Store Name`.

## Konfigurasi QRIS Bawaan

Payload QRIS bawaan bisa diubah melalui variabel lingkungan berikut:

```env
QRIS_STATIC_PAYLOAD=000201...
```

Jika `QRIS_STATIC_PAYLOAD` tidak diisi, aplikasi memakai payload bawaan dari `config/qris.php`.

## Perintah Berguna

```bash
php artisan test
npm run types:check
npm run build
```

## Catatan

- Pastikan basis data dan driver antrean/sesi/cache sudah disiapkan sesuai `.env`.
- Jika memakai SQLite, pastikan file basis data sudah ada sebelum menjalankan migrasi.
- Folder `public/` harus berisi file CSV jika ingin menampilkan ringkasan merchant dari data transaksi.
