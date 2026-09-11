# USC Vehicle Ops

Sistem manajemen operasional kendaraan berbasis web untuk USC, dibangun dengan **Laravel 12** + **Vite** + **Tailwind CSS 4**.

---

## Alur Proses Penggunaan Aplikasi

### 👤 1. Setup Awal (Admin)

Sebelum sistem dapat digunakan, Admin menyiapkan master data terlebih dahulu:

- Daftarkan **kendaraan** (plat nomor, tipe, odometer awal, dll.)
- Daftarkan **driver**
- Daftarkan **departemen**
- Daftarkan **vendor** servis beserta kontrak sewa
- Set **harga BBM** & pengaturan sistem

---

### 📋 2. Peminjaman Kendaraan

```
Karyawan mengajukan peminjaman
        ↓
Masuk antrean approval (Admin GA)
        ↓
Admin GA menyetujui / menolak
        ↓
[Disetujui] → Serah terima kendaraan (Check-Out)
              → Cetak surat jalan (PDF)
        ↓
Kendaraan digunakan
        ↓
Pengembalian kendaraan (Check-In)
        ↓
Peminjaman selesai & odometer diperbarui
```

**Catatan:**
- Pemohon dapat membatalkan pengajuan sebelum serah terima.
- Serah terima mencatat inspeksi kondisi kendaraan beserta foto.
- Sistem menandai peminjaman yang melewati tanggal tanpa pengembalian sebagai **overdue**.

---

### ⛽ 3. Pemakaian BBM & Reimbursement

```
Driver / Karyawan mencatat pengisian BBM
        ↓
Ajukan klaim BBM (foto nota wajib)
        ↓
Admin GA memverifikasi / menolak
        ↓
[Terverifikasi] → Dikumpulkan ke Reimbursement Batch
                        ↓
                Diserahkan ke Finance
                        ↓
                Finance menandai sebagai "Lunas"
```

**Catatan:**
- Sistem mendeteksi anomali konsumsi BBM secara otomatis.
- Admin GA dapat mengkoreksi nominal klaim saat verifikasi.
- Klaim terlambat (late claim) memerlukan persetujuan khusus.

---

### 🔧 4. Servis Kendaraan

```
Admin / Mekanik mencatat jadwal servis berkala
        ↓
Sistem memberi peringatan saat servis mendekati / jatuh tempo
        ↓
Buat Permintaan Servis ke Vendor
        ↓
Kirim permintaan ke vendor (generate PDF)
        ↓
Update status pengerjaan
        ↓
Selesai → Riwayat servis kendaraan tercatat otomatis
```

---

### 🛣️ 5. Biaya Tol

```
Kartu E-Toll di-top up saldo
        ↓
Catat transaksi tol per perjalanan
  (tarif otomatis dari database berdasarkan gerbang & golongan kendaraan)
        ↓
Rekonsiliasi saldo sistem vs saldo aktual kartu
```

---

### 📊 6. Laporan & Monitoring

| Fitur | Keterangan |
|---|---|
| **Dashboard** | Ringkasan aktivitas & status kendaraan |
| **Papan Tugas** | Penugasan driver harian (bisa dicetak) |
| **Laporan** | Export rekap biaya operasional ke Excel / PDF |
| **Audit Log** | Rekam jejak semua aksi pengguna di sistem |
| **Notifikasi** | Alert real-time + preferensi kanal per pengguna |

---


## Cara Menjalankan

### 1. Untuk Testing (Development)

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Jalankan migrasi & seeder
php artisan migrate --seed

# Jalankan dev server
php artisan serve
npm run dev
php artisan reverb:start
```

### 2. Untuk Production

```bash
# Install dependencies (tanpa package dev)
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Setup environment (Edit .env sesuai server production)
cp .env.example .env
php artisan key:generate

# Jalankan migrasi (biasanya tanpa seeder di production)
php artisan migrate --force

# Cache konfigurasi & route agar lebih cepat
php artisan optimize

# (Opsional) Setup queue worker / reverb
# Pastikan menggunakan Supervisor di server production untuk daemon ini
# php artisan queue:work
# php artisan reverb:start
```
