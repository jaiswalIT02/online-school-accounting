#!/bin/bash

# Laravel Deployment Script
# Run this script on your server after uploading files

echo "🚀 Starting Laravel Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    echo "Please create .env file first:"
    echo "cp .env.example .env"
    echo "nano .env"
    exit 1
fi

echo -e "${GREEN}✓ .env file found${NC}"

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Composer install failed!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
    echo -e "${GREEN}✓ Application key generated${NC}"
fi

# Set permissions
echo "🔐 Setting permissions..."
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"

# Run migrations
echo "🗄️  Running database migrations..."
read -p "Run migrations? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Migrations failed!${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ Migrations completed${NC}"
fi

# Cache for production
echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Production cache created${NC}"

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "Next steps:"
echo "1. Verify your .env file has correct database credentials"
echo "2. Check web server configuration"
echo "3. Visit your domain in browser to test"
