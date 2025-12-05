#!/bin/bash

echo "Setting up Boarding House Finder System..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env 2>/dev/null || echo "Please create .env file manually"
fi

echo "Building Docker containers..."
docker-compose up -d --build

echo "Waiting for database to be ready..."
sleep 10

echo "Installing PHP dependencies..."
docker-compose exec -T app composer install

echo "Generating application key..."
docker-compose exec -T app php artisan key:generate

echo "Running migrations..."
docker-compose exec -T app php artisan migrate --force

echo "Seeding database..."
docker-compose exec -T app php artisan db:seed --force

echo "Creating storage link..."
docker-compose exec -T app php artisan storage:link

echo "Setting permissions..."
docker-compose exec -T app chmod -R 775 storage bootstrap/cache

echo "Installing NPM dependencies..."
npm install

echo "Building assets..."
npm run build

echo "Setup complete!"
echo "Access the application at: http://localhost:8080"
echo "Access PHPMyAdmin at: http://localhost:8081"
echo ""
echo "Default admin credentials:"
echo "Email: admin@example.com"
echo "Password: password"

