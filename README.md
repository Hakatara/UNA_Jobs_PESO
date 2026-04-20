# UNA_Jobs_PESO

Local setup notes for running this project again after a long time.

## Requirements

- XAMPP (Apache + MySQL)
- PHP CLI available in terminal
- Composer

## Local Environment (.env)

Use local XAMPP MySQL credentials in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

## Database Setup

Run these commands from project root:

```
php artisan migrate --seed
```

If a migration fails on `2023_07_19_152743_migrate_old_city_state_image.php` due to missing `Botble\Location\Models\City`, keep that migration safe for local by making its `up()` method no-op (or guard it).

If full seeding fails because some plugin models are unavailable, seed core data first and skip problematic seeders in `database/seeders/DatabaseSeeder.php` for local setup.

## PHP Extensions Needed

Image upload and some package installs require these PHP extensions:

- gd
- zip

Enable them in `C:\xampp\php\php.ini`:

```
extension=gd
extension=zip
```

Then restart Apache from XAMPP Control Panel.

## Admin/Theme URLs

On this local XAMPP setup, these URLs work:

- Login: `http://localhost/UNA_Jobs_PESO/public/admin/login`
- Theme options: `http://localhost/UNA_Jobs_PESO/public/admin/theme/options`

If `http://localhost/UNA_Jobs_PESO/admin/...` returns 404, use the `/public` URL variant or configure an Apache VirtualHost that points DocumentRoot to:

`C:/xampp/htdocs/UNA_Jobs_PESO/public`

## Useful Commands

```
php artisan route:clear
php artisan config:clear
php artisan cache:clear
composer install
composer dump-autoload
```

## Notes

- This project uses Botble CMS modules under `platform/`.
- Admin and theme customization routes are package routes, not only in `routes/web.php`.