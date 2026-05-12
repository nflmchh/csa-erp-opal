# Panduan Deployment ke Rumahweb Shared Hosting

## Prasyarat

- Akun Rumahweb dengan PHP 8.2+ dan MySQL 8.0+
- cPanel access
- FTP client (FileZilla) atau File Manager cPanel
- SSH terminal (aktifkan di cPanel → SSH Access jika tersedia)

---

## 1. Persiapan Lokal

### 1a. Build assets produksi

```bash
npm run build
```

File hasil build ada di `public/build/` — sudah di-commit ke repo.

### 1b. Hapus cache development

```bash
php artisan optimize:clear
```

### 1c. Siapkan `.env.production` (jangan commit ke git)

Salin `.env` dan ubah nilai-nilai berikut:

```env
APP_NAME="SevenKey ERP"
APP_ENV=production
APP_KEY=            # akan di-generate di server
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=namadb_dari_cpanel
DB_USERNAME=namauser_dari_cpanel
DB_PASSWORD=password_db

CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=480

MAIL_MAILER=smtp
# (opsional — isi jika butuh fitur email)
```

> **Catatan:** Di shared hosting tanpa Redis, gunakan `CACHE_STORE=file` dan `QUEUE_CONNECTION=sync`.

---

## 2. Upload File ke Server

### Opsi A: Via File Manager cPanel (direkomendasikan jika tanpa SSH)

1. Buka cPanel → **File Manager**
2. Masuk ke folder `public_html/` (atau subdomain folder)
3. Upload semua file proyek **kecuali**:
   - `node_modules/`
   - `.git/`
   - `storage/logs/*.log`
4. Upload folder `public/` isinya ke **root domain** (public_html), bukan subfolder:

   **Struktur yang benar di server:**
   ```
   ~/                          ← home directory
   ├── erp_ecosystem/          ← semua file Laravel (app, config, routes, dll)
   │   ├── app/
   │   ├── bootstrap/
   │   ├── config/
   │   ├── database/
   │   ├── resources/
   │   ├── routes/
   │   ├── storage/
   │   ├── vendor/
   │   └── public/             ← ISI folder ini dipindah ke public_html
   └── public_html/            ← document root domain
       ├── index.php           ← dari public/index.php (dimodifikasi)
       ├── build/              ← dari public/build/
       └── .htaccess           ← dari public/.htaccess
   ```

5. Edit `public_html/index.php` — ubah path ke Laravel root:

   ```php
   // Ganti baris ini:
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';

   // Menjadi (sesuaikan dengan path di server):
   require __DIR__.'/../erp_ecosystem/vendor/autoload.php';
   $app = require_once __DIR__.'/../erp_ecosystem/bootstrap/app.php';
   ```

### Opsi B: Via Git + SSH (jika ada SSH terminal)

```bash
cd ~/
git clone https://github.com/username/erp_ecosystem.git
cd erp_ecosystem
composer install --no-dev --optimize-autoloader
```

Kemudian symlink `public_html`:
```bash
rm -rf ~/public_html
ln -s ~/erp_ecosystem/public ~/public_html
```

---

## 3. Setup di Server

### 3a. Upload `.env` production

Upload file `.env` ke root proyek (`~/erp_ecosystem/.env`).

### 3b. Buat database di cPanel

1. cPanel → **MySQL Databases**
2. Buat database baru → catat nama database
3. Buat user MySQL → catat username dan password
4. Assign user ke database → pilih **All Privileges**
5. Update `.env` dengan nilai-nilai tersebut

### 3c. Jalankan perintah artisan via SSH

```bash
cd ~/erp_ecosystem

# Generate app key
php artisan key:generate

# Jalankan migration
php artisan migrate --force

# Jalankan seeder
php artisan db:seed --force

# Optimize
php artisan optimize
php artisan view:cache
```

### 3d. Jika tidak ada SSH — gunakan PHP runner via cPanel

Buat file sementara `~/erp_ecosystem/run_setup.php`:

```php
<?php
// Akses sekali lewat browser lalu hapus file ini
$output = shell_exec('cd ' . __DIR__ . ' && php artisan migrate --force 2>&1');
echo '<pre>' . $output . '</pre>';

$output2 = shell_exec('cd ' . __DIR__ . ' && php artisan db:seed --force 2>&1');
echo '<pre>' . $output2 . '</pre>';

$output3 = shell_exec('cd ' . __DIR__ . ' && php artisan optimize 2>&1');
echo '<pre>' . $output3 . '</pre>';
```

Akses via browser sekali, lalu **hapus file tersebut segera**.

---

## 4. Permission Storage

```bash
chmod -R 775 ~/erp_ecosystem/storage
chmod -R 775 ~/erp_ecosystem/bootstrap/cache
```

Via File Manager cPanel: klik kanan folder `storage` → **Change Permissions** → centang semua Write untuk Owner dan Group.

---

## 5. Konfigurasi .htaccess

Pastikan `public_html/.htaccess` berisi (sudah ada dari Laravel):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## 6. HTTPS / SSL

Di cPanel → **SSL/TLS** atau **Let's Encrypt SSL**:
1. Install SSL untuk domain
2. Aktifkan **Force HTTPS Redirect**

---

## 7. Verifikasi Deployment

Buka browser dan cek:
- [ ] `https://yourdomain.com` → redirect ke halaman login
- [ ] Login sebagai superadmin (sesuai seeder)
- [ ] Dashboard tampil normal
- [ ] Buka `/products` → daftar produk muncul
- [ ] Buka `/pos` → POS terminal tampil
- [ ] Buka `/finance` → dashboard keuangan tampil

---

## 8. Checklist Sebelum Go Live

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production` di `.env`
- [ ] File `.env` tidak dapat diakses publik (test: `curl https://yourdomain.com/.env` harus 404/403)
- [ ] Folder `storage/` dan `bootstrap/cache/` writable
- [ ] `php artisan optimize` sudah dijalankan
- [ ] Assets di `public/build/` sudah terupload
- [ ] Timezone sesuai: `APP_TIMEZONE=Asia/Jakarta` di `.env`
- [ ] Ganti password default seeder segera setelah login pertama

---

## 9. Update ke Versi Baru

Setiap deploy ulang:

```bash
cd ~/erp_ecosystem
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

Jika ada perubahan asset (CSS/JS), build dulu di lokal lalu upload folder `public/build/` ke server.

---

## Catatan Khusus Rumahweb

- PHP version: pastikan pilih **PHP 8.2** atau **8.3** di cPanel → **Select PHP Version**
- Aktifkan extension: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Jika `composer install` lambat / timeout, upload folder `vendor/` langsung via FTP
- Max upload size: sesuaikan di `php.ini` custom → `upload_max_filesize = 20M` dan `post_max_size = 20M`
