# DPS Complain Production Runbook

## Server requirement

- PHP 8.2+
- Composer 2.x
- MySQL/MariaDB
- Web server document root wajib ke `public/`
- `writable/` bisa ditulis user web server

## Deploy

```bash
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php spark key:generate
```

Edit `.env`:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://domain-produksi/'
app.forceGlobalSecureRequests = true
database.default.hostname = 127.0.0.1
database.default.database = dps_complain
database.default.username = dps_complain_user
database.default.password = PASSWORD_DB_PRODUCTION
database.default.DBDebug = false
```

## Database

```bash
php spark migrate
php spark migrate:status
```

Jangan jalankan `InitialSeeder` di production. Seeder itu hanya data contoh lokal.
Buat admin production dengan password unik:

```bash
php spark complaints:create-admin admin "Admin Panitia"
```

Password minimal 6 karakter.

## Sync peserta

```bash
php spark complaints:sync-participants --event=ID_EVENT
```

Gunakan tombol Sync dari admin hanya setelah data event dan DB sumber benar.

## Smoke test setelah deploy

1. Buka `/complaints` dan pastikan form tampil.
2. Login `/admin/login`.
3. Buat/cek event active.
4. Sync peserta event.
5. Submit complain dari public.
6. Tracking tiket complain.
7. Submit mode "Tidak Ada Complain" untuk kontingen tanpa complain aktif.
8. Cek admin report, print, excel, dan export CSV.
9. Update status complain.
10. Logout admin.

## Production safety

- Jangan commit `.env`.
- Backup database sebelum `php spark migrate`.
- Pastikan HTTPS aktif.
- Pastikan `writable/logs` tidak public.
- Rotasi password admin setelah event selesai bila akses dibagi ke panitia.
