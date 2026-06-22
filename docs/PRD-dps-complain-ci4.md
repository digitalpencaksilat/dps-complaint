# PRD — Sistem Complain Peserta Kejuaraan

**Project:** DPS Complain / Sistem Complain Peserta Kejuaraan  
**Tech Stack:** CodeIgniter 4, Bootstrap 5, MySQL/MariaDB  
**Target User:** Pelatih / official kontingen, admin panitia  
**Core Mode:** Multi-kejuaraan / multi-event  
**Status:** Draft awal  

---

## 1. Ringkasan Produk

Sistem Complain Peserta Kejuaraan adalah aplikasi web untuk menampung komplain/koreksi data peserta kejuaraan dari pelatih atau official kontingen.

Data peserta utama tidak dibuat manual dari awal, tetapi diambil dari database kejuaraan yang sudah ada melalui proses import/sinkronisasi per kejuaraan. Admin membuat data kejuaraan terlebih dahulu, lalu setiap data peserta dan kontingen terikat ke kejuaraan tersebut. Pelatih membuka website, memilih kejuaraan yang ingin diajukan complain, memilih jenis complain, lalu mengisi data sesuai kebutuhan complain.

Untuk complain selain **"Tidak Ada Peserta"**, pelatih wajib mencari dan memilih peserta yang sudah terdaftar agar complain terhubung ke data atlet yang benar.

Untuk complain **"Tidak Ada Peserta"**, pelatih mencari dan memilih kontingen, lalu menuliskan keterangan peserta yang belum terdaftar.

Setelah satu atau beberapa complain diisi, pelatih mengisi nama official dan nomor telepon, lalu submit. Admin panitia dapat melihat complain per kejuaraan, memfilter, memproses, dan memberi status atas complain tersebut.

---

## 2. Tujuan Produk

1. Menyediakan kanal resmi untuk pelatih/official mengajukan complain data peserta.
2. Mengurangi complain manual via WhatsApp/chat personal yang rawan tercecer.
3. Memastikan complain terhubung ke kejuaraan, peserta, dan kontingen yang benar.
4. Mempermudah panitia melakukan rekap, validasi, dan tindak lanjut complain per kejuaraan.
5. Menyimpan histori complain beserta status penyelesaiannya.

---

## 3. Masalah Yang Diselesaikan

Saat ini, complain peserta biasanya dikirim manual ke panitia. Masalah yang sering muncul:

- Data complain tidak terpusat.
- Panitia sulit tahu complain mana yang sudah diproses.
- Nama peserta sering mirip, rawan salah identifikasi.
- Official tidak selalu memberi data lengkap.
- Tidak ada format baku untuk jenis complain.
- Rekap complain perlu dibuat manual.

Sistem ini membuat proses complain lebih rapi: pilih jenis complain, pilih peserta/kontingen dari data valid, isi keterangan, submit, lalu admin memproses.

---

## 4. Scope MVP

### 4.1 Termasuk MVP

#### Sisi Pelatih / Official

1. Halaman form complain publik.
2. Pilih kejuaraan terlebih dahulu.
3. Pilih jenis complain setelah kejuaraan dipilih.
4. Jenis complain awal:
   - Kesalahan Nama
   - Kesalahan Jenis Kelamin
   - Kesalahan Kategori Yang Diikuti
   - Tidak Ada Peserta
5. Jika jenis complain **bukan Tidak Ada Peserta**:
   - Pelatih mencari nama peserta dari kejuaraan terpilih.
   - Sistem menampilkan hasil pencarian berisi nama peserta dan kontingen.
   - Pelatih memilih peserta yang sesuai.
   - Sistem menampilkan informasi atlet.
   - Pelatih mengisi keterangan kesalahan.
6. Jika jenis complain **Tidak Ada Peserta**:
   - Pelatih mencari nama kontingen dari kejuaraan terpilih.
   - Sistem menampilkan list kontingen.
   - Pelatih memilih kontingen yang sesuai.
   - Pelatih mengisi keterangan peserta yang belum ada.
7. Tombol **Tambah Complain Lagi** untuk menambah lebih dari satu complain dalam satu submit.
8. Input nama official.
9. Input nomor telepon official.
10. Official menggambar tanda tangan digital langsung di layar HP/laptop.
11. Simpan complain.
12. Halaman sukses setelah submit.
13. Nomor tiket / kode complain setelah berhasil submit.

#### Sisi Admin

1. Login admin.
2. Kelola data kejuaraan.
3. Dashboard daftar complain.
4. Detail complain.
5. Filter complain berdasarkan:
   - Kejuaraan
   - Status
   - Jenis complain
   - Kontingen
   - Tanggal submit
6. Ubah status complain:
   - Baru
   - Diproses
   - Selesai
   - Ditolak
7. Catatan admin / hasil tindak lanjut.
8. Export complain ke Excel/CSV.

#### Data Import

1. Import/sinkronisasi data peserta dari database kejuaraan existing.
2. Data yang dibutuhkan:
   - ID peserta sumber
   - Nama panjang peserta
   - Nama kontingen
   - Jenis kelamin
   - Kategori usia
   - Kategori pertandingan
   - Kelas tanding / nama seni
3. Import/sinkronisasi data kontingen.
4. Data peserta di sistem complain bersifat referensi untuk pencarian dan validasi.

---

## 5. Di Luar Scope MVP

Fitur berikut tidak wajib untuk versi awal:

1. Login pelatih/official.
2. Upload bukti file/foto tambahan.
3. Notifikasi WhatsApp otomatis.
4. Approval bertingkat.
5. Auto-update langsung ke database kejuaraan.
6. Pembayaran complain.
7. Role admin detail selain admin utama.

Fitur tersebut bisa masuk fase berikutnya.

---

## 5A. Multi-Kejuaraan / Event Management

Sistem harus mendukung banyak kejuaraan. Admin wajib membuat data kejuaraan terlebih dahulu sebelum peserta/kontingen diimport dan sebelum complain dibuka untuk public.

### Data Kejuaraan Minimal

| Field | Deskripsi |
|---|---|
| Nama kejuaraan | Nama event yang tampil ke pelatih |
| Slug/kode event | Kode unik event untuk URL/tiket |
| Lokasi | Lokasi event opsional |
| Tanggal mulai | Tanggal mulai event |
| Tanggal selesai | Tanggal selesai event |
| Status | Draft/Aktif/Ditutup/Arsip |
| Batas waktu complain | Tanggal/jam terakhir complain diterima |
| Sumber database | Konfigurasi database sumber peserta untuk event tersebut |
| Batas SLA proses | Target waktu admin memproses complain, contoh 1x24 jam |

### Aturan Multi-Kejuaraan

1. Public hanya bisa submit complain ke kejuaraan berstatus **Aktif** dan belum melewati batas waktu complain.
2. Setiap kejuaraan memakai database sumber berbeda.
3. Peserta dan kontingen hasil import harus terikat ke `event_id` dari project complain, bukan hanya `source_event_id` dari database sumber.
4. Search peserta/kontingen wajib dibatasi berdasarkan kejuaraan terpilih.
5. Nomor tiket sebaiknya memakai prefix event, contoh: `DPS2026-000123`.
6. Admin dashboard default menampilkan filter kejuaraan.
7. Export complain bisa per kejuaraan atau semua kejuaraan.
8. Data complain lama tetap tersimpan walau kejuaraan diarsipkan.
9. Admin bisa menutup complain manual walau batas waktu belum lewat.
10. Sistem otomatis menolak submit baru jika status event Ditutup/Arsip atau melewati batas waktu complain.

