# Coding challenge 

## Getting Started

### Laravel Setup key, databases, and fake data for albums
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class AlbumSeeder
```
# Use this command to run the backend. This is to match the localhost that the front end use for sanctum
```bash 
php artisan serve --host=localhost --port=8000
```