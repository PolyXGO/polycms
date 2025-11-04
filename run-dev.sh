#!/bin/bash

# PolyCMS Development Helper Script

echo "🚀 Starting PolyCMS Development Servers..."
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  .env file not found!"
    echo "Creating from .env.example..."
    cp .env.example .env
    php artisan key:generate
    echo "✅ .env created"
    echo ""
fi

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "⚠️  node_modules not found!"
    echo "Installing npm dependencies..."
    npm install
    echo "✅ Dependencies installed"
    echo ""
fi

# Check if vendor exists
if [ ! -d "vendor" ]; then
    echo "⚠️  vendor directory not found!"
    echo "Installing composer dependencies..."
    composer install
    echo "✅ Dependencies installed"
    echo ""
fi

echo "📋 Starting servers..."
echo ""
echo "Terminal 1: Laravel Server (http://localhost:8000)"
echo "Terminal 2: Vite Dev Server (http://localhost:5173)"
echo ""
echo "Press Ctrl+C to stop all servers"
echo ""

# Check if concurrently is available
if command -v concurrently &> /dev/null; then
    echo "✅ Using concurrently to run both servers..."
    npx concurrently -c "green,blue" --names "Laravel,Vite" "php artisan serve" "npm run dev"
else
    echo "⚠️  concurrently not found. Please run in separate terminals:"
    echo ""
    echo "Terminal 1: php artisan serve"
    echo "Terminal 2: npm run dev"
    echo ""
    echo "Or install concurrently: npm install -g concurrently"
    echo ""
    echo "Starting Laravel server only..."
    php artisan serve
fi
