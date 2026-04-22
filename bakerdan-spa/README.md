# 🍞 BAKERDAN Vue SPA

**Beautiful Single Page Application for Laravel Bakery E-commerce**

Built with Vue 3, Tailwind CSS, and Laravel backend integration. Pixel-perfect recreation of your Figma designs with a warm, inviting bakery aesthetic.

![BAKERDAN Preview](https://via.placeholder.com/1200x600/C9876C/FFFFFF?text=BAKERDAN+SPA)

## ✨ Features

### 🎨 **5 Complete Pages**
- **Customer Home** - Product catalog with grid layout, categories, filters
- **Cart Page** - Split view with product detail + cart management
- **Customize Order** - Custom order form with image upload
- **User Settings** - Personal info and security settings
- **Beautiful UI** - Warm peachy color palette, smooth animations

### 🛠️ **Technology Stack**
- **Vue 3** (Composition API with `<script setup>`)
- **Vue Router 4** (Client-side routing)
- **Tailwind CSS 3** (Utility-first styling)
- **Axios** (API communication)
- **Vite** (Lightning-fast build tool)
- **Laravel** (Backend API)

### 🎯 **Key Features**
- ✅ Fully responsive design (mobile, tablet, desktop)
- ✅ Real-time cart management
- ✅ Custom order form with image upload
- ✅ User authentication & profile management
- ✅ Password strength indicator
- ✅ Beautiful transitions and animations
- ✅ CSRF protection
- ✅ Clean, maintainable code structure

## 🚀 Quick Start

### Option 1: Automated Deployment (Recommended)

```bash
# Run the deployment script
./deploy.sh /path/to/your/laravel/project

# Install dependencies
cd /path/to/your/laravel/project
npm install

# Start development servers
npm run dev        # Terminal 1
php artisan serve  # Terminal 2

# Visit http://localhost:8000/customer
```

### Option 2: Manual Installation

See [INSTALLATION.md](./INSTALLATION.md) for detailed step-by-step instructions.

## 📁 Project Structure

```
bakerdan-spa/
├── components/
│   ├── layout/
│   │   ├── CustomerLayout.vue    # Main SPA layout
│   │   ├── AppHeader.vue         # Navigation header
│   │   └── AppSidebar.vue        # Category sidebar
│   ├── pages/
│   │   ├── HomePage.vue          # Product catalog
│   │   ├── CartPage.vue          # Cart management
│   │   ├── CustomizePage.vue     # Custom orders
│   │   └── SettingsPage.vue      # User settings
│   └── shared/
│       ├── ProductCard.vue       # Product grid item
│       └── CartItem.vue          # Cart list item
├── router/
│   └── index.js                  # Vue Router config
├── services/
│   └── api.js                    # Axios API service
├── assets/
│   └── styles.css                # Tailwind CSS
├── App.vue                       # Root component
├── main.js                       # Vue app entry
└── [config files]
```

## 🎨 Design System

### Color Palette
```css
Primary: #C9876C (Warm Peach)
Hover: #B8765B (Dark Peach)
Dark: #8B6F47 (Brown)
Background: #F9FAFB (Light Gray)
```

### Typography
- **Headings**: Urbanist (bold, modern)
- **Body**: Poppins (clean, readable)

### Components
- Rounded corners (0.5rem - 2rem)
- Soft shadows for depth
- Smooth transitions (200-300ms)
- Hover effects on interactive elements

## 🔌 API Integration

### Available Endpoints

**Products**
```
GET    /api/products              # List all products
GET    /api/products/{id}         # Get product details
GET    /api/categories            # List categories
```

**Cart**
```
GET    /api/cart                  # Get cart items
POST   /api/cart/add/{id}         # Add to cart
PUT    /api/cart/items/{id}       # Update quantity
DELETE /api/cart/items/{id}       # Remove item
DELETE /api/cart/clear             # Clear cart
```

**Custom Orders**
```
POST   /api/custom-orders         # Create custom order
```

**User Profile**
```
GET    /api/profile               # Get user data
PUT    /api/profile               # Update profile
PUT    /api/profile/password      # Change password
```

## 📱 Responsive Breakpoints

```javascript
sm: 640px   // Mobile landscape
md: 768px   // Tablet
lg: 1024px  // Desktop
xl: 1280px  // Large desktop
```

## 🔒 Security Features

- CSRF token validation on all requests
- Authentication middleware
- Role-based access control (customer only)
- Password strength validation
- Secure password hashing
- Input sanitization

## 🧪 Development

### Running Development Server

```bash
# Start Vite dev server with HMR
npm run dev

# Start Laravel backend
php artisan serve
```

Visit `http://localhost:8000/customer`

### Building for Production

```bash
# Build optimized assets
npm run build

# Assets will be in public/build/
```

## 📝 File Descriptions

### Core Files

| File | Purpose |
|------|---------|
| `App.vue` | Root Vue component |
| `main.js` | App initialization |
| `router/index.js` | Route configuration |
| `services/api.js` | Axios API calls |

### Laravel Integration

| File | Purpose |
|------|---------|
| `blade-template.blade.php` | Laravel view template |
| `routes-web.php` | Laravel routes |
| `CustomerController.php` | Main controller |
| `*Controller-api.php` | API controllers |

### Configuration

| File | Purpose |
|------|---------|
| `vite.config.js` | Vite build config |
| `tailwind.config.js` | Tailwind customization |
| `postcss.config.js` | PostCSS plugins |
| `package.json` | Dependencies |

## 🎯 Roadmap

- [x] Customer product catalog
- [x] Shopping cart management
- [x] Custom order form
- [x] User settings
- [ ] Purchase history
- [ ] Order tracking
- [ ] Payment integration
- [ ] Admin dashboard
- [ ] Email notifications

## 🐛 Troubleshooting

**Vite manifest not found**
```bash
npm run build
```

**Module not found errors**
```bash
npm install
```

**CSRF token mismatch**
Check meta tag in blade template:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

**Hot reload not working**
```bash
npm run dev
# Make sure port 5173 is available
```

## 📖 Documentation

- [Installation Guide](./INSTALLATION.md) - Full setup instructions
- [Vue 3 Docs](https://vuejs.org) - Vue.js documentation
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [Vite](https://vitejs.dev) - Build tool docs

## 🤝 Contributing

This is a custom project built for BAKERDAN. For modifications:

1. Update Vue components in `components/`
2. Modify routes in `router/index.js`
3. Adjust styles in Tailwind config
4. Update API calls in `services/api.js`

## 📄 License

Custom project for BAKERDAN bakery e-commerce platform.

---

**Built with ❤️ for BAKERDAN** 🍞

For questions or support, refer to the documentation or Laravel/Vue community resources.
