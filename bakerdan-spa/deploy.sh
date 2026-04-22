#!/bin/bash

# BAKERDAN SPA Deployment Script
# This script helps deploy the Vue SPA to your Laravel project

set -e

echo "🍞 BAKERDAN SPA Deployment Script"
echo "=================================="
echo ""

# Check if Laravel project path is provided
if [ -z "$1" ]; then
    echo "Usage: ./deploy.sh /path/to/your/laravel/project"
    echo "Example: ./deploy.sh ~/Projects/bakerdan-laravel"
    exit 1
fi

LARAVEL_PATH="$1"
SPA_SOURCE="/home/claude/bakerdan-spa"

# Verify Laravel project exists
if [ ! -d "$LARAVEL_PATH" ]; then
    echo "❌ Error: Laravel project not found at $LARAVEL_PATH"
    exit 1
fi

if [ ! -f "$LARAVEL_PATH/artisan" ]; then
    echo "❌ Error: Not a valid Laravel project (artisan file not found)"
    exit 1
fi

echo "📂 Laravel project: $LARAVEL_PATH"
echo "📦 Source: $SPA_SOURCE"
echo ""

# Backup existing files
echo "💾 Creating backups..."
if [ -f "$LARAVEL_PATH/routes/web.php" ]; then
    cp "$LARAVEL_PATH/routes/web.php" "$LARAVEL_PATH/routes/web.php.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✅ Backed up routes/web.php"
fi

# Create directories
echo ""
echo "📁 Creating directories..."
mkdir -p "$LARAVEL_PATH/resources/js"
mkdir -p "$LARAVEL_PATH/resources/views/customer"
mkdir -p "$LARAVEL_PATH/app/Http/Controllers/Api"
echo "✅ Directories created"

# Copy Vue SPA files
echo ""
echo "📋 Copying Vue SPA files..."
cp -r "$SPA_SOURCE/components" "$LARAVEL_PATH/resources/js/"
cp -r "$SPA_SOURCE/router" "$LARAVEL_PATH/resources/js/"
cp -r "$SPA_SOURCE/services" "$LARAVEL_PATH/resources/js/"
cp -r "$SPA_SOURCE/assets" "$LARAVEL_PATH/resources/js/"
cp "$SPA_SOURCE/App.vue" "$LARAVEL_PATH/resources/js/"
cp "$SPA_SOURCE/main.js" "$LARAVEL_PATH/resources/js/"
echo "✅ Vue files copied"

# Copy blade template
echo ""
echo "📄 Copying blade template..."
cp "$SPA_SOURCE/blade-template.blade.php" "$LARAVEL_PATH/resources/views/customer/app.blade.php"
echo "✅ Blade template copied"

# Copy controllers
echo ""
echo "🎮 Copying controllers..."
cp "$SPA_SOURCE/CustomerController.php" "$LARAVEL_PATH/app/Http/Controllers/"
cp "$SPA_SOURCE/ProductController-api.php" "$LARAVEL_PATH/app/Http/Controllers/Api/ProductController.php"
cp "$SPA_SOURCE/CartController-api.php" "$LARAVEL_PATH/app/Http/Controllers/Api/CartController.php"
cp "$SPA_SOURCE/CustomOrderController-api.php" "$LARAVEL_PATH/app/Http/Controllers/Api/CustomOrderController.php"
cp "$SPA_SOURCE/ProfileController-api.php" "$LARAVEL_PATH/app/Http/Controllers/Api/ProfileController.php"
echo "✅ Controllers copied"

# Copy routes
echo ""
echo "🛣️  Copying routes..."
cp "$SPA_SOURCE/routes-web.php" "$LARAVEL_PATH/routes/web.php"
echo "✅ Routes updated"

# Copy config files
echo ""
echo "⚙️  Copying config files..."
cp "$SPA_SOURCE/package.json" "$LARAVEL_PATH/package.json"
cp "$SPA_SOURCE/vite.config.js" "$LARAVEL_PATH/vite.config.js"
cp "$SPA_SOURCE/tailwind.config.js" "$LARAVEL_PATH/tailwind.config.js"
cp "$SPA_SOURCE/postcss.config.js" "$LARAVEL_PATH/postcss.config.js"
echo "✅ Config files copied"

# Copy documentation
echo ""
echo "📚 Copying documentation..."
cp "$SPA_SOURCE/INSTALLATION.md" "$LARAVEL_PATH/BAKERDAN_SPA_INSTALLATION.md"
echo "✅ Documentation copied"

echo ""
echo "=================================="
echo "✅ Deployment Complete!"
echo "=================================="
echo ""
echo "📝 Next steps:"
echo "1. cd $LARAVEL_PATH"
echo "2. npm install"
echo "3. npm run dev"
echo "4. php artisan serve"
echo "5. Visit: http://localhost:8000/customer"
echo ""
echo "📖 See BAKERDAN_SPA_INSTALLATION.md for full documentation"
echo ""
