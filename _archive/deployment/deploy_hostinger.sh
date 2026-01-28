#!/bin/bash

# Hostinger Deployment Script for akasa.bihtech.com
# Run this script on Hostinger server via SSH

echo "🚀 Starting Laravel CRM Deployment..."

# Step 1: Navigate to project directory
cd /home/u188221078/domains/bihtech.com/public_html/akasa
echo "✅ Changed to project directory"

# Step 2: Git Pull
echo "📥 Pulling latest code from GitHub..."
git pull origin main

# Step 3: Composer Install
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Step 4: Check if .env exists, if not create from example
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "⚠️  Please edit .env file with your database credentials!"
fi

# Step 5: Generate App Key (if not set)
php artisan key:generate --force

# Step 6: Set Permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R u188221078:u188221078 storage bootstrap/cache

# Step 7: Create Storage Link
php artisan storage:link

# Step 8: Run Migrations (commented out - run manually after .env setup)
# php artisan migrate --force

# Step 9: Cache Clear and Optimize
echo "⚡ Optimizing application..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "✅ Deployment complete!"
echo "📋 Next steps:"
echo "   1. Edit .env file with database credentials"
echo "   2. Run: php artisan migrate"
echo "   3. Run: php artisan db:seed"
echo "   4. Create admin user via: php artisan tinker"
