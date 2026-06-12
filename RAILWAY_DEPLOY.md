# Deploy BeDevbis API ke Railway

Panduan ini dibuat untuk project Laravel API di repo ini.

## 1. Prasyarat

- Akun Railway aktif.
- Repository project sudah di-push ke GitHub (opsional jika pakai CLI upload lokal).
- Railway CLI terpasang:

```bash
npm install -g @railway/cli
```

## 2. Siapkan service di Railway

1. Buat project baru di Railway.
2. Tambahkan service aplikasi (Deploy from GitHub atau via CLI).
3. Tambahkan service database `MySQL` di project yang sama.

## 3. Set environment variables aplikasi

Di service aplikasi, set variabel berikut:

```env
APP_NAME="BeDevbis Marketplace"
APP_ENV=production
APP_DEBUG=false
APP_KEY=<isi_dari_php_artisan_key_generate_show>
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
DB_URL=${{MySQL.MYSQL_URL}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SANCTUM_TOKEN_EXPIRY_DAYS=7
FRONTEND_URL=https://your-frontend-domain.com
```

Jika nama service database kamu bukan `MySQL`, ganti namespace `${{MySQL.*}}` sesuai nama service di Railway.

Ambil nilai `APP_KEY` dari lokal:

```bash
php artisan key:generate --show
```

## 4. Deploy aplikasi

Jika pakai CLI:

```bash
railway login
railway link
railway up
```

## 5. Jalankan migration

Di repo ini, migration juga dijalankan saat startup (`railway.toml`), tapi tetap disarankan jalankan manual sekali setelah deploy pertama:

```bash
railway run php artisan migrate --force
```

## 6. Verifikasi API hidup

1. Generate domain public di Railway (Settings -> Networking -> Generate Domain).
2. Cek health endpoint:

```bash
curl https://<domain-railway-kamu>/api/health
```

Response yang benar:

```json
{"status":"ok"}
```

## 7. Troubleshooting cepat

- Jika error `APP_KEY` kosong: pastikan variabel `APP_KEY` sudah diisi.
- Jika error database: cek semua variabel `DB_*` sudah reference ke service `MySQL`.
- Jika deploy sukses tapi endpoint mati: pastikan service expose port dari variabel `PORT` (sudah ditangani oleh `railway.toml` di repo ini).
- Jika ubah env vars: jalankan redeploy agar perubahan terbaca.
