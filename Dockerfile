FROM php:8.4-cli

RUN apt-get update && apt-get install -y unzip git libzip-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql zip

WORKDIR /app

COPY composer.json composer.lock ./
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --no-dev --optimize-autoloader

COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "."]