---

## 6. User Role

### 6.1 Pelatih / Official

Aktor yang mengajukan complain. Tidak perlu login pada MVP. Wajib mengisi nama official dan nomor telepon.

Kemampuan:

- Pilih jenis complain.
- Cari peserta atau kontingen.
- Isi keterangan complain.
- Tambah lebih dari satu complain.
- Submit complain.
- Tracking tiket public untuk melihat status dan riwayat perubahan.

### 6.2 Admin Panitia

Aktor internal panitia yang memproses complain.

Kemampuan:

- Login.
- Lihat semua complain.
- Buka detail complain.
- Ubah status complain.
- Isi catatan admin.
- Export data complain.
- Melihat SLA/tenggat proses pada dashboard.
- Melihat histori perubahan status complain.

---

## 7. User Flow Pelatih / Official

### 7.1 Flow Utama

1. Pelatih membuka website complain.
2. Sistem menampilkan halaman form complain.
3. Pelatih memilih kejuaraan.
4. Sistem hanya memakai data peserta/kontingen dari kejuaraan terpilih.
5. Pelatih memilih jenis complain.
6. Sistem menampilkan form lanjutan berdasarkan jenis complain.
7. Pelatih mengisi complain.
8. Pelatih dapat klik **Tambah Complain Lagi** jika ada lebih dari satu complain.
9. Pelatih mengisi nama official.
10. Pelatih mengisi nomor telepon official.
11. Pelatih menggambar tanda tangan digital pada canvas/touchpad.
12. Pelatih klik **Simpan**.
13. Sistem validasi data.
14. Sistem menyimpan complain beserta tanda tangan.
15. Sistem menampilkan halaman sukses dan nomor tiket.

### 7.2 Flow: Kesalahan Nama

1. Pelatih memilih jenis complain **Kesalahan Nama**.
2. Sistem menampilkan input pencarian peserta.
3. Pelatih mengetik nama peserta.
4. Sistem menampilkan hasil pencarian:
   - Nama peserta
   - Kontingen
   - Kategori usia
   - Kategori pertandingan
   - Kelas tanding / nama seni
5. Pelatih memilih peserta yang benar.
6. Sistem menampilkan detail peserta.
7. Pelatih mengisi keterangan, contoh:
   - "Nama seharusnya Muhammad Rizky Pratama, bukan M Rizki Pratama."
8. Pelatih submit.

### 7.3 Flow: Kesalahan Jenis Kelamin

1. Pelatih memilih jenis complain **Kesalahan Jenis Kelamin**.
2. Pelatih mencari dan memilih peserta.
3. Sistem menampilkan detail peserta termasuk jenis kelamin saat ini.
4. Pelatih mengisi keterangan, contoh:
   - "Jenis kelamin seharusnya Putri."
5. Pelatih submit.

### 7.4 Flow: Kesalahan Kategori Yang Diikuti

1. Pelatih memilih jenis complain **Kesalahan Kategori Yang Diikuti**.
2. Pelatih mencari dan memilih peserta.
3. Sistem menampilkan detail peserta termasuk kategori saat ini.
4. Pelatih mengisi keterangan, contoh:
   - "Seharusnya masuk Tanding Usia Dini Putra Kelas B, bukan Kelas C."
5. Pelatih submit.

### 7.5 Flow: Tidak Ada Peserta

1. Pelatih memilih jenis complain **Tidak Ada Peserta**.
2. Sistem menampilkan input pencarian kontingen.
3. Pelatih mengetik nama kontingen.
4. Sistem menampilkan list kontingen.
5. Pelatih memilih kontingen yang benar.
6. Pelatih mengisi keterangan, contoh:
   - "Peserta atas nama Ahmad Fauzan belum muncul. Seharusnya terdaftar di Tanding Remaja Putra Kelas D."
7. Pelatih submit.

---

## 8. Functional Requirements

### FR-000 — Kelola Kejuaraan

Admin harus dapat membuat dan mengelola data kejuaraan sebelum complain dibuka.

**Acceptance Criteria:**

- Admin dapat membuat kejuaraan baru.
- Admin dapat mengubah status kejuaraan: Draft, Aktif, Ditutup, Arsip.
- Admin dapat mengisi konfigurasi database sumber berbeda untuk setiap kejuaraan.
- Admin dapat mengisi batas waktu complain sampai tanggal dan jam.
- Admin dapat mengisi SLA proses complain, contoh 24 jam setelah submit.
- Public hanya melihat kejuaraan berstatus Aktif dan belum lewat batas waktu complain.
- Setiap peserta, kontingen, dan complain wajib punya `event_id`.
- Import peserta dari database sumber wajib menyimpan relasi ke `event_id` lokal.
- Search peserta/kontingen wajib scoped ke event terpilih.
- Admin dapat memfilter dashboard berdasarkan kejuaraan.
- Admin dapat menutup complain manual dari data kejuaraan.

---

### FR-001 — Halaman Form Complain Publik

Sistem harus menyediakan halaman form complain yang bisa diakses pelatih/official tanpa login.

**Acceptance Criteria:**

- Halaman bisa dibuka dari browser.
- Tampilan responsive Bootstrap 5.
- Form menampilkan pilihan kejuaraan terlebih dahulu, lalu pilihan jenis complain.
- Tombol submit tidak aktif atau tidak bisa berhasil jika data wajib belum lengkap.

---

### FR-002 — Pilih Jenis Complain Terlebih Dahulu

Sistem harus meminta user memilih kejuaraan dan jenis complain sebelum menampilkan input peserta/kontingen.

**Acceptance Criteria:**

- Pilihan jenis complain tersedia:
  - Kesalahan Nama
  - Kesalahan Jenis Kelamin
  - Kesalahan Kategori Yang Diikuti
  - Tidak Ada Peserta
- Jika belum memilih kejuaraan dan jenis complain, form detail belum ditampilkan.
- Setelah jenis complain dipilih, form detail berubah sesuai jenis complain.

---

### FR-003 — Pencarian Peserta

Untuk complain selain **Tidak Ada Peserta**, sistem harus menyediakan pencarian peserta.

**Acceptance Criteria:**

- User dapat mengetik minimal 2-3 karakter nama peserta.
- Search hanya menampilkan peserta dari kejuaraan terpilih.
- Sistem menampilkan daftar hasil pencarian.
- Setiap hasil minimal menampilkan:
  - Nama peserta
  - Kontingen
  - Kategori usia
  - Jenis kelamin
  - Kategori pertandingan
  - Kelas tanding / nama seni
- User dapat memilih satu peserta.
- Peserta terpilih tersimpan sebagai referensi complain.

---

### FR-004 — Detail Peserta Terpilih

Setelah peserta dipilih, sistem harus menampilkan detail peserta.

**Acceptance Criteria:**

