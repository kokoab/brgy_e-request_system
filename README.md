# Boarding House Finder and Management System

A comprehensive Laravel-based system for finding and managing boarding houses with user and admin roles.

## Features

- **User Authentication**: Email/password authentication with Laravel Sanctum
- **Role-Based Access**: User and Admin roles
- **Property Management**: Users can add their own properties
- **Property Browsing**: Search and filter boarding houses
- **Map Integration**: Leaflet.js for location visualization
- **Image Uploads**: Multiple images per property
- **Reviews & Ratings**: Users can review properties
- **Favorites**: Bookmark favorite properties
- **Booking System**: Contact/booking functionality
- **Admin Dashboard**: Approve/reject properties, manage listings

## Requirements

- Docker and Docker Compose
- PHP 8.1+
- Composer
- Node.js and NPM

## Installation

### Quick Setup (Automated)

Run the setup script:
```bash
./setup.sh
```

### Manual Setup

1. **Create `.env` file** (copy from `.env.example` if it exists, or create with these settings):
   ```env
   APP_NAME=BoardingHouseFinder
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost:8080

   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=boarding_house_db
   DB_USERNAME=root
   DB_PASSWORD=root
   ```

2. **Build and start Docker containers**:
   ```bash
   docker-compose up -d --build
   ```

3. **Install PHP dependencies** (inside container):
   ```bash
   docker-compose exec app composer install
   ```

4. **Generate application key**:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. **Run migrations and seed database**:
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

6. **Create storage link**:
   ```bash
   docker-compose exec app php artisan storage:link
   ```

7. **Set permissions**:
   ```bash
   docker-compose exec app chmod -R 775 storage bootstrap/cache
   ```

8. **Install NPM dependencies**:
   ```bash
   npm install
   ```

9. **Build assets**:
   ```bash
   npm run build
   ```

## Access Points

- **Application**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
- **Database**: localhost:3306

## Default Admin Account

After seeding:
- Email: admin@example.com
- Password: password

## Usage

1. Register as a user or login
2. Browse properties or add your own
3. View properties on map
4. Add reviews and favorites
5. Contact property owners

