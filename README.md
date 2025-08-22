# Laravel Setup Guide

This guide walks you through setting up a Laravel project with PostgreSQL, Filament, and Filament Shield.

---

## Prerequisites

- Ubuntu or Debian-based system
- Docker
- PHP >= 8.1
- Node.js & npm
- Composer

---

## 1. Install PHP and Required Extensions

```bash
sudo apt update
sudo apt install php-cli php-mbstring php-xml php-curl php-zip unzip curl -y
sudo apt install php-pgsql -y
sudo apt install php-intl -y   # or sudo apt install php8.3-intl -y
```

Check PHP version:

```bash
php -v
```

---

## 2. Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 3. Install Node.js & npm

```bash
sudo apt install nodejs npm -y
node -v
npm -v
```

---

## 4. Run Docker PostgreSQL

```bash
docker run --name laravel-postgres \
  -e POSTGRES_USER=laravel \
  -e POSTGRES_PASSWORD=secret \
  -e POSTGRES_DB=laravel_db \
  -p 5432:5432 \
  -d postgres:16
```

---

## 5. Configure Environment Variables

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Update database variables in `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_db
DB_USERNAME=laravel
DB_PASSWORD=secret
```

---

## 6. Install Dependencies

```bash
composer install
npm install
```

---

## 7. Generate App Key

```bash
php artisan key:generate
```

---

## 8. Run Database Migrations

```bash
php artisan migrate
```

---

## 9. Run Laravel Server & Frontend

Start Laravel server:

```bash
php artisan serve
```

Start frontend (Vite):

```bash
npm run dev
```

---

## 10. Create Filament Admin User

If no user exists:

```bash
php artisan make:filament-user
```

Follow the prompts to create a user.

---

## 11. Install Filament Shield

```bash
composer require bezhansalleh/filament-shield:4.x-dev
```

After installation, run the following commands:

```bash
# Run migrations
php artisan migrate

# Install Shield
php artisan shield:install --fresh

# Create super admin user
php artisan shield:super-admin
```

Follow the prompts to select a user ID for the super admin.

---

## 12. Assign Permissions

Go to:

```
http://127.0.0.1:8000/panel/shield/roles
```

Edit the super admin role and select **all permissions**.

---

## Notes

- Make sure Docker is running before executing database-related commands.
- Node.js version should be compatible with Laravel Vite.
- Always run `composer install` and `npm install` when pulling updates from the repository.

---

This completes the setup for Laravel with PostgreSQL, Filament, and Shield.



## Running the Project

After completing all of the setup steps, you need to run the following commands to start the project:

```bash
# Start Vite for frontend assets
npm run dev

# Serve the Laravel backend
php artisan serve

## Running Migrations

If you need to apply or reapply database migrations, run:

```bash
php artisan migrate