- Detail peserta muncul setelah dipilih.
- Detail menampilkan:
  - Nama panjang
  - Kontingen
  - Jenis kelamin
  - Kategori usia
  - Kategori pertandingan
  - Kelas tanding / nama seni
- Detail bersifat read-only.

---

### FR-005 — Pencarian Kontingen Untuk Tidak Ada Peserta

Untuk jenis complain **Tidak Ada Peserta**, sistem harus menyediakan pencarian kontingen.

**Acceptance Criteria:**

- User dapat mengetik nama kontingen.
- Search hanya menampilkan kontingen dari kejuaraan terpilih.
- Sistem menampilkan daftar kontingen yang cocok.
- User dapat memilih satu kontingen.
- Sistem tidak mewajibkan memilih peserta.
- Sistem mewajibkan keterangan berisi detail peserta yang belum ada.

---

### FR-006 — Keterangan Complain

Setiap item complain harus memiliki keterangan kesalahan.

**Acceptance Criteria:**

- Field keterangan wajib diisi.
- Minimal panjang keterangan: 10 karakter.
- Placeholder memberi contoh format keterangan.
- Keterangan tersimpan per item complain.

---

### FR-007 — Tambah Complain Lagi

User harus bisa menambahkan lebih dari satu complain dalam satu submit.

**Acceptance Criteria:**

- Tombol **Tambah Complain Lagi** tersedia.
- User dapat menambahkan beberapa item complain.
- Setiap item complain punya jenis complain, target peserta/kontingen, dan keterangan masing-masing.
- User dapat menghapus item complain sebelum submit.
- Minimal 1 item complain wajib ada.

---

### FR-008 — Data Official

User harus mengisi nama official dan nomor telepon sebelum submit.

**Acceptance Criteria:**

- Nama official wajib diisi.
- Nomor telepon wajib diisi.
- Nomor telepon menerima format angka, spasi, tanda +, dan tanda -.
- Nomor telepon disimpan di submit header.

---

### FR-008A — Tanda Tangan Digital Official

Official harus memberikan tanda tangan digital langsung pada form sebagai bukti pengajuan complain. Tanda tangan dibuat dengan menggambar langsung di layar HP/laptop, bukan upload gambar.

**Acceptance Criteria:**

- Form menyediakan area tanda tangan berbasis canvas.
- User dapat menggambar tanda tangan menggunakan jari di HP atau mouse/touchpad di laptop.
- Tombol **Hapus Tanda Tangan** tersedia.
- Tanda tangan wajib diisi sebelum submit.
- Sistem menolak submit jika canvas tanda tangan kosong.
- Tanda tangan disimpan sebagai data gambar hasil canvas, bukan file upload dari user.
- Tanda tangan tampil di admin detail sebagai bukti.
- Tanda tangan tampil di tracking tiket public dalam mode read-only jika diperlukan.
- Tanda tangan ikut tercetak/export PDF jika fitur cetak bukti dibuat.

---

### FR-009 — Simpan Complain

Sistem harus menyimpan complain sebagai satu laporan dengan beberapa item complain.

**Acceptance Criteria:**

- Sistem membuat nomor tiket unik dengan prefix/konteks kejuaraan.
- Sistem menyimpan `event_id` pada header complain.
- Sistem menyimpan data official.
- Sistem menyimpan tanda tangan digital official.
- Sistem menyimpan semua item complain.
- Status awal complain adalah **Baru**.
- Sistem menyimpan timestamp submit.
- Sistem menghitung `sla_due_at` berdasarkan SLA kejuaraan.
- Sistem menampilkan halaman sukses setelah simpan.

---

### FR-010 — Admin Login

Admin harus login untuk mengakses dashboard.

**Acceptance Criteria:**

- Halaman login tersedia.
- Username/password divalidasi.
- Session admin dibuat setelah login berhasil.
- User tanpa login diarahkan ke halaman login jika membuka dashboard.

---

### FR-011 — Dashboard Admin

Admin harus dapat melihat daftar complain.

**Acceptance Criteria:**

- Daftar complain menampilkan:
  - Kejuaraan
  - Nomor tiket
  - Tanggal submit
  - Nama official
  - Nomor telepon
  - Jumlah item complain
  - Status
  - SLA/tenggat proses
  - Indikator overdue
- Data terbaru tampil paling atas.
- Admin dapat membuka detail complain.

---

### FR-012 — Detail Complain Admin

Admin harus dapat melihat detail lengkap complain.

**Acceptance Criteria:**

- Detail header menampilkan data kejuaraan, data official, nomor tiket, SLA, dan status overdue.
- Detail item menampilkan jenis complain, peserta/kontingen terkait, dan keterangan.
- Untuk item peserta, tampil data peserta snapshot saat submit.
- Untuk item kontingen, tampil nama kontingen.

---

### FR-013 — Ubah Status Complain

Admin harus dapat mengubah status complain.

**Acceptance Criteria:**

- Status tersedia:
  - Baru
  - Diproses
  - Selesai
  - Ditolak
- Admin dapat menambahkan catatan admin.
- Perubahan status tersimpan dengan timestamp.
- Setiap perubahan status tersimpan ke histori perubahan.
- Histori perubahan tampil di detail admin dan tracking tiket public.

---

### FR-014 — Filter Dan Export

Admin harus dapat memfilter dan export complain.

**Acceptance Criteria:**

- Filter berdasarkan kejuaraan, status, jenis complain, kontingen, tanggal.
- Export menghasilkan file CSV atau Excel.
- Export mengikuti filter aktif.

---

### FR-015 — Import/Sinkronisasi Data Peserta

Sistem harus dapat mengambil data peserta dari database kejuaraan existing.

**Acceptance Criteria:**

- Admin/developer dapat menjalankan proses import/sync per kejuaraan.
- Setiap kejuaraan dapat memakai koneksi database sumber berbeda.
- Peserta tersimpan di tabel lokal untuk pencarian cepat dan terikat ke `event_id`.
- Kontingen tersimpan di tabel lokal dan terikat ke `event_id`.
- Data peserta punya referensi ID dari database sumber.
- Import bisa dijalankan ulang tanpa duplikasi.
- Unik peserta minimal berdasarkan kombinasi `event_id` + `source_participant_id`.

---


---

### FR-016 — Tracking Tiket Public

Official harus dapat melihat status complain lewat nomor tiket tanpa login.

**Acceptance Criteria:**

- User dapat membuka halaman tracking tiket public.
- User memasukkan nomor tiket.
- Sistem menampilkan status complain saat ini.
- Sistem menampilkan timeline perubahan status.
- Sistem menampilkan `public_note` dari admin.
- Sistem tidak menampilkan `admin_note` internal.
- Nomor telepon official disensor.
- Endpoint tracking terkena rate limit.

---

## 9. Non-Functional Requirements

### 9.1 Performance

- Pencarian peserta/kontingen harus terasa cepat.
- Target response pencarian: < 500 ms untuk dataset normal kejuaraan.
- Field pencarian menggunakan debounce agar tidak membanjiri server.

### 9.2 Usability

- Tampilan mobile-first.
- Form mudah dipakai di HP.
- Label Bahasa Indonesia jelas.
- Hasil pencarian harus menampilkan kontingen agar tidak salah pilih.

