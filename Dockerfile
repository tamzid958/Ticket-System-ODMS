FROM php:8.4

# Install dependencies
# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    unzip \
    git \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd intl zip

# Install Node.js (you can change to 18.x or 20.x if needed)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs    

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .


RUN composer install
RUN npm install
RUN npm run build

# Expose ports:
# - 8000 for Laravel (php artisan serve)
# - 5173 for Vite (default HMR port)
EXPOSE 8000 5173

RUN chmod +x /app/entrypoint.sh
ENTRYPOINT ["/app/entrypoint.sh"]

# Start both servers
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=8000"]