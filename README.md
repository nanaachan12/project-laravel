# StockGudang - Modul Manajemen Stok Gudang Sederhana

Aplikasi manajemen inventaris stok gudang dan mutasi barang masuk/keluar berbasis **Laravel 12** dan **Alpine.js** dengan tampilan modern menggunakan **Tailwind CSS**.

---

## 📋 Daftar Isi
1. [Fitur Aplikasi](#-fitur-aplikasi)
   - [Fitur yang Sudah Selesai](#-fitur-yang-sudah-selesai)
   - [Rencana Pengembangan / Fitur Belum Selesai](#-rencana-pengembangan--fitur-belum-selesai)
2. [Prasyarat Sistem](#-prasyarat-sistem)
3. [Panduan Menjalankan Project](#-panduan-menjalankan-project)
4. [Menjalankan Pengujian Otomatis (Automated Tests)](#-menjalankan-pengujian-otomatis-automated-tests)
5. [Struktur Database](#-struktur-database)
6. [Catatan Penggunaan AI (AI Usage Note)](#-catatan-penggunaan-ai-ai-usage-note)

---

## ✨ Fitur Aplikasi

### ✅ Fitur yang Sudah Selesai
Seluruh 5 fitur wajib yang dipersyaratkan telah selesai 100% dan diuji:

1. **Halaman Daftar Barang & Form Tambah Barang (Fitur 1)**
   - Menampilkan daftar seluruh barang inventaris dengan kartu statistik (*Total Jenis Barang*, *Total Unit Tersedia*, *Stok Kosong/Habis*).
   - Form modal interaktif menggunakan Alpine.js untuk menambah barang baru.
   - Preset cepat satuan barang (`pcs`, `box`, `rim`, `kg`, `unit`, `lusin`).
   - Validasi ketat pada backend:
     - `kode`: Wajib diisi (*required*) dan harus unik (*unique*).
     - `nama`: Wajib diisi (*required*).
     - `satuan`: Wajib diisi (*required*).
     - `stock`: Opsional (default 0, nilai minimal 0).
   - Filter pencarian barang berdasarkan kode, nama, atau satuan secara langsung.

2. **Pencatatan Barang Masuk & Keluar dalam Modal Alpine.js (Fitur 2)**
   - Tombol aksi cepat **`+ Masuk`** dan **`- Keluar`** pada setiap baris data barang.
   - Form modal bertenaga **Alpine.js** yang terbuka instan tanpa me-refresh halaman (*seamless UX*).
   - Pengisian otomatis identitas barang (kode, nama, stok saat ini).
   - Tombol *switcher* instan di dalam modal untuk beralih antara jenis `Masuk` dan `Keluar`.
   - Simulasi kalkulasi stok akhir secara *real-time* saat user mengetik jumlah barang.

3. **Pembaruan Stok Otomatis Setiap Ada Mutasi (Fitur 3)**
   - Eksekusi mutasi dibungkus di dalam **Database Transaction (`DB::transaction`)** dengan penguncian baris pesimistik (*pessimistic row locking / `lockForUpdate`*) untuk integritas data.
   - Mutasi `masuk` otomatis menambah stok barang (`increment`).
   - Mutasi `keluar` otomatis mengurangi stok barang (`decrement`).

4. **Proteksi Anti Stok Minus / Overdraw (Fitur 4)**
   - **Validasi Frontend (Alpine.js)**: Memunculkan alert peringatan merah seketika jika kuantitas barang keluar melebihi stok yang ada, serta menonaktifkan (*disable*) tombol submit.
   - **Validasi Backend (Laravel)**: Jika kuantitas barang keluar melebihi stok tersedia, transaksi **langsung ditolak**, sistem melempar pesan kesalahan validasi, transaksi di-*rollback*, dan stok barang dijamin **tidak berubah sedikit pun**.

5. **Riwayat Mutasi per Barang (Fitur 5)**
   - **Modal Riwayat Cepat**: Tombol *Riwayat* pada tabel barang memanggil endpoint JSON API dan menampilkan tabel riwayat mutasi instan di dalam modal Alpine.js.
   - **Halaman Detail Lengkap (`/items/{id}`)**: Menampilkan kartu profil barang, akumulasi total barang masuk, akumulasi total barang keluar, stok aktif, dan tabel kronologi lengkap seluruh mutasi barang.

---

### ⏳ Rencana Pengembangan / Fitur Belum Selesai
Fitur-fitur tambahan yang potensial untuk pengembangan tahap berikutnya:
- [ ] **Autentikasi & Hak Akses (Multi-User RBAC)**: Pemisahan peran antara *Admin Gudang*, *Operator/Staff Input*, dan *Supervisor*.
- [ ] **Laporan & Ekspor Data**: Ekspor rekapitulasi stok dan mutasi barang ke format PDF dan Excel/CSV.
- [ ] **Fitur Multi-Gudang / Lokasi Rak**: Pengelolaan lokasi gudang yang berbeda (Gudang Utama, Gudang Cabang) dan penomoran rak/bin.
- [ ] **Sistem Peringatan Stok Minimum (Reorder Point Alert)**: Notifikasi email atau indikator otomatis ketika stok barang mencapai batas minimum pemesanan ulang.
- [ ] **Upload Foto/Gambar Barang**: Kemampuan mengunggah foto fisik barang inventaris.
- [ ] **Pencetakan Barcode / QR Code**: Generate label barcode/QR code untuk memudahkan *scanning* fisik barang.

---

## 💻 Prasyarat Sistem
Pastikan perangkat Anda telah terinstal:
- **PHP** >= 8.2 (disarankan PHP 8.3 dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`)
- **Composer** >= 2.x
- **MySQL / MariaDB** (atau SQLite)
- **Web Browser** modern dengan JavaScript aktif

---

## 🚀 Panduan Menjalankan Project

### 1. Clone Repositori
```bash
git clone https://github.com/nanaachan12/project-laravel.git
cd project-laravel
```

### 2. Instal Dependensi Composer
```bash
composer install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env` (jika belum ada):
```bash
cp .env.example .env
```
Lalu buat Application Key:
```bash
php artisan key:generate
```

Sesuaikan konfigurasi database pada file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_laravel
DB_USERNAME=root
DB_PASSWORD=
```
> *Catatan: Pastikan database `project_laravel` sudah dibuat di MySQL/phpMyAdmin Anda.*

### 4. Jalankan Migrasi Database
Jalankan migrasi untuk membuat tabel `items`, `stock_movements`, dan tabel sistem lainnya:
```bash
php artisan migrate
```

### 5. Jalankan Server Development
Jalankan server bawaan Laravel:
```bash
php artisan serve
```
Buka browser dan akses:
👉 **[http://localhost:8000](http://localhost:8000)**

---

## 🧪 Menjalankan Pengujian Otomatis (Automated Tests)

Project ini dilengkapi dengan pengujian menyeluruh (*Feature Test*) menggunakan PHPUnit/Pest:
- `ItemManagementTest`: Menguji halaman indeks barang, pembuatan barang baru, validasi form kosong, dan validasi keunikan kode barang.
- `StockMovementTest`: Menguji auto-update stok masuk/keluar, proteksi penolakan mutasi keluar melebihi stok (anti-minus), dan riwayat mutasi.

Untuk menjalankan seluruh rangkaian pengujian:
```bash
php artisan test
```

---

## 🗄️ Struktur Database

### Tabel `items` (Barang)
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment primary key |
| `kode` | VARCHAR(50) | Kode unik barang (Wajib & Unique) |
| `nama` | VARCHAR(255) | Nama barang (Wajib) |
| `satuan` | VARCHAR(50) | Satuan unit barang (Wajib, misal: pcs, box, kg) |
| `stock` | INT | Jumlah stok barang aktif (Default: 0) |
| `created_at` / `updated_at` | TIMESTAMP | Waktu pencatatan data |

### Tabel `stock_movements` (Mutasi Stok)
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment primary key |
| `item_id` | BIGINT (FK) | Relasi ke tabel `items.id` (*cascade on delete*) |
| `jenis` | ENUM | Jenis mutasi: `'masuk'` atau `'keluar'` |
| `jumlah` | INT | Kuantitas mutasi (Minimal 1) |
| `keterangan` | TEXT | Catatan/keterangan mutasi (Opsional) |
| `tanggal` | DATE | Tanggal transaksi mutasi |
| `created_at` / `updated_at` | TIMESTAMP | Waktu pencatatan data |

---

## 🤖 Catatan Penggunaan AI (AI Usage Note)

Dalam pengembangan project ini, **AI (Antigravity Coding Assistant bertenaga Gemini)** digunakan secara terarah sebagai mitra *pair programming* dengan rincian berikut:

1. **Perancangan Skema & Migrasi Database**:
   - Membantu menyusun file migrasi tabel `items` dan `stock_movements` sesuai spesifikasi field dan relasi foreign key yang aman.
2. **Implementasi Business Logic & Integritas Transaksi**:
   - Membantu menyusun controller `StockMovementController` dengan memanfaatkan **`DB::transaction()`** dan **`lockForUpdate()`** guna mencegah inkonsistensi stok dan *race condition*.
   - Mengimplementasikan validasi backend yang menolak mutasi jika stok keluar melebihi stok yang ada tanpa mengubah stok barang.
3. **Desain Antarmuka Modern & Reaktif**:
   - Membantu penataan layout antarmuka menggunakan **Tailwind CSS CDN**.
   - Menyusun komponen modal interaktif dan manipulasi DOM reaktif tanpa reload menggunakan **Alpine.js CDN**.
4. **Penyusunan Pengujian Otomatis (*Test-Driven Quality*)**:
   - Menyusun skenario pengujian unit & fitur lengkap di `tests/Feature/ItemManagementTest.php` dan `tests/Feature/StockMovementTest.php` untuk memastikan seluruh fitur wajib terverifikasi secara objektif.
5. **Git Workflow & Dokumentasi**:
   - Membantu penataan commit git bertahap per fitur dan penyusunan dokumentasi teknis pada file `README.md` ini.
