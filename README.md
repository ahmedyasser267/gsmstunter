# GSMStunter - Premium Refurbished Electronics Marketplace

A world-class, professional web application for buying, selling, and trading refurbished electronics. Designed with a clean European aesthetic, bilingual support (Dutch/English), and optimized for conversion.

## Live Preview

Simply open `index.html` in any modern web browser. No build tools or server required.

## Project Structure

```
gsmstunter-project/
├── index.html            # Homepage - Hero, trust badges, categories, testimonials
├── products.html         # Product listing - Filters, grid/list view, sorting
├── product-detail.html   # Product detail - Gallery, specs, condition selector
├── sell.html             # Sell device - Step-by-step valuation wizard
├── trade.html            # Trade-in - Device comparison, value calculator
├── cart.html             # Shopping cart - Items, promo codes, summary
├── checkout.html         # Multi-step checkout - Contact, shipping, payment
├── account.html          # User dashboard - Orders, wishlist, settings
├── styles.css            # Complete stylesheet - 3900+ lines of CSS
├── script.js             # All JavaScript functionality - i18n, cart, etc.
└── README.md             # This file
```

## Features

### Core Functionality
- **Buy Section** - Browse and purchase refurbished electronics with advanced filtering
- **Sell Section** - Step-by-step device valuation wizard with instant quotes
- **Trade/Exchange Section** - Trade-in calculator for upgrading devices

### Design & UX
- Clean, minimal, modern European design aesthetic
- Mobile-first responsive layout (320px to 1440px+)
- Smooth CSS transitions and scroll animations
- Professional typography with Inter font
- Color-coded condition badges (Like New, Excellent, Good, Fair)
- Sticky header with scroll shadow effect
- Toast notifications for user actions
- Live chat widget placeholder

### Bilingual Support (i18n)
- **Dutch (NL)** - Default language
- **English (EN)** - Full translation support
- Language switcher with flag icons in the header
- Preference saved to localStorage for persistence
- All UI text, buttons, labels, and messages translated

### Product Features
- Product grid with hover effects and quick-view
- Advanced filtering (Brand, Condition, Price, Storage, Color, OS)
- Sort by popularity, price, newest, rating
- Grid/list view toggle
- Wishlist with heart icons (saved to localStorage)
- Image gallery with thumbnail navigation
- Condition selector with dynamic pricing
- Storage capacity selector

### Shopping Experience
- Cart management (add, remove, quantity adjustment)
- Cart data persisted in localStorage
- Promo code input
- Multi-step checkout (Contact → Shipping → Payment → Review)
- Payment methods: iDEAL, Credit Card, PayPal, Klarna
- Form validation with error states

### Trust & Credibility
- Trust badges (3-year warranty, next-day delivery, 30-day returns)
- Customer testimonials with star ratings
- Sustainability impact stats (CO₂ saved, trees planted, e-waste reduced)
- Professional refurbished quality badges
- Secure checkout indicators

## Technical Stack

### HTML
- Semantic HTML5 (`<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`)
- ARIA labels for accessibility
- Meta tags for SEO and Open Graph
- Proper heading hierarchy (h1-h6)

### CSS
- CSS Custom Properties (design tokens) for theming
- CSS Grid and Flexbox layouts
- Mobile-first responsive design with media queries
- Breakpoints: 480px, 768px, 1024px, 1280px
- Smooth transitions and keyframe animations
- BEM-like naming convention
- Custom scrollbar styling
- Focus-visible states for keyboard navigation
- Print stylesheet

### JavaScript (Vanilla)
- No frameworks or dependencies required
- i18n system with translation objects
- Cart management with localStorage
- Wishlist with localStorage
- Intersection Observer for scroll animations
- Counter animations for stats
- Search autocomplete
- Modal window management
- Accordion and tab components
- Form validation
- Smooth scrolling

### External Resources (CDN)
- **Font**: Inter (Google Fonts)
- **Icons**: Font Awesome 6.5.1
- **Images**: Unsplash (high-quality stock photos)
- **Flags**: flagcdn.com (country flags)

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome for Android)

## Color Palette

| Color | Hex | Usage |
|-------|-----|-------|
| Primary | `#0D7C66` | Main brand color, buttons, links |
| Primary Light | `#10A37F` | Hover states, highlights |
| Accent | `#FF6B2C` | CTAs, promotional elements |
| Background | `#F7F8FA` | Page backgrounds |
| Text | `#1E293B` | Primary text |
| Text Secondary | `#64748B` | Secondary text, labels |
| Success | `#22C55E` | Success states, "Like New" condition |
| Warning | `#F59E0B` | Warning states, "Good" condition |
| Error | `#EF4444` | Error states |

## Customization

### Changing Colors
Edit the CSS custom properties in `styles.css`:
```css
:root {
  --color-primary: #0D7C66;
  --color-accent: #FF6B2C;
  /* ... */
}
```

### Adding Translations
Add new language objects in `script.js`:
```javascript
translations.de = {
  "nav-home": "Startseite",
  "nav-buy": "Kaufen",
  // ...
};
```

### Adding Products
Products can be added directly as HTML in `products.html` using the `.product-card` component structure, with `data-brand`, `data-condition`, and other filter attributes.

## Performance Features

- Lazy loading for images below the fold
- Efficient CSS with custom properties
- Intersection Observer for animations (no scroll event listeners)
- localStorage for cart and preference persistence
- Minimal JavaScript with no framework overhead
- Responsive images with appropriate sizing

## Accessibility (WCAG 2.1 Level AA)

- Semantic HTML structure
- ARIA labels on interactive elements
- Keyboard navigation support (focus-visible states)
- Sufficient color contrast ratios
- Screen reader friendly content
- Skip to content patterns
- Form labels and error states

## License

This project is for demonstration purposes. All product images are sourced from Unsplash (free to use).

---

Built with care for the European refurbished electronics market.
