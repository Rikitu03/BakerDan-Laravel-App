# BAKERDAN Vue SPA - Installation Guide

Beautiful Vue 3 + Tailwind CSS Single Page Application for Laravel bakery e-commerce.

## 📋 Features

✅ **5 Complete Pages:**
- Customer Home (Product Catalog)
- Cart Page (Product Detail + Cart List)
- Customize Order Page
- User Settings (Personal Info + Security)
- Beautiful warm peachy bakery aesthetic

✅ **Tech Stack:**
- Vue 3 (Composition API)
- Vue Router 4
- Tailwind CSS 3
- Axios for API calls
- Vite for build tooling
- Laravel backend integration

## 🚀 Installation Steps

### 1. Copy Files to Your Laravel Project

```bash
# Navigate to your Laravel project root
cd /path/to/your/laravel/project

# Create resources/js directory if it doesn't exist
mkdir -p resources/js

# Copy all Vue SPA files
cp -r /home/claude/bakerdan-spa/* resources/js/

# Move the blade template to views
mkdir -p resources/views/customer
cp resources/js/blade-template.blade.php resources/views/customer/app.blade.php

# Copy routes file (backup your existing routes first!)
cp your/current/routes/web.php routes/web.php.backup
cp resources/js/routes-web.php routes/web.php

# Copy controllers
cp resources/js/CustomerController.php app/Http/Controllers/
mkdir -p app/Http/Controllers/Api
cp resources/js/ProductController-api.php app/Http/Controllers/Api/ProductController.php
cp resources/js/CartController-api.php app/Http/Controllers/Api/CartController.php
cp resources/js/CustomOrderController-api.php app/Http/Controllers/Api/CustomOrderController.php
cp resources/js/ProfileController-api.php app/Http/Controllers/Api/ProfileController.php
```

### 2. Install Node Dependencies

```bash
# Install Vue and dependencies
npm install vue@^3.4.21 vue-router@^4.3.0 axios@^1.6.7

# Install Vite and plugins
npm install -D @vitejs/plugin-vue@^5.0.4 vite@^5.1.4

# Install Tailwind CSS
npm install -D tailwindcss@^3.4.1 postcss@^8.4.35 autoprefixer@^10.4.18
```

### 3. Update Laravel Configuration

**vite.config.js** (create in project root):
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/main.js'],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
})
```

**package.json** - Add these scripts:
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  }
}
```

### 4. Update Blade Template

Edit `resources/views/customer/app.blade.php`:
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BAKERDAN - Artisan Bakery</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Urbanist:wght@600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/js/main.js'])
</head>
<body>
    <div id="app"></div>
    
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            user: @json($customer)
        };
    </script>
</body>
</html>
```

### 5. File Structure

Your Laravel project should now have:

```
your-laravel-project/
├── app/
│   └── Http/
│       └── Controllers/
│           ├── CustomerController.php
│           └── Api/
│               ├── ProductController.php
│               ├── CartController.php
│               ├── CustomOrderController.php
│               └── ProfileController.php
├── resources/
│   ├── js/
│   │   ├── App.vue
│   │   ├── main.js
│   │   ├── assets/
│   │   │   └── styles.css
│   │   ├── components/
│   │   │   ├── layout/
│   │   │   │   ├── CustomerLayout.vue
│   │   │   │   ├── AppHeader.vue
│   │   │   │   └── AppSidebar.vue
│   │   │   ├── pages/
│   │   │   │   ├── HomePage.vue
│   │   │   │   ├── CartPage.vue
│   │   │   │   ├── CustomizePage.vue
│   │   │   │   └── SettingsPage.vue
│   │   │   └── shared/
│   │   │       ├── ProductCard.vue
│   │   │       └── CartItem.vue
│   │   ├── router/
│   │   │   └── index.js
│   │   └── services/
│   │       └── api.js
│   └── views/
│       └── customer/
│           └── app.blade.php
├── routes/
│   └── web.php
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
└── package.json
```

## 🎨 Development Workflow

### Start Development Server

```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite frontend (with HMR)
npm run dev
```

Now visit: `http://localhost:8000/customer`

### Building for Production

```bash
# Build optimized assets
npm run build

# Assets will be in public/build/
```

## 🔧 API Endpoints

The SPA communicates with these Laravel API endpoints:

```
GET    /api/products              - List products
GET    /api/products/{id}         - Product details
GET    /api/categories            - List categories
GET    /api/cart                  - Get cart items
POST   /api/cart/add/{id}         - Add to cart
PUT    /api/cart/items/{id}       - Update cart item
DELETE /api/cart/items/{id}       - Remove from cart
DELETE /api/cart/clear             - Clear cart
POST   /api/custom-orders         - Create custom order
GET    /api/profile               - Get profile
PUT    /api/profile               - Update profile
PUT    /api/profile/password      - Update password
```

## 🎯 Next Steps

1. **Database Integration**: Replace mock data in API controllers with actual database queries
2. **Image Storage**: Set up proper image storage for products and custom orders
3. **Authentication**: Ensure your auth middleware works correctly
4. **Payment Integration**: Add payment gateway for checkout
5. **Order Management**: Create admin panel for managing orders

## 🐛 Troubleshooting

**Issue: "Vite manifest not found"**
```bash
npm run build
```

**Issue: "Module not found"**
```bash
npm install
```

**Issue: CSRF token mismatch**
Make sure meta tag is present in blade template:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## 🎨 Customization

### Colors
Edit `tailwind.config.js` to customize the warm bakery color palette:
```javascript
colors: {
  primary: {
    500: '#C9876C',  // Main brand color
    600: '#B8765B',  // Hover state
  }
}
```

### Fonts
Current fonts: Poppins (body) + Urbanist (headings)
Change in `App.vue` and `index.html`

## 📱 Responsive Design

All pages are fully responsive with:
- Mobile-first approach
- Tablet breakpoints
- Desktop optimization

## 🔒 Security

- CSRF protection on all API calls
- Authentication middleware
- Role-based access (customer only)
- Input validation on all forms

## 📞 Support

For issues or questions, check:
- Vue 3 docs: https://vuejs.org
- Vite docs: https://vitejs.dev
- Tailwind docs: https://tailwindcss.com

---

**Enjoy your beautiful BAKERDAN SPA!** 🍞✨