### 9.3 Security

- Validasi input server-side wajib.
- Escape output di view untuk mencegah XSS.
- Admin dashboard wajib login.
- CSRF protection aktif untuk form submit.
- Rate limiting wajib untuk submit publik dan tracking tiket public.
- Rekomendasi awal: submit complain maksimal 5 kali per IP per 10 menit, search API maksimal 60 request per IP per menit, tracking tiket maksimal 20 request per IP per 10 menit.

### 9.4 Reliability

- Submit harus atomic: header dan semua item complain tersimpan bersama.
- Jika ada error, tidak boleh tersimpan sebagian.
- Nomor tiket harus unik.

### 9.5 Maintainability

- Gunakan struktur CodeIgniter 4 standar.
- Logic bisnis ditempatkan di Service class, bukan menumpuk di Controller.
- Query database ditempatkan di Model/Repository.
- View dipisah reusable component jika diperlukan.

---

## 10. Data Yang Diimport Dari Database Kejuaraan

Minimal field:

| Field Lokal | Deskripsi |
|---|---|
| source_participant_id | ID peserta dari database kejuaraan |
| full_name | Nama panjang peserta |
| contingent_name | Nama kontingen |
| gender | Jenis kelamin |
| age_category | Kategori usia |
| competition_category | Kategori pertandingan, contoh Tanding/Seni Tunggal/Seni Ganda/Seni Regu |
| class_or_art_name | Kelas tanding atau nama seni |
| source_event_id | ID event sumber jika tersedia |
| imported_at | Waktu import |
| updated_at | Waktu update lokal |

Catatan format tampilan:

- Kategori usia + jenis kelamin sebaiknya ditampilkan: `Usia X - Putra/Putri`.
- Kelas tanding/nama seni harus jelas agar pelatih tidak salah pilih.

### 10.1 Mapping Database Sumber: `db_testing_event`

Hasil pengecekan awal pada database `db_testing_event`:

- Host: `127.0.0.1`
- User: `root`
- Password: kosong
- Jumlah data utama:
  - `pendaftar`: 595 baris
  - `kontingen`: 39 baris
  - `peserta_tanding`: 462 baris
  - `peserta_seni`: 134 baris
  - `kategori_usia`: 10 baris
  - `kategori_lomba`: 20 baris
  - `kelas_tanding`: 98 baris
  - `kompetisi_tanding`: 154 baris
  - `kompetisi_seni`: 29 baris
  - `sub_kategori_seni`: 30 baris

Mapping kontingen:

```sql
SELECT
  id_kontingen AS source_contingent_id,
  nama_kontingen AS name
FROM kontingen
```

Mapping peserta tanding:

```sql
SELECT
  pt.id_peserta_tanding AS source_participant_id,
  p.id_pendaftar AS source_registrant_id,
  p.nama_pendaftar AS full_name,
  p.jenis_kelamin AS gender,
  k.id_kontingen AS source_contingent_id,
  k.nama_kontingen AS contingent_name,
  ku.nama_kategori_usia AS age_category,
  kl.nama_kategori_lomba AS competition_category,
  kt.label AS class_or_art_name
FROM peserta_tanding pt
JOIN pendaftar p ON p.id_pendaftar = pt.id_pendaftar
LEFT JOIN kontingen k ON k.id_kontingen = p.id_kontingen
LEFT JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = pt.id_kompetisi_tanding
LEFT JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding
LEFT JOIN kategori_lomba kl ON kl.id_kategori_lomba = kt.id_kategori_lomba
LEFT JOIN kategori_usia ku ON ku.id_kategori_usia = kl.id_kategori_usia
```

Mapping peserta seni:

```sql
SELECT
  ps.id_peserta_seni AS source_participant_id,
  p.id_pendaftar AS source_registrant_id,
  p.nama_pendaftar AS full_name,
  p.jenis_kelamin AS gender,
  k.id_kontingen AS source_contingent_id,
  k.nama_kontingen AS contingent_name,
  ku.nama_kategori_usia AS age_category,
  kl.nama_kategori_lomba AS competition_category,
  CONCAT(sks.jenis_seni, ' - ', sks.nama_seni, ' - ', sks.sistem_penampilan) AS class_or_art_name
FROM peserta_seni ps
JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar
LEFT JOIN kontingen k ON k.id_kontingen = p.id_kontingen
LEFT JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni
LEFT JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni
LEFT JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni
LEFT JOIN kategori_lomba kl ON kl.id_kategori_lomba = sks.id_kategori_lomba
LEFT JOIN kategori_usia ku ON ku.id_kategori_usia = kl.id_kategori_usia
```

Catatan import:

- Karena tiap kejuaraan memakai database berbeda, command sync wajib menerima `event_id` lokal.
- `event_id` lokal dari project complain disimpan pada semua hasil import.
- `source_participant_id` perlu diberi prefix jenis lomba agar tidak bentrok, contoh `tanding:3` dan `seni:3`.
- `source_registrant_id` bisa disimpan di `raw_payload` atau field tambahan jika nanti dibutuhkan.

---

## 11. Rancangan Database Awal

### 11.0 `events`

Menyimpan data kejuaraan.

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | ID lokal |
| name | VARCHAR(255) | Nama kejuaraan |
| slug | VARCHAR(100) UNIQUE | Kode event untuk URL/tiket |
| location | VARCHAR(255) NULL | Lokasi event |
| start_date | DATE NULL | Tanggal mulai |
| end_date | DATE NULL | Tanggal selesai |
| complaint_deadline | DATETIME NULL | Batas waktu complain sampai tanggal dan jam |
| complaint_closed_at | DATETIME NULL | Waktu complain ditutup manual/otomatis |
| complaint_closed_reason | VARCHAR(255) NULL | Alasan penutupan complain |
| sla_hours | INT DEFAULT 24 | Target proses complain dalam jam |
| status | VARCHAR(30) | draft/active/closed/archived |
| source_db_host | VARCHAR(255) NULL | Host database kejuaraan sumber |
| source_db_name | VARCHAR(255) NULL | Nama database kejuaraan sumber |
| source_db_username | VARCHAR(255) NULL | Username database sumber |
| source_db_password_encrypted | TEXT NULL | Password database sumber terenkripsi |
| source_config | JSON/TEXT NULL | Mapping tabel/field sumber data event |
| created_at | DATETIME | CI4 timestamp |
| updated_at | DATETIME | CI4 timestamp |

Index:

- `slug`
- `status`
- `complaint_deadline`

---

### 11.1 `participants`

Menyimpan cache data peserta dari database kejuaraan.

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | ID lokal |
| event_id | BIGINT | FK ke events |
| source_participant_id | VARCHAR(100) | ID peserta dari DB sumber, disarankan prefix `tanding:`/`seni:` |
| source_competition_type | VARCHAR(30) | tanding/seni |
| source_registrant_id | VARCHAR(100) NULL | ID pendaftar dari DB sumber |
| full_name | VARCHAR(255) | Nama panjang |
| contingent_id | BIGINT NULL | FK ke contingents |
| contingent_name | VARCHAR(255) | Snapshot nama kontingen |
| gender | VARCHAR(20) | Putra/Putri/L/P sesuai sumber |
| age_category | VARCHAR(100) | Kategori usia |
| competition_category | VARCHAR(100) | Tanding/Seni |
| class_or_art_name | VARCHAR(150) | Kelas tanding/nama seni |
| source_event_id | VARCHAR(100) NULL | ID event sumber |
| raw_payload | JSON/TEXT NULL | Data mentah opsional |
| imported_at | DATETIME | Waktu import |
| created_at | DATETIME | CI4 timestamp |
| updated_at | DATETIME | CI4 timestamp |

