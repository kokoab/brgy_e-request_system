# Setup Instructions

## Prerequisites

- Docker and Docker Compose installed
- Node.js and NPM installed
- Git (optional)

## Step-by-Step Setup

### 1. Create Environment File

Create a `.env` file in the root directory with the following content:

```env
APP_NAME=BoardingHouseFinder
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=boarding_house_db
DB_USERNAME=root
DB_PASSWORD=root

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### 2. Start Docker Containers

```bash
docker-compose up -d --build
```

This will start:
- Laravel application (PHP-FPM)
- Nginx web server
- MySQL database
- PHPMyAdmin

### 3. Install Dependencies

```bash
# PHP dependencies
docker-compose exec app composer install

# Node.js dependencies
npm install
```

### 4. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 5. Run Database Migrations

```bash
docker-compose exec app php artisan migrate --seed
```

This will create all tables and seed initial data including:
- Admin user (admin@example.com / password)
- Sample users
- Sample properties

### 6. Create Storage Link

```bash
docker-compose exec app php artisan storage:link
```

### 7. Set Permissions

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### 8. Build Frontend Assets

```bash
npm run build
```

## Access the Application

- **Main Application**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
  - Server: `db`
  - Username: `root`
  - Password: `root`

## Default Credentials

After seeding:
- **Admin**: admin@example.com / password
- **Regular Users**: Check database or create new ones

## Troubleshooting

### Container won't start
- Check if ports 8080, 8081, and 3306 are available
- Ensure Docker is running

### Database connection errors
- Wait a few seconds after starting containers for MySQL to initialize
- Check database credentials in `.env`

### Permission errors
- Run: `docker-compose exec app chmod -R 775 storage bootstrap/cache`

### Composer errors
- Ensure you're running commands inside the Docker container
- Try: `docker-compose exec app composer install --no-interaction`

## Development Commands

```bash
# View logs
docker-compose logs -f app

# Access container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan [command]

# Rebuild containers
docker-compose down
docker-compose up -d --build
```

