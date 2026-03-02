# 📱 GSMStunter — Premium Refurbished Electronics Marketplace

> A world-class, bilingual (Dutch/English) web platform for buying, selling, and trading refurbished electronics. Built with a clean European design aesthetic, conversion-optimized UX, and zero dependencies.

---

## Table of Contents

- [Overview](#overview)
- [Live Preview](#live-preview)
- [Project Structure](#project-structure)
- [Pages & Features](#pages--features)
- [Design System](#design-system)
- [Bilingual Support (i18n)](#bilingual-support-i18n)
- [JavaScript Functionality](#javascript-functionality)
- [Responsive Breakpoints](#responsive-breakpoints)
- [Trust & Credibility Elements](#trust--credibility-elements)
- [External Dependencies (CDN)](#external-dependencies-cdn)
- [Browser Support](#browser-support)
- [Customization Guide](#customization-guide)
- [Performance Features](#performance-features)
- [Accessibility (WCAG 2.1)](#accessibility-wcag-21)
- [Inspired By](#inspired-by)
- [License](#license)

---

## Overview

**GSMStunter** is a full-featured front-end for a refurbished electronics marketplace targeting the Dutch and European market. It provides three core services:

| Service | Description |
|---------|-------------|
| **Kopen (Buy)** | Browse, filter, and purchase professionally refurbished smartphones, laptops, tablets, smartwatches, and accessories |
| **Verkopen (Sell)** | Step-by-step device valuation wizard — select device, describe condition, get an instant cash quote |
| **Inruilen (Trade-in)** | Calculate trade-in value for your old device and apply it as credit toward a newer refurbished model |

### Key Highlights

- 11 production-ready files, ~350 KB total
- 3,900+ lines of CSS with design tokens
- 1,740 lines of vanilla JavaScript (zero frameworks)
- Full NL/EN bilingual support with localStorage persistence
- Mobile-first responsive design (320px → 1440px+)
- Cart & wishlist with localStorage persistence
- Smooth scroll animations, toast notifications, modals
- Professional trust indicators inspired by leading Dutch marketplaces

---

## Live Preview

No build tools, servers, or installation required. Simply open:

```
index.html
```

in any modern web browser.

---

## Project Structure

```
gsmstunter/
│
├── index.html                  # Homepage — Hero, trust badges, categories,
│                               #   services, products, how-it-works,
│                               #   testimonials, sustainability, newsletter
│
├── products.html               # Product listing — 12 products, sidebar filters
│                               #   (brand/condition/price/storage/color/OS),
│                               #   sort, grid/list view toggle, pagination
│
├── product-detail.html         # Product detail — Image gallery with thumbnails,
│                               #   condition & storage selectors, specs table,
│                               #   customer reviews, related products
│
├── sell.html                   # Sell your device — 4-step wizard:
│                               #   Device type → Brand/Model → Condition → Quote
│                               #   Benefits section, FAQ accordion
│
├── trade.html                  # Trade-in — Device comparison calculator,
│                               #   trade-in value estimator, how-it-works,
│                               #   benefits, FAQ accordion
│
├── cart.html                   # Shopping cart — Item list with quantity controls,
│                               #   promo code input, order summary,
│                               #   trust indicators
│
├── checkout.html               # Multi-step checkout — 4 steps:
│                               #   Contact → Shipping → Payment → Review
│                               #   Supports iDEAL, Credit Card, PayPal, Klarna
│
├── account.html                # User dashboard — Profile sidebar, order history
│                               #   table, wishlist grid, dashboard stats
│
├── assets/
│   ├── css/
│   │   └── styles.css          # Complete stylesheet (3,900+ lines)
│   │                           #   CSS custom properties, Grid/Flexbox,
│   │                           #   animations, responsive media queries
│   ├── js/
│   │   └── script.js           # All JavaScript functionality (1,740 lines)
│   │                           #   i18n, cart, wishlist, filters, wizard,
│   │                           #   modals, animations, form validation
│   └── images/
│       └── .gitkeep            # Placeholder for local image assets
│
└── README.md                   # This documentation file
```

---

## Pages & Features

### 1. Homepage (`index.html`)

| Section | Description |
|---------|-------------|
| **Top Bar** | Free shipping notice, warranty badge, returns info, language switcher (NL/EN flags) |
| **Header** | Logo, search bar with autocomplete, navigation (Buy/Sell/Trade), user/wishlist/cart icons, mobile hamburger |
| **Mega Menu** | Product categories dropdown (Smartphones, Laptops, Tablets, More) |
| **Hero** | Animated gradient background, floating shapes, headline, CTAs (Shop Now / Sell Your Device), live stats counters |
| **Trust Bar** | 4 trust badges — 3-year warranty, next-day delivery, 30-day returns, 40-step inspection |
| **Services** | 3 service cards (Buy/Sell/Trade) with hover animations |
| **Categories** | 6 category cards (Smartphones, Laptops, Tablets, Smartwatches, Headphones, Accessories) |
| **Featured Products** | 4 product cards with condition badges, pricing, wishlist, add-to-cart |
| **How It Works** | 4-step visual process with connected dots |
| **Testimonials** | 3 customer review cards with star ratings and verified badges |
| **Sustainability** | Environmental impact stats (CO₂, trees, e-waste) with animated counters |
| **Promo Banner** | €15 discount with promo code WELKOM15 |
| **Newsletter** | Email subscription form |
| **Footer** | 5-column layout — brand, products, services, support, company, social links, payment icons |
| **Chat Widget** | Floating chat button (bottom-right) |

### 2. Product Listing (`products.html`)

| Feature | Details |
|---------|---------|
| **Breadcrumb** | Home > Producten |
| **Sidebar Filters** | Brand (Apple, Samsung, Google, OnePlus, Xiaomi), Condition (4 levels), Price range, Storage (64GB–1TB), Color, OS |
| **Toolbar** | Product count, sort dropdown (5 options), grid/list view toggle |
| **Product Grid** | 12 product cards (4 columns desktop), filter data attributes for JS filtering |
| **Pagination** | Previous / Page numbers / Next |

### 3. Product Detail (`product-detail.html`)

| Feature | Details |
|---------|---------|
| **Gallery** | Main image with zoom on hover, 4 clickable thumbnails |
| **Condition Selector** | 4 options (Like New / Excellent / Good / Fair) with dynamic pricing |
| **Storage Selector** | 128GB / 256GB / 512GB / 1TB buttons |
| **Pricing** | Current price, original price strikethrough, savings percentage |
| **Cart Actions** | Quantity selector, Add to Cart, Buy Now |
| **Guarantees** | Free shipping, 2-year warranty, 30-day returns, professionally refurbished |
| **Tabs** | Description, Specifications table, Customer reviews |
| **Related Products** | 4 similar product cards |

### 4. Sell Your Device (`sell.html`)

| Step | Content |
|------|---------|
| **Step 1** | Select device type (Smartphone / Laptop / Tablet / Smartwatch) |
| **Step 2** | Choose brand (Apple / Samsung / Google / OnePlus) and specific model |
| **Step 3** | Describe condition — Screen quality, Functionality, Cosmetic damage (radio options) |
| **Step 4** | Instant quote display (€285 example) with Accept Offer button |
| **Benefits** | Free shipping label, Fast payment within 48h, Up to €500 value |
| **FAQ** | Accordion with common selling questions |

### 5. Trade-in (`trade.html`)

| Feature | Details |
|---------|---------|
| **Calculator** | Two-column layout: Your current device (type/brand/model/condition dropdowns) vs. Desired device |
| **Result** | Trade-in value and remaining amount to pay |
| **How It Works** | 3-step visual guide |
| **Benefits** | Savings, sustainability, certified quality |
| **FAQ** | Accordion with trade-in questions |

### 6. Shopping Cart (`cart.html`)

| Feature | Details |
|---------|---------|
| **Cart Items** | Product image, name, specs, quantity controls, remove button, line price |
| **Promo Code** | Input field with Apply button |
| **Order Summary** | Subtotal, shipping (free over €50), VAT (21%), total |
| **Trust Badges** | SSL secure, 30-day returns, free shipping |

### 7. Checkout (`checkout.html`)

| Step | Form Fields |
|------|-------------|
| **1. Contact** | Email, phone number |
| **2. Shipping** | First/last name, address, city, postal code, country (NL/BE/DE), shipping method (Standard free / Express €7.95) |
| **3. Payment** | iDEAL, Credit Card (Visa/Mastercard), PayPal, Klarna — with conditional card fields |
| **4. Review** | Order summary, address preview, payment method, Place Order button |
| **Sidebar** | Persistent order summary with item thumbnails and totals |

### 8. User Account (`account.html`)

| Section | Details |
|---------|---------|
| **Sidebar** | Profile avatar + name, navigation (Dashboard, Orders, Wishlist, Addresses, Payment Methods, Sell History, Trade History, Settings, Logout) |
| **Dashboard Stats** | 3 cards — Total orders (12), Active trades (2), Wishlist items (5) |
| **Recent Orders** | Table with Order ID, Product, Date, Status badge (Delivered/Shipped/Processing), Total |
| **Wishlist** | 3 compact product cards with images and pricing |

---

## Design System

### Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-primary` | `#0D7C66` | Brand color, buttons, links, active states |
| `--color-primary-light` | `#10A37F` | Hover states, highlights |
| `--color-primary-dark` | `#095C4B` | Active/pressed states |
| `--color-primary-50` | `#E8F7F3` | Light backgrounds, selected states |
| `--color-accent` | `#FF6B2C` | CTA buttons, promotional elements |
| `--color-accent-dark` | `#E55A1B` | CTA hover states |
| `--color-bg` | `#F7F8FA` | Page section backgrounds |
| `--color-text` | `#1E293B` | Primary text |
| `--color-text-secondary` | `#64748B` | Secondary text, labels |
| `--color-text-muted` | `#94A3B8` | Placeholder text, hints |
| `--color-success` | `#22C55E` | Success states, "Like New" badge |
| `--color-warning` | `#F59E0B` | Warning states, "Good" badge |
| `--color-error` | `#EF4444` | Error states, form validation |
| `--color-info` | `#3B82F6` | Info states, "Shipped" badge |

### Typography

| Property | Value |
|----------|-------|
| **Font Family** | Inter (Google Fonts) with system fallbacks |
| **Body Size** | 16px (1rem) |
| **Body Weight** | 400 (normal) |
| **Headings Weight** | 600–800 |
| **Line Height** | 1.5 (body), 1.2 (headings) |
| **Scale** | 0.75rem → 3.75rem (12px → 60px) |

### Spacing Scale

8-point grid system: `0.25rem` → `6rem` using CSS custom properties (`--space-1` through `--space-24`).

### Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-sm` | 6px | Small elements (checkboxes, tags) |
| `--radius-md` | 10px | Buttons, inputs, small cards |
| `--radius-lg` | 14px | Cards, sections |
| `--radius-xl` | 20px | Large cards, modals |
| `--radius-2xl` | 24px | Hero images, feature sections |
| `--radius-full` | 9999px | Pills, avatars, badges |

### Shadows

6-tier shadow system from `--shadow-xs` to `--shadow-2xl`, plus `--shadow-card` and `--shadow-card-hover` for product cards.

### Transitions

| Token | Value | Usage |
|-------|-------|-------|
| `--transition-fast` | 150ms ease | Hover states, color changes |
| `--transition-base` | 250ms ease | Most interactions |
| `--transition-slow` | 350ms ease | Page transitions, modals |
| `--transition-spring` | 400ms cubic-bezier | Bouncy animations |

### Condition Badge Colors

| Condition | Dutch | Badge Class | Color |
|-----------|-------|-------------|-------|
| Like New | Als nieuw | `badge--like-new` | Green `#065F46` on `#D1FAE5` |
| Excellent | Uitstekend | `badge--excellent` | Green `#166534` on `#DCFCE7` |
| Good | Goed | `badge--good` | Amber `#92400E` on `#FEF3C7` |
| Fair | Redelijk | `badge--fair` | Orange `#9A3412` on `#FED7AA` |

---

## Bilingual Support (i18n)

The entire UI supports **Dutch (NL)** and **English (EN)** with seamless switching.

### How It Works

1. Translation object in `script.js` holds all UI strings in both languages
2. HTML elements use `data-i18n` attributes to map to translation keys
3. Placeholder text uses `data-i18n-placeholder` attribute
4. Title attributes use `data-i18n-title` attribute
5. Language preference is saved to `localStorage` (`gsmstunter-lang`)
6. Flag-based switcher in the top-right corner (Dutch flag / UK flag)

### Translation Coverage

| Category | Keys Count |
|----------|-----------|
| Navigation | 11 |
| Top Bar | 3 |
| Hero Section | 8 |
| Trust Bar | 8 |
| Services | 9 |
| Categories | 6 |
| Products | 10 |
| How It Works | 10 |
| Testimonials | 3 |
| Sustainability | 6 |
| Newsletter | 4 |
| Footer | 28 |
| Product Listing | 12 |
| Product Detail | 12 |
| Sell Page | 14 |
| Trade Page | 8 |
| Cart | 10 |
| Checkout | 12 |
| Account | 11 |
| General | 13 |
| **Total** | **~190+ keys** |

### Adding a New Language

```javascript
// In script.js, add a new language object:
translations.de = {
  "nav-home": "Startseite",
  "nav-buy": "Kaufen",
  "nav-sell": "Verkaufen",
  // ... all other keys
};
```

Then add a new option in the language dropdown in each HTML file's header.

---

## JavaScript Functionality

### Module Overview

| Function | Purpose |
|----------|---------|
| `setLanguage(lang)` | Switch UI language and persist to localStorage |
| `applyTranslations()` | Apply all translations to `data-i18n` elements |
| `addToCart(product)` | Add item to cart (merge if exists) |
| `removeFromCart(index)` | Remove item by index |
| `updateCartQuantity(index, qty)` | Adjust item quantity |
| `toggleWishlist(productId)` | Toggle wishlist status |
| `showToast(type, title, message)` | Display toast notification |
| `initStickyHeader()` | Add shadow on scroll |
| `initMobileMenu()` | Toggle mobile menu panel |
| `initLangDropdown()` | Language switcher dropdown |
| `initSearch()` | Search autocomplete with product suggestions |
| `initScrollAnimations()` | IntersectionObserver fade/slide animations |
| `initAccordions()` | FAQ accordion toggle |
| `initTabs()` | Tab panel switching |
| `initModals()` | Open/close modal overlays |
| `initMegaMenu()` | Mega menu hover behavior |
| `initProductFilters()` | Checkbox filter logic for product listing |
| `initViewToggle()` | Grid/list view switching |
| `initProductGallery()` | Thumbnail click → main image update |
| `initConditionSelector()` | Condition card selection with price update |
| `initStorageSelector()` | Storage option buttons |
| `initQuantitySelectors()` | +/− quantity controls |
| `initSellWizard()` | 4-step sell form navigation |
| `initTradeCalculator()` | Trade-in value calculation |
| `initCartPage()` | Cart rendering from localStorage |
| `initCheckout()` | Multi-step checkout navigation |
| `initCounterAnimations()` | Animated number counters |
| `initFormValidation()` | Required field validation |
| `initNewsletter()` | Newsletter form submission |

### Data Persistence (localStorage)

| Key | Data |
|-----|------|
| `gsmstunter-lang` | Selected language (`"nl"` or `"en"`) |
| `gsmstunter-cart` | Cart items array (JSON) |
| `gsmstunter-wishlist` | Wishlist product IDs array (JSON) |

---

## Responsive Breakpoints

| Breakpoint | Target | Key Changes |
|------------|--------|-------------|
| `≤ 480px` | Small phones | Single column products, stacked trust bar |
| `≤ 768px` | Tablets portrait | 2-column products, hidden top bar left, stacked forms |
| `≤ 1024px` | Tablets landscape | Hidden desktop nav → hamburger, single column hero, hidden sidebar filters |
| `≤ 1280px` | Small desktops | 3-column products, adjusted footer |
| `> 1280px` | Desktop | Full layout — 4-column products, sidebar, mega menu |

---

## Trust & Credibility Elements

Integrated features inspired by leading Dutch refurbished marketplaces:

| Feature | Source Inspiration |
|---------|-------------------|
| Tot 3 jaar garantie | Forza Refurbished |
| Voor 22:30 besteld, morgen in huis | Forza Refurbished |
| Niet goed, geld terug (30 dagen) | Forza, Back Market, Refurbed |
| Professioneel refurbished in 40+ stappen | Refurbed |
| Keurmerk Refurbished | Forza Refurbished |
| Gratis verzending | Refurbed, Mobico |
| Bespaar tot 40% t.o.v. nieuw | Refurbed |
| CO₂-uitstoot bespaard / Bomen geplant | Refurbed |
| 97% klanttevredenheid | Mobico |
| 2 miljoen+ klanten | Back Market, Swappie |
| €15 korting op eerste bestelling | Back Market |
| Conditiesysteem (Als nieuw/Uitstekend/Goed/Redelijk) | Swappie |
| Gratis screenprotector bij iPhone | Mobico |
| Snelle betaling (verkoop) | Mobico |
| Verkoop tot €500 | Refurbed |

---

## External Dependencies (CDN)

| Resource | CDN URL | Purpose |
|----------|---------|---------|
| **Inter Font** | `fonts.googleapis.com` | Primary typeface |
| **Font Awesome 6.5.1** | `cdnjs.cloudflare.com` | 1,400+ icons |
| **Product Images** | `images.unsplash.com` | High-quality stock photos |
| **Country Flags** | `flagcdn.com` | NL/GB flag images for language switcher |

No npm packages, no build tools, no frameworks required.

---

## Browser Support

| Browser | Minimum Version |
|---------|----------------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |
| iOS Safari | 14+ |
| Chrome Android | 90+ |

---

## Customization Guide

### Changing Brand Colors

Edit CSS custom properties in `assets/css/styles.css`:

```css
:root {
  --color-primary: #0D7C66;      /* Change main brand color */
  --color-primary-light: #10A37F; /* Lighter variant */
  --color-primary-dark: #095C4B;  /* Darker variant */
  --color-primary-50: #E8F7F3;   /* Very light background */
  --color-accent: #FF6B2C;       /* CTA/action color */
}
```

### Changing the Logo

In every HTML file, find the logo section:

```html
<a href="index.html" class="header__logo">
  <div class="header__logo-icon">
    <i class="fas fa-recycle"></i>  <!-- Change icon here -->
  </div>
  <div class="header__logo-text">gsm<span>stunter</span></div>  <!-- Change name here -->
</a>
```

### Adding Products

Copy a `.product-card` block in `products.html` and update:
- `data-brand`, `data-condition`, `data-storage`, `data-color`, `data-os` attributes (for filtering)
- Image `src`, product name, specs, pricing
- `onclick` handler in the add-to-cart button with correct product data

### Adding Pages

1. Copy any existing HTML file as a template
2. Keep the same header and footer structure
3. Ensure `href="assets/css/styles.css"` and `src="assets/js/script.js"` paths are correct
4. Add `data-i18n` attributes to all translatable text
5. Add corresponding translation keys to both `nl` and `en` objects in `script.js`

---

## Performance Features

- **Lazy Loading** — Images below the fold use IntersectionObserver
- **Efficient Animations** — CSS transitions and IntersectionObserver (no scroll event listeners)
- **localStorage Caching** — Cart, wishlist, and language preference persist without server calls
- **Minimal JavaScript** — Vanilla JS, no framework overhead (~66 KB unminified)
- **CSS Custom Properties** — Efficient theming without preprocessor
- **Critical Path** — Fonts preconnected, icons loaded via CDN with caching

---

## Accessibility (WCAG 2.1)

| Feature | Implementation |
|---------|----------------|
| Semantic HTML | `<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`, `<article>` |
| ARIA Labels | All interactive elements (buttons, links, inputs) |
| Keyboard Navigation | `:focus-visible` styles on all focusable elements |
| Color Contrast | Minimum 4.5:1 ratio for text |
| Screen Reader | `.sr-only` utility class for visually hidden labels |
| Form Labels | All inputs have associated `<label>` elements |
| Alt Text | All images have descriptive `alt` attributes |
| Heading Hierarchy | Proper h1 → h6 structure per page |
| Print Styles | Header/footer/chat hidden in print media query |

---

## Inspired By

This project synthesizes the best UI/UX patterns from leading European refurbished marketplaces:

| Site | Key Inspiration |
|------|----------------|
| [Forza Refurbished](https://www.forza-refurbished.nl/) | Trust badges, warranty focus, Dutch market leader |
| [Refurbed](https://www.refurbed.nl/) | Sustainability messaging, clean design, environmental stats |
| [Mobico](https://mobico.nl/) | Simplicity, fast delivery, customer satisfaction |
| [Back Market](https://www.backmarket.nl/) | Professional design, review system, scale indicators |
| [Swappie](https://swappie.com/nl/) | Condition grading, how-it-works flow, trade-in focus |
| [Vendit](https://www.vendit.nl/) | Dutch market approach, clean UI |

---

## License

This project is for demonstration and development purposes. Product images are sourced from [Unsplash](https://unsplash.com) (free to use under the Unsplash License).

---

**Built with precision for the European refurbished electronics market.**
