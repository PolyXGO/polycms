#!/bin/bash

echo "🚀 PolyCMS Setup Script"
echo "======================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✅ .env created"
else
    echo "✅ .env already exists"
fi

# Generate application key
echo ""
echo "🔑 Generating application key..."
php artisan key:generate

# Run migrations
echo ""
echo "📦 Running migrations..."
php artisan migrate

# Run seeders
echo ""
echo "🌱 Running seeders..."
php artisan db:seed

# Create storage link
echo ""
echo "📁 Creating storage link..."
php artisan storage:link

# Dump autoload
echo ""
echo "🔄 Dumping autoload..."
composer dump-autoload

echo ""
echo "✅ Setup complete!"
echo ""
echo "📋 Default credentials:"
echo "   Email: admin@example.com"
echo "   Password: password"
echo ""
echo "🌐 Start development server:"
echo "   php artisan serve"
echo ""
echo "🎨 Start frontend dev server (in another terminal):"
echo "   npm run dev"
echo ""