Index yang disarankan:

- `event_id, source_participant_id`
- `source_participant_id`
- `full_name`
- `contingent_name`
- kombinasi `full_name, contingent_name`

---

### 11.2 `contingents`

Menyimpan cache kontingen dari database kejuaraan.

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | ID lokal |
| event_id | BIGINT | FK ke events |
| source_contingent_id | VARCHAR(100) NULL | ID dari DB sumber jika ada |
| name | VARCHAR(255) | Nama kontingen |
| source_event_id | VARCHAR(100) NULL | ID event sumber |
| created_at | DATETIME | CI4 timestamp |
| updated_at | DATETIME | CI4 timestamp |

Index:

- `name`
- `source_contingent_id`

---

### 11.3 `complaint_reports`

Header submit complain.

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | ID lokal |
| event_id | BIGINT | FK ke events |
| ticket_code | VARCHAR(50) UNIQUE | Nomor tiket |
| official_name | VARCHAR(255) | Nama official |
| official_phone | VARCHAR(50) | Nomor telepon |
| signature_image | LONGTEXT NULL | Data URL/Base64 tanda tangan canvas |
| signature_hash | VARCHAR(64) NULL | SHA-256 tanda tangan untuk integritas |
| signed_at | DATETIME NULL | Waktu tanda tangan dibuat/disubmit |
| status | ENUM/VARCHAR | Baru/Diproses/Selesai/Ditolak |
| admin_note | TEXT NULL | Catatan admin |
| submitted_at | DATETIME | Waktu submit |
| sla_due_at | DATETIME NULL | Tenggat proses berdasarkan SLA event |
| first_processed_at | DATETIME NULL | Pertama kali masuk Diproses |
| resolved_at | DATETIME NULL | Waktu Selesai/Ditolak |
| processed_at | DATETIME NULL | Alias/kompatibilitas untuk waktu proses terakhir |
| created_at | DATETIME | CI4 timestamp |
| updated_at | DATETIME | CI4 timestamp |

Index:

- `ticket_code`
- `status`
- `submitted_at`

---

### 11.4 `complaint_items`

Detail item complain.

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | ID lokal |
| complaint_report_id | BIGINT | FK ke complaint_reports |
| complaint_type | VARCHAR(50) | name_error/gender_error/category_error/missing_participant |
| participant_id | BIGINT NULL | FK ke participants, null jika tidak ada peserta |
| contingent_id | BIGINT NULL | FK ke contingents |
| participant_snapshot | JSON/TEXT NULL | Snapshot data peserta saat submit |
| contingent_snapshot | JSON/TEXT NULL | Snapshot data kontingen saat submit |
| description | TEXT | Keterangan user |
| created_at | DATETIME | CI4 timestamp |
| updated_at | DATETIME | CI4 timestamp |

Index:

- `complaint_report_id`
- `complaint_type`
- `participant_id`
- `contingent_id`

---

### 11.5 `complaint_status_histories`

Menyimpan histori perubahan status complain.

| Column | Type | Notes |
|---|---|---|
| id | BIGINT PK | ID lokal |
| complaint_report_id | BIGINT | FK ke complaint_reports |
| old_status | VARCHAR(30) NULL | Status sebelum perubahan |
| new_status | VARCHAR(30) | Status setelah perubahan |
| note | TEXT NULL | Catatan perubahan |
| public_note | TEXT NULL | Catatan yang boleh tampil di tracking tiket |
| changed_by_admin_id | BIGINT NULL | Admin pengubah |
| changed_at | DATETIME | Waktu perubahan |
| created_at | DATETIME | CI4 timestamp |

Index:

- `complaint_report_id`
- `new_status`
- `changed_at`

---

## 12. UI/UX Requirement

### 12.1 Halaman Form Public

Komponen halaman:

1. Header sistem.
2. Pilihan kejuaraan aktif.
3. Informasi batas waktu complain.
4. Info singkat aturan submit complain.
5. Card data complain.
6. Dropdown/radio jenis complain.
7. Dynamic form area:
   - Search peserta jika complain peserta.
   - Search kontingen jika complain tidak ada peserta.
8. Detail peserta/kontingen terpilih.
9. Textarea keterangan.
10. Tombol tambah complain lagi.
11. Ringkasan item complain yang sudah ditambah.
12. Field nama official.
13. Field nomor telepon.
14. Area tanda tangan digital.
15. Tombol hapus tanda tangan.
16. Tombol simpan.

### 12.2 Hasil Search Peserta

Setiap item hasil search wajib mudah dibedakan:

```text
Nama Peserta
Kontingen: Nama Kontingen
Kategori: Usia X - Putra/Putri
Pertandingan: Tanding / Seni Tunggal / dst
Kelas/Nomor: Kelas A / Tunggal Tangan Kosong / dst
```

### 12.3 Dashboard Admin

Komponen dashboard:

1. Summary card:
   - Total complain
   - Baru
   - Diproses
   - Selesai
   - Ditolak
   - Overdue SLA
2. Filter bar.
3. Table complain.
4. Badge status.
5. Badge SLA: Aman, Mendekati Deadline, Overdue.
6. Action detail.
7. Export button.

### 12.4 Tracking Tiket Public

Komponen halaman tracking:

1. Input nomor tiket.
2. Ringkasan kejuaraan.
3. Status complain saat ini.
4. SLA/tenggat proses.
5. Riwayat perubahan status/timeline.
6. Catatan admin yang boleh tampil ke public (`public_note`).
7. Detail item complain dalam mode read-only.
8. Nomor telepon official disensor, contoh `0812****7890`.
9. Tanda tangan official tampil read-only sebagai bukti jika kebijakan public mengizinkan.

Aturan privacy tracking public:

- Tracking public hanya menampilkan status, public note, timeline, dan ringkasan complain.
- Nomor telepon official wajib disensor.
- `admin_note` internal tidak boleh tampil di public.
- `public_note` boleh tampil di tracking tiket.
- Data peserta tetap read-only dan hanya sesuai snapshot submit.

---

## 13. API / Endpoint Draft CodeIgniter 4

### Public

| Method | URL | Fungsi |
|---|---|---|
| GET | `/complaints` | Form complain publik |
| GET | `/api/participants/search?event_id=&q=` | Search peserta per kejuaraan |
| GET | `/api/contingents/search?event_id=&q=` | Search kontingen per kejuaraan |
| POST | `/complaints` | Submit complain |
| GET | `/complaints/success/{ticket}` | Halaman sukses |
| GET | `/complaints/track` | Form tracking tiket public |
| POST | `/complaints/track` | Cari tiket complain |
| GET | `/complaints/track/{ticket}` | Detail tracking tiket public |

### Admin

| Method | URL | Fungsi |
|---|---|---|
| GET | `/admin/login` | Form login admin |
| POST | `/admin/login` | Proses login |
| POST | `/admin/logout` | Logout |
| GET | `/admin/complaints` | Dashboard complain |
| GET | `/admin/events` | Daftar kejuaraan |
| GET | `/admin/events/create` | Form tambah kejuaraan |
| POST | `/admin/events` | Simpan kejuaraan |
| GET | `/admin/events/{id}/edit` | Form edit kejuaraan |
| POST | `/admin/events/{id}` | Update kejuaraan |
| POST | `/admin/events/{id}/close-complaints` | Tutup complain kejuaraan |
| GET | `/admin/complaints/{id}` | Detail complain |
| POST | `/admin/complaints/{id}/status` | Update status dan histori |
| GET | `/admin/complaints/export` | Export data |

### Import / Sync

| Method/Command | Target | Fungsi |
|---|---|---|
| CLI | `php spark complaints:sync-participants --event=ID` | Sync peserta/kontingen dari DB kejuaraan terpilih |
| CLI | `php spark complaints:sync-participants --event=ID --fresh` | Re-sync event tertentu dari awal jika dibutuhkan |

---

## 14. Validation Rules

### 14.1 Submit Header

| Field | Rule |
|---|---|
| event_id | required, valid active event, complaint window open |
| official_name | required, min_length[3], max_length[255] |
| official_phone | required, min_length[8], max_length[50] |
| signature_image | required, valid canvas signature, max size 1 MB |
| items | required, min 1 item |

### 14.2 Item Complain Peserta

Berlaku untuk:

- Kesalahan Nama
- Kesalahan Jenis Kelamin
- Kesalahan Kategori Yang Diikuti

| Field | Rule |
|---|---|
| complaint_type | required, in allowed types |
| participant_id | required, valid participant |
| description | required, min_length[10] |

### 14.3 Item Complain Tidak Ada Peserta

| Field | Rule |
|---|---|
| complaint_type | required, value missing_participant |
| contingent_id | required, valid contingent |
| description | required, min_length[10] |

---

## 15. Status Lifecycle

```text
Baru -> Diproses -> Selesai
Baru -> Ditolak
Diproses -> Ditolak
Diproses -> Selesai
Baru/Diproses -> Perlu Konfirmasi
```

Definisi:

- **Baru:** complain baru masuk, belum dicek admin.
- **Diproses:** admin sedang memeriksa/memperbaiki data.
- **Selesai:** complain sudah ditindaklanjuti.
- **Ditolak:** complain tidak valid/tidak bisa diproses.
- **Perlu Konfirmasi:** admin membutuhkan info tambahan dari official.

Setiap perubahan status wajib masuk ke histori. Histori minimal menyimpan status lama, status baru, catatan, admin pengubah, dan waktu perubahan. Histori tampil di admin dan tracking tiket public.

---

## 16. Success Metrics

1. Pelatih bisa memilih kejuaraan, submit complain, dan tracking tiket tanpa bantuan panitia.
2. Panitia bisa melihat semua complain dalam satu dashboard.
3. Nama peserta yang dicomplain bisa diidentifikasi jelas lewat nama + kontingen.
4. Tidak ada submit complain tanpa keterangan.
5. Admin bisa export rekap complain.
6. Waktu rekap manual berkurang signifikan.

---

## 17. Risiko Dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Nama peserta banyak yang mirip | Salah pilih peserta | Tampilkan kontingen, kategori, kelas/nomor seni pada hasil search |
| Data sumber berubah | Data lokal tidak update | Sediakan command sync ulang |
| User submit spam | Data kotor | Terapkan rate limit wajib untuk submit/search/tracking |
| Database sumber tiap event berbeda | Import salah event | Simpan konfigurasi DB per event dan wajib mapping ke `event_id` lokal |
| Complain masuk setelah deadline | Proses tidak adil | Validasi complaint_deadline server-side dan tombol tutup manual |
| SLA terlewat tanpa terlihat | Complain tidak dikerjakan | Dashboard badge Overdue SLA + filter khusus overdue |
| Keterangan terlalu singkat | Admin bingung | Minimal 10 karakter + placeholder contoh |
| Submit banyak item gagal sebagian | Data tidak konsisten | Gunakan DB transaction |
| Kontingen tidak ditemukan | User stuck | Beri pesan hubungi panitia / opsi manual fase berikutnya |
| Tanda tangan kosong/palsu asal coret | Bukti lemah | Wajib validasi canvas tidak kosong dan simpan timestamp/hash |

---

## 18. Open Questions

1. Struktur tabel tiap database kejuaraan sama atau bisa berbeda?
2. Apakah credential database sumber disimpan permanen di project complain atau hanya dipakai saat import?
3. Apakah admin perlu login multi-user atau cukup satu akun admin awal?
4. Apakah setelah admin selesai, data di database kejuaraan diubah manual atau otomatis oleh sistem?
5. Apakah perlu upload bukti seperti KTP/akta/foto peserta pada fase berikutnya?

---

## 19. Rekomendasi Fase Development

### Phase 1 — MVP Public Submit

- Setup CI4 + Bootstrap 5.
- Buat migration tabel events, peserta, kontingen, complaint reports, complaint items.
- Buat admin CRUD kejuaraan minimal.
- Buat seed/import dummy data peserta per kejuaraan.
- Buat tracking tiket public.
- Buat rate limit submit/search/tracking.
- Buat form complain publik.
- Buat search peserta/kontingen AJAX.
- Buat submit multiple complain item.
- Buat halaman sukses nomor tiket.

### Phase 2 — Admin Dashboard

- Login admin.
- List complain.
- Detail complain.
- Update status.
- Catatan admin.
- SLA dashboard dan filter overdue.
- Histori perubahan status.

### Phase 3 — Import Database Kejuaraan

- Mapping tabel database kejuaraan per event.
- Konfigurasi koneksi database sumber per event.
- Command sync peserta/kontingen per event.
- Prevent duplikasi.
- Log hasil sync.

### Phase 4 — Export Dan Hardening

- Filter dashboard.
- Export CSV/Excel.
- Rate limit submit.
- UI polish mobile.
- Testing end-to-end.

---

## 20. Acceptance Criteria MVP Final

MVP dianggap selesai jika:

1. Admin bisa membuat kejuaraan dengan konfigurasi database sumber, batas waktu complain, dan SLA.
2. Pelatih bisa membuka form complain publik.
3. Pelatih bisa memilih kejuaraan aktif yang belum lewat deadline complain.
4. Pelatih bisa memilih jenis complain setelah memilih kejuaraan.
5. Untuk complain peserta, pelatih bisa mencari dan memilih peserta.
6. Untuk complain tidak ada peserta, pelatih bisa mencari dan memilih kontingen.
7. Pelatih bisa menambah lebih dari satu complain dalam satu submit.
8. Pelatih wajib mengisi nama official dan nomor telepon.
9. Pelatih wajib menggambar tanda tangan digital langsung di form.
10. Submit berhasil menghasilkan nomor tiket.
11. Pelatih bisa tracking tiket public dan melihat status/histori.
12. Admin bisa login dan melihat complain masuk.
13. Admin bisa membuka detail complain beserta tanda tangan.
14. Admin bisa mengubah status complain dan histori tersimpan.
15. Dashboard admin menampilkan SLA/tenggat proses dan overdue.
16. Admin bisa export data complain.
17. Data peserta/kontingen berasal dari import/sync database berbeda per kejuaraan dan terikat ke `event_id` lokal.

---

## 21. Catatan Implementasi Teknis CI4

Rekomendasi struktur:

```text
app/
  Commands/
    SyncParticipants.php
  Controllers/
    ComplaintController.php
    TrackingController.php
    Admin/AuthController.php
    Admin/EventAdminController.php
    Admin/ComplaintAdminController.php
    Api/ParticipantSearchController.php
    Api/ContingentSearchController.php
  Models/
    EventModel.php
    ParticipantModel.php
    ContingentModel.php
    ComplaintReportModel.php
    ComplaintItemModel.php
    ComplaintStatusHistoryModel.php
  Services/
    ComplaintSubmissionService.php
    ComplaintStatusService.php
    ParticipantSyncService.php
    RateLimitService.php
    SignatureService.php
  Views/
    complaints/form.php
    complaints/success.php
    complaints/track.php
    complaints/track_detail.php
    admin/events/index.php
    admin/events/form.php
    admin/auth/login.php
    admin/complaints/index.php
    admin/complaints/show.php
```

Prinsip:

- Controller tipis.
- Validasi di Controller/Request layer.
- Transaction dan business logic submit di `ComplaintSubmissionService`.
- Search endpoint return JSON kecil dan cepat, wajib scoped by `event_id`.
- Snapshot peserta dan event disimpan saat submit agar histori tidak berubah jika data peserta/event disync ulang.
- Konfigurasi database sumber per event harus disimpan aman; password terenkripsi, tidak tampil mentah di UI.
- Status update lewat `ComplaintStatusService` agar histori selalu konsisten.
- Rate limit diterapkan pada POST submit, endpoint search, dan tracking tiket.

---

## 22. Draft Nama Menu

Public:

- Form Complain Peserta
- Tambah Complain
- Simpan Complain
- Nomor Tiket
- Tracking Tiket

Admin:

- Dashboard Complain
- Daftar Complain
- Detail Complain
- Status Complain
- Export Complain
- Sinkronisasi Peserta
- Kelola Kejuaraan
- SLA / Overdue
- Histori Status

---

## 23. Prioritas Jenis Complain

Versi awal:

1. Kesalahan Nama
2. Kesalahan Jenis Kelamin
3. Kesalahan Kategori Yang Diikuti
4. Tidak Ada Peserta

Kemungkinan tambahan fase berikutnya:

1. Salah Kontingen
2. Salah Kelas Tanding
3. Salah Nomor Seni
4. Peserta Ganda/Duplikat
5. Peserta Mengundurkan Diri


---

## 24. Hal Yang Masih Kurang / Perlu Diputuskan

1. **Mapping struktur database sumber** — setiap kejuaraan pakai database berbeda; perlu pastikan struktur tabel sama atau disediakan mapping per event.
2. **Credential database sumber** — perlu mekanisme simpan terenkripsi atau input saat sync saja.
3. **Public tracking privacy** — tracking tiket public perlu batasi data yang tampil agar nomor HP dan data sensitif tidak bocor.
4. **Upload bukti** — untuk kesalahan nama/jenis kelamin kadang butuh KTP/akte/ijazah. MVP bisa tanpa upload, tapi struktur item complain perlu siap extension.
5. **Kategori complain lebih detail** — “Kesalahan kategori yang diikuti” bisa pecah menjadi salah kategori usia, salah kelas tanding, salah nomor seni, salah kontingen. Untuk MVP boleh tetap 1, tapi admin filtering lebih bagus jika dipisah.
6. **Audit log admin** — masuk MVP sebagai histori perubahan status.
7. **Anti-spam** — masuk MVP sebagai rate limit submit/search/tracking.
8. **Template import fleksibel** — kalau sumber data belum stabil, perlu mapping import yang mudah diubah.
9. **Arsip event** — event selesai tidak boleh hilang; data complain tetap bisa dibuka/export.
10. **Nomor tiket readable** — gunakan prefix event agar tidak tercampur antar kejuaraan.
11. **Role admin** — minimal admin utama, tapi nanti bisa butuh operator event tertentu saja.
12. **SLA/tenggat proses complain** — masuk MVP sebagai badge/filter overdue di dashboard admin dan info di tracking tiket.

---

## 24A. Inventaris Halaman Yang Akan Dibuat

### Public Pages

1. **Form Complain Peserta** — `/complaints`
   - Pilih kejuaraan aktif
   - Tampilkan batas waktu complain
   - Pilih jenis complain
   - Search peserta/kontingen
   - Tambah banyak item complain
   - Isi data official
   - Gambar tanda tangan digital
   - Review dan submit

2. **Halaman Sukses Submit** — `/complaints/success/{ticket}`
   - Nomor tiket
   - Ringkasan kejuaraan
   - Nama official
   - Status awal
   - Link tracking tiket

3. **Tracking Tiket** — `/complaints/track`
   - Input nomor tiket
   - Rate limit tracking

4. **Detail Tracking Tiket** — `/complaints/track/{ticket}`
   - Status saat ini
   - Public note
   - Timeline status
   - SLA/tenggat proses
   - Nomor HP disensor
   - Detail item read-only
   - Tanda tangan read-only jika kebijakan public mengizinkan

### Admin Pages

1. **Login Admin** — `/admin/login`
2. **Dashboard Complain** — `/admin/complaints`
   - Summary card
   - Filter kejuaraan/status/jenis/tanggal/kontingen
   - Badge SLA dan overdue
   - Table complain

3. **Detail Complain** — `/admin/complaints/{id}`
   - Header tiket
   - Data kejuaraan
   - Data official
   - Nomor HP penuh untuk admin
   - Tanda tangan official
   - Detail item complain
   - Timeline status
   - Form update status
   - Internal note dan public note

4. **Kelola Kejuaraan** — `/admin/events`
   - List kejuaraan
   - Status Draft/Aktif/Ditutup/Arsip
   - Batas waktu complain
   - SLA
   - Tombol tutup complain

5. **Form Kejuaraan** — `/admin/events/create`, `/admin/events/{id}/edit`
   - Data event
   - Database source config
   - Deadline complain
   - SLA hours

6. **Sync Peserta** — `/admin/events/{id}/sync` atau command `php spark complaints:sync-participants --event=ID`
   - Preview koneksi database
   - Jalankan sync
   - Log hasil sync

7. **Export Complain** — `/admin/complaints/export`
   - Export mengikuti filter aktif

### Shared/API Pages

1. **Search Peserta API** — `/api/participants/search?event_id=&q=`
2. **Search Kontingen API** — `/api/contingents/search?event_id=&q=`
3. **Rate Limit Handler** — berlaku untuk submit/search/tracking

---

## 24B. Spesifikasi Tanda Tangan Digital

Tanda tangan digital dibuat langsung oleh official pada form complain menggunakan canvas HTML5. Ini bukan upload gambar. Official menggambar tanda tangan dengan jari di HP atau mouse/touchpad di laptop.

### UX Tanda Tangan

- Area tanda tangan muncul di step **Data Official / Review**.
- Label: **Tanda Tangan Official**.
- Help text: `Silakan tanda tangan langsung pada kotak di bawah ini menggunakan jari atau mouse.`
- Tombol: **Hapus Tanda Tangan**.
- Jika kosong, tampil error: `Tanda tangan wajib diisi sebagai bukti pengajuan complain.`
- Canvas harus nyaman di HP: minimal tinggi 180px, full width, border dashed/soft.

### Teknis Frontend

- Gunakan HTML5 `<canvas>`.
- Tangkap event pointer: `pointerdown`, `pointermove`, `pointerup`, `pointercancel`.
- Support touch dan mouse.
- Saat submit, convert canvas ke PNG data URL: `canvas.toDataURL('image/png')`.
- Simpan ke hidden input `signature_image`.
- Sediakan flag `signature_is_empty` untuk validasi frontend.

### Teknis Backend

- Validasi `signature_image` wajib ada.
- Validasi format harus data URL PNG: `data:image/png;base64,...`.
- Batasi ukuran maksimal 1 MB.
- Generate `signature_hash = sha256(signature_image)`.
- Simpan `signed_at` sama dengan waktu submit.
- Tanda tangan disimpan di `complaint_reports.signature_image`.

### Keamanan Dan Privacy

- Tanda tangan dianggap data sensitif.
- Admin boleh melihat tanda tangan penuh.
- Tracking public default boleh menyembunyikan tanda tangan; jika ditampilkan, hanya read-only dan tidak bisa download langsung.
- Export CSV tidak perlu menyertakan base64 tanda tangan.
- Jika ada fitur PDF bukti submit, tanda tangan boleh dirender di PDF.

---

## 25. Arahan Desain Tema Dari DPS CI4

Tema project complain mengikuti visual language dari `/Applications/XAMPP/htdocs/dps-ci4`, terutama layout admin dan halaman pendaftaran public.

### 25.1 Brand Token

Gunakan token warna dan font berikut:

```css
:root {
  --brand-primary: #c60000;
  --brand-secondary: #ffd700;
  --brand-dark: #1a1a1a;
  --brand-soft: #fff6f6;
  --bg-color: #f4f6f9;
  --admin-border: rgba(198, 0, 0, 0.08);
  --admin-muted: #7d6670;
  --admin-shadow: 0 18px 46px rgba(74, 17, 23, 0.08);
}
```

Font:

- Body: `Poppins`
- Heading/title/brand: `Oswald`, uppercase, letter spacing ringan

CDN/font pattern mengikuti DPS CI4:

```html
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

### 25.2 Public Form Theme

Public form complain mengikuti style halaman registrasi DPS CI4:

Referensi:

- `/Applications/XAMPP/htdocs/dps-ci4/app/Views/pendaftaran/template.php`
- `/Applications/XAMPP/htdocs/dps-ci4/app/Views/pendaftaran/pages/registrasi.php`
- `/Applications/XAMPP/htdocs/dps-ci4/app/Views/pendaftaran/components/topnav.php`

Karakter visual:

- Background halaman: `#f4f6f9` atau `#fffaf9`
- Card utama: putih, radius besar `28px`, shadow halus
- Header card: gradient merah gelap ke hitam

```css
.complaint-card-header {
  padding: 1.65rem 2rem 1.35rem;
  background: linear-gradient(135deg, rgba(198, 0, 0, 0.92) 0%, rgba(122, 13, 20, 0.98) 55%, rgba(26, 26, 26, 0.98) 100%);
  color: #fff;
}

.complaint-card {
  border: 0;
  border-radius: 28px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 20px 50px rgba(33, 37, 41, 0.09);
}
```

Form style:

- Input/select radius `16px`
- Focus merah: `box-shadow: 0 0 0 0.2rem rgba(198, 0, 0, 0.12)`
- Button primary pill, merah, uppercase Oswald
- Stepper untuk flow:
  1. Pilih Kejuaraan
  2. Isi Complain
  3. Data Official
  4. Review & Simpan

### 25.3 Public Landing / Tracking Theme

Tracking tiket public boleh memakai style landing DPS CI4:

Referensi:

- `/Applications/XAMPP/htdocs/dps-ci4/app/Views/pendaftaran/pages/home.php`
- `/Applications/XAMPP/htdocs/dps-ci4/app/Views/pendaftaran/template.php`

Karakter visual:

- Navbar dark transparan blur
- Hero dark dengan aksen merah/emas
- CTA button rounded-pill
- Timeline tracking berupa card putih di atas background soft

Tracking status card:

- Status `Baru`: badge abu/biru
- Status `Diproses`: badge kuning/oranye
- Status `Perlu Konfirmasi`: badge ungu/kuning
- Status `Selesai`: badge hijau
- Status `Ditolak`: badge merah
- SLA overdue: badge merah + icon warning

Nomor telepon official tampil sensor, contoh `0812****7890`.

### 25.4 Admin Theme

Admin complain mengikuti layout admin DPS CI4:

Referensi:

- `/Applications/XAMPP/htdocs/dps-ci4/app/Views/layouts/admin.php`
- `/Applications/XAMPP/htdocs/dps-ci4/public/assets/css/admin/admin.css`

Karakter visual:

- Sidebar kiri fixed `280px`
- Sidebar background `#f8f9fa`
- Border kanan merah `4px solid #c60000`
- Main content margin-left `280px`
- Topbar card putih, radius `20px`, border-top merah
- Admin card putih, radius `20px`, shadow halus, gradient line bawah
- Nav link active memakai background merah soft

Komponen admin complain:

- `admin-shell`
- `admin-sidebar`
- `admin-main`
- `admin-topbar`
- `admin-card`
- `admin-nav-link`
- `setting-status-badge` untuk status event/complain

Dashboard complain harus punya kartu summary dengan style `admin-card`:

- Total Complain
- Baru
- Diproses
- Perlu Konfirmasi
- Selesai
- Ditolak
- Overdue SLA

### 25.5 Asset/CSS Rekomendasi Project Complain

Struktur asset yang disarankan:

```text
public/
  assets/
    css/
      complaint-theme.css
      admin.css
    js/
      complaint-form.js
      ticket-tracking.js
      signature-pad.js
      admin-complaints.js
```

`complaint-theme.css` berisi token + public components.
`admin.css` boleh adaptasi dari DPS CI4 `public/assets/css/admin/admin.css` dengan nav lebih sederhana khusus project complain.

### 25.6 Prinsip Desain

1. Tetap Bootstrap 5.
2. Jangan pakai AdminLTE; DPS CI4 sudah punya custom admin shell.
3. Pakai merah DPS sebagai primary action.
4. Pakai card besar, radius besar, shadow soft.
5. Form public mobile-first.
6. Semua label Bahasa Indonesia.
7. Watermark/tagline jika dipakai tetap English: `Powered by Digital Pencak Silat`.
8. Jangan tampilkan data sensitif di tracking public.
