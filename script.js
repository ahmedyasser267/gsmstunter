/* ============================================
   GSMSTUNTER - Refurbished Electronics Marketplace
   Complete JavaScript - Production Ready
   ============================================ */

/* ── Translations (i18n) ── */
const translations = {
  nl: {
    // Navigation
    "nav-home": "Home",
    "nav-buy": "Kopen",
    "nav-sell": "Verkopen",
    "nav-trade": "Inruilen",
    "nav-sustainability": "Duurzaamheid",
    "nav-about": "Over ons",
    "nav-contact": "Contact",
    "nav-account": "Account",
    "nav-cart": "Winkelwagen",
    "nav-orders": "Bestellingen",
    "nav-help": "Hulp",
    "search-placeholder": "Zoek smartphones, laptops, tablets...",

    // Top Bar
    "topbar-shipping": "Gratis verzending vanaf €50",
    "topbar-warranty": "Tot 3 jaar garantie",
    "topbar-returns": "30 dagen retourneren",

    // Hero
    "hero-badge": "🌱 Duurzaam & Betaalbaar",
    "hero-title-1": "Premium Refurbished",
    "hero-title-2": "Elektronica",
    "hero-subtitle": "Koop, verkoop en ruil refurbished apparaten. Bespaar tot 40% t.o.v. nieuw en maak een duurzame keuze voor de planeet.",
    "hero-cta-buy": "Shop nu",
    "hero-cta-sell": "Verkoop je device",
    "hero-stat-customers": "klanten",
    "hero-stat-warranty": "jaar garantie",
    "hero-stat-satisfaction": "tevredenheid",
    "hero-stat-co2": "CO₂ bespaard",

    // Trust Bar
    "trust-warranty-title": "Tot 3 jaar garantie",
    "trust-warranty-text": "Uitgebreide bescherming op alle apparaten",
    "trust-delivery-title": "Voor 22:30 besteld, morgen in huis",
    "trust-delivery-text": "Snelle en gratis verzending",
    "trust-returns-title": "30 dagen retourneren",
    "trust-returns-text": "Niet goed, geld terug!",
    "trust-quality-title": "Professioneel refurbished",
    "trust-quality-text": "Gecontroleerd in 40+ stappen",

    // Services
    "services-label": "Onze Diensten",
    "services-title": "Kopen, Verkopen of Inruilen",
    "services-subtitle": "Drie eenvoudige manieren om deel te nemen aan de circulaire economie",
    "service-buy-title": "Kopen",
    "service-buy-desc": "Ontdek premium refurbished apparaten tegen de beste prijzen. Alle producten zijn professioneel gecontroleerd met garantie.",
    "service-buy-cta": "Bekijk producten",
    "service-sell-title": "Verkopen",
    "service-sell-desc": "Verkoop je gebruikte apparaten snel en eenvoudig. Ontvang direct een eerlijke prijs en snelle betaling.",
    "service-sell-cta": "Verkoop je device",
    "service-trade-title": "Inruilen",
    "service-trade-desc": "Ruil je oude apparaat in voor korting op een nieuwer model. Eenvoudig, snel en duurzaam.",
    "service-trade-cta": "Ruil nu in",

    // Categories
    "categories-label": "Categorieën",
    "categories-title": "Populaire Categorieën",
    "categories-subtitle": "Vind het perfecte refurbished apparaat voor jou",
    "cat-smartphones": "Smartphones",
    "cat-laptops": "Laptops",
    "cat-tablets": "Tablets",
    "cat-smartwatches": "Smartwatches",
    "cat-headphones": "Koptelefoons",
    "cat-accessories": "Accessoires",

    // Products
    "products-label": "Populair",
    "products-title": "Uitgelichte Producten",
    "products-subtitle": "De beste deals op professioneel refurbished elektronica",
    "products-view-all": "Bekijk alle producten",
    "product-condition-like-new": "Als nieuw",
    "product-condition-excellent": "Uitstekend",
    "product-condition-good": "Goed",
    "product-condition-fair": "Redelijk",
    "product-add-cart": "In winkelwagen",
    "product-quick-view": "Snel bekijken",
    "product-warranty": "2 jaar garantie",
    "product-from": "vanaf",

    // How It Works
    "how-label": "Hoe het werkt",
    "how-title": "In 4 eenvoudige stappen",
    "how-subtitle": "Van bestelling tot in je handen - eenvoudiger kan niet",
    "how-step1-title": "Kies je apparaat",
    "how-step1-text": "Blader door ons aanbod en vind het perfecte refurbished apparaat",
    "how-step2-title": "Selecteer conditie",
    "how-step2-text": "Kies de conditie die bij je budget past",
    "how-step3-title": "Bestel veilig",
    "how-step3-text": "Betaal veilig met iDEAL, creditcard of Klarna",
    "how-step4-title": "Ontvang & geniet",
    "how-step4-text": "Snelle levering met volledige garantie",

    // Testimonials
    "testimonials-label": "Ervaringen",
    "testimonials-title": "Wat onze klanten zeggen",
    "testimonials-subtitle": "Meer dan 2 miljoen tevreden klanten",
    "testimonial-verified": "Geverifieerde aankoop",

    // Sustainability
    "sustainability-title": "Samen maken we het verschil",
    "sustainability-text": "Elke refurbished aankoop draagt bij aan een duurzamere wereld. Samen verminderen we e-waste en CO₂-uitstoot.",
    "sustainability-cta": "Meer over duurzaamheid",
    "sustainability-co2": "ton CO₂ bespaard",
    "sustainability-trees": "bomen geplant",
    "sustainability-ewaste": "kg e-waste bespaard",

    // Newsletter
    "newsletter-title": "Blijf op de hoogte",
    "newsletter-subtitle": "Ontvang €15 korting op je eerste bestelling en mis nooit meer een deal",
    "newsletter-placeholder": "Je e-mailadres",
    "newsletter-cta": "Aanmelden",
    "newsletter-privacy": "We respecteren je privacy. Afmelden kan altijd.",

    // Footer
    "footer-desc": "Premium refurbished elektronica voor een duurzamere wereld. Koop, verkoop en ruil met vertrouwen.",
    "footer-products": "Producten",
    "footer-smartphones": "Smartphones",
    "footer-laptops": "Laptops",
    "footer-tablets": "Tablets",
    "footer-watches": "Smartwatches",
    "footer-headphones": "Koptelefoons",
    "footer-accessories": "Accessoires",
    "footer-services": "Diensten",
    "footer-buy": "Kopen",
    "footer-sell": "Verkopen",
    "footer-trade": "Inruilen",
    "footer-business": "Zakelijk",
    "footer-support": "Klantenservice",
    "footer-help": "Help & FAQ",
    "footer-delivery": "Bezorgen",
    "footer-returns-link": "Retourneren",
    "footer-warranty-link": "Garantie",
    "footer-payment": "Betaalmethodes",
    "footer-contact-link": "Contact",
    "footer-company": "Bedrijf",
    "footer-about": "Over ons",
    "footer-sustainability-link": "Duurzaamheid",
    "footer-careers": "Vacatures",
    "footer-press": "Pers",
    "footer-blog": "Blog",
    "footer-legal": "Juridisch",
    "footer-terms": "Algemene voorwaarden",
    "footer-privacy": "Privacybeleid",
    "footer-cookies": "Cookiebeleid",
    "footer-copyright": "© 2026 GSMStunter. Alle rechten voorbehouden.",

    // Product Listing Page
    "products-page-title": "Alle Producten",
    "products-count": "producten gevonden",
    "breadcrumb-home": "Home",
    "breadcrumb-products": "Producten",
    "breadcrumb-smartphones": "Smartphones",
    "breadcrumb-product": "iPhone 15 Pro",
    "filter-brand": "Merk",
    "filter-condition": "Conditie",
    "filter-price": "Prijs",
    "filter-storage": "Opslag",
    "filter-color": "Kleur",
    "filter-os": "Besturingssysteem",
    "filter-price-min": "Min",
    "filter-price-max": "Max",
    "filter-clear": "Filters wissen",
    "sort-label": "Sorteer op:",
    "pagination-prev": "Vorige",
    "pagination-next": "Volgende",
    "sort-popular": "Meest populair",
    "sort-price-low": "Prijs: laag-hoog",
    "sort-price-high": "Prijs: hoog-laag",
    "sort-newest": "Nieuwste",
    "sort-rating": "Beste beoordeling",

    // Product Detail
    "product-tabs-heading": "Productinformatie",
    "tab-description": "Beschrijving",
    "tab-specifications": "Specificaties",
    "tab-reviews": "Reviews",
    "product-condition-label": "Conditie",
    "product-storage-label": "Opslag",
    "product-quantity-label": "Aantal",
    "product-add-cart": "In winkelwagen",
    "product-buy-now": "Direct kopen",
    "product-guarantee-shipping": "Gratis verzending",
    "product-guarantee-warranty": "2 jaar garantie",
    "product-guarantee-returns": "30 dagen retourneren",
    "product-guarantee-refurbished": "Professioneel refurbished",
    "product-title-iphone15": "iPhone 15 Pro",
    "product-subtitle-iphone15": "128GB · Space Black · iOS 17",
    "product-reviews": "4.8 (124 reviews)",
    "product-savings": "-25%",
    "similar-products-title": "Vergelijkbare producten",
    "description-heading": "Over de iPhone 15 Pro",
    "description-p1": "De iPhone 15 Pro is Apple's krachtigste smartphone met de revolutionaire A17 Pro-chip, titanium design en een geavanceerd camerasysteem. Dit refurbished exemplaar is professioneel gecontroleerd en gereinigd, zodat je geniet van premium kwaliteit tegen een scherpe prijs.",
    "description-p2": "Het 6.1-inch Super Retina XDR-display levert verbluffende helderheid en kleurnauwkeurigheid. De 48MP hoofdcamera met ProRAW-ondersteuning maakt indrukwekkende foto's en video's mogelijk. Met USB-C, Action-knop en iOS 17 heb je de nieuwste functies binnen handbereik.",
    "description-p3": "Alle GSMStunter refurbished iPhones doorlopen een streng 40-stappen controleproces. Wij garanderen volledige functionaliteit, originele onderdelen waar mogelijk, en een uitstekende staat van het apparaat. Kies duurzaam zonder concessies te doen aan kwaliteit.",
    "spec-merk": "Merk",
    "spec-model": "Model",
    "spec-processor": "Processor",
    "spec-scherm": "Scherm",
    "spec-camera": "Camera",
    "spec-batterij": "Batterij",
    "spec-os": "OS",
    "spec-afmetingen": "Afmetingen",
    "spec-gewicht": "Gewicht",
    "review1-text": "\"Super blij met mijn refurbished iPhone 15 Pro! Ziet er als nieuw uit en werkt perfect. De prijs was onverslaanbaar. Zeker een aanrader!\"",
    "review2-text": "\"Snelle levering en uitstekende conditie. De A17 Pro-chip is razendsnel. Geen enkel verschil met nieuw, behalve de prijs!\"",
    "review3-text": "\"Tweede refurbished iPhone bij GSMStunter. Altijd topkwaliteit en de 2 jaar garantie geeft extra gemoedsrust. Aanrader!\"",
    "review-verified": "Geverifieerde aankoop",
    "detail-description": "Beschrijving",
    "detail-specs": "Specificaties",
    "detail-reviews": "Beoordelingen",
    "detail-whats-included": "Wat zit er in de doos",
    "detail-condition-title": "Kies conditie",
    "detail-storage-title": "Kies opslag",
    "detail-add-cart": "In winkelwagen",
    "detail-buy-now": "Nu kopen",
    "detail-free-shipping": "Gratis verzending",
    "detail-warranty-info": "2 jaar garantie inbegrepen",
    "detail-returns-info": "30 dagen retourneren",
    "detail-refurbished-info": "Professioneel refurbished",
    "detail-similar": "Vergelijkbare producten",

    // Sell Page
    "sell-title": "Verkoop je Device",
    "sell-subtitle": "Ontvang direct een eerlijke prijs voor je gebruikte apparaat",
    "sell-hero-title": "Verkoop je Device",
    "sell-hero-subtitle": "Ontvang binnen enkele minuten een eerlijke prijs voor je apparaat. Gratis verzending en snel uitbetaald.",
    "sell-step1": "Selecteer apparaat",
    "sell-step2": "Kies model",
    "sell-step3": "Beschrijf conditie",
    "sell-step4": "Ontvang bod",
    "sell-step1-title": "Selecteer apparaat",
    "sell-step2-title": "Kies model",
    "sell-step3-title": "Beschrijf conditie",
    "sell-step4-title": "Ontvang bod",
    "sell-device-type": "Wat wil je verkopen?",
    "sell-device-smartphone": "Smartphone",
    "sell-device-laptop": "Laptop",
    "sell-device-tablet": "Tablet",
    "sell-device-smartwatch": "Smartwatch",
    "sell-models": "Modellen",
    "sell-select-brand": "Kies je merk",
    "sell-select-model": "Kies je model",
    "sell-condition-q1": "Hoe is de staat van het scherm?",
    "sell-condition-q2": "Werkt het apparaat volledig?",
    "sell-condition-q3": "Zijn er cosmetische beschadigingen?",
    "sell-q1-screen": "Hoe is de conditie van het scherm?",
    "sell-q2-function": "Werkt alles naar behoren?",
    "sell-q3-cosmetic": "Cosmetische schade?",
    "sell-condition-perfect": "Perfect",
    "sell-condition-minor": "Kleine krassen",
    "sell-condition-cracked": "Barst of breuk",
    "sell-condition-none": "Geen",
    "sell-condition-light": "Lichte slijtage",
    "sell-condition-heavy": "Duidelijke schade",
    "sell-yes": "Ja",
    "sell-no": "Nee",
    "sell-minor-issues": "Kleine problemen",
    "sell-estimated-value": "Geschatte waarde",
    "sell-quote-title": "Je geschatte opbrengst",
    "sell-accept": "Accepteer bod",
    "sell-accept-offer": "Bod accepteren",
    "sell-next": "Volgende",
    "sell-back": "Terug",
    "sell-free-shipping": "Gratis verzendlabel",
    "sell-fast-payment": "Snelle betaling binnen 48 uur",
    "sell-benefits-title": "Waarom verkopen bij GSMStunter?",
    "sell-benefit1-title": "Gratis verzendlabel",
    "sell-benefit1-desc": "Wij sturen je een gratis verzendlabel. Verpak en verstuur.",
    "sell-benefit2-title": "Snel uitbetaald",
    "sell-benefit2-desc": "Ontvang je geld binnen 48 uur na ontvangst.",
    "sell-benefit3-title": "Tot €500 voor je apparaat",
    "sell-benefit3-desc": "Eerlijke prijzen voor al je apparaten.",
    "sell-faq-title": "Veelgestelde vragen over verkopen",
    "sell-faq1-q": "Hoe werkt het verkoopproces?",
    "sell-faq1-a": "Selecteer je apparaat, kies het model, beschrijf de conditie en ontvang direct een bod. Accepteer het bod, verstuur je apparaat met het gratis verzendlabel en ontvang je geld binnen 48 uur.",
    "sell-faq2-q": "Is verzending gratis?",
    "sell-faq2-a": "Ja, wij sturen je een gratis verzendlabel. Je hoeft alleen je apparaat te verpakken en af te geven bij een PostNL-punt.",
    "sell-faq3-q": "Wanneer krijg ik mijn geld?",
    "sell-faq3-a": "Na inspectie van je apparaat ontvang je het bedrag binnen 48 uur op je rekening via iDEAL of bankoverschrijving.",

    // Trade Page
    "trade-title": "Ruil je Device In",
    "trade-subtitle": "Wissel je oude apparaat in voor korting op een nieuwer model",
    "trade-hero-title": "Ruil je Device In",
    "trade-hero-subtitle": "Ruil je oude apparaat in voor een nieuw refurbished model. Bespaar geld en draag bij aan een duurzamere wereld.",
    "trade-calc-title": "Bereken je inruilwaarde",
    "trade-your-device": "Jouw huidige apparaat",
    "trade-wanted-device": "Je gewenste apparaat",
    "trade-device-type": "Apparaattype",
    "trade-brand": "Merk",
    "trade-model": "Model",
    "trade-condition": "Conditie",
    "trade-select-type": "Selecteer type",
    "trade-select-brand": "Selecteer merk",
    "trade-select-model": "Selecteer model",
    "trade-select-condition": "Selecteer conditie",
    "trade-wanted-product": "Gewenst product",
    "trade-select-wanted": "Selecteer product",
    "trade-value": "Inruilwaarde",
    "trade-in-value": "Inruilwaarde",
    "trade-additional": "Bij te betalen",
    "trade-additional-payment": "Te betalen",
    "trade-calculate": "Bereken inruilwaarde",
    "trade-start": "Start inruil",
    "trade-how-title": "Hoe werkt inruilen?",
    "trade-how-step1-title": "Selecteer apparaten",
    "trade-how-step1-desc": "Kies je huidige apparaat en het gewenste refurbished model.",
    "trade-how-step2-title": "Ontvang inruilwaarde",
    "trade-how-step2-desc": "Bereken direct je inruilwaarde en zie wat je nog moet betalen.",
    "trade-how-step3-title": "Verstuur & ontvang",
    "trade-how-step3-desc": "Verstuur je oude apparaat met gratis label en ontvang je nieuwe.",
    "trade-benefits-title": "Voordelen van inruilen",
    "trade-benefit1-title": "Bespaar op je upgrade",
    "trade-benefit1-desc": "Verlaag de prijs van je nieuwe apparaat met je inruilwaarde.",
    "trade-benefit2-title": "Duurzaam",
    "trade-benefit2-desc": "Geef je apparaat een tweede leven en verminder e-waste.",
    "trade-benefit3-title": "Gecertificeerd refurbished",
    "trade-benefit3-desc": "Ontvang een als-nieuw apparaat met garantie.",
    "trade-faq-title": "Veelgestelde vragen over inruilen",
    "trade-faq1-q": "Hoe wordt mijn inruilwaarde bepaald?",
    "trade-faq1-a": "De waarde hangt af van het merk, model, opslag en conditie van je apparaat. Onze calculator geeft een indicatie; de definitieve waarde volgt na inspectie.",
    "trade-faq2-q": "Kan ik meerdere apparaten inruilen?",
    "trade-faq2-a": "Ja, je kunt meerdere apparaten inruilen. De totale inruilwaarde wordt van de aankoopprijs afgetrokken.",
    "trade-faq3-q": "Wanneer ontvang ik mijn nieuwe apparaat?",
    "trade-faq3-a": "Na ontvangst en goedkeuring van je inruil sturen we je nieuwe refurbished apparaat binnen 2-3 werkdagen.",

    // Cart
    "cart-title": "Winkelwagen",
    "cart-empty": "Je winkelwagen is leeg",
    "cart-continue": "Verder winkelen",
    "cart-summary-title": "Overzicht",
    "cart-subtotal": "Subtotaal",
    "cart-shipping": "Verzending",
    "cart-shipping-free": "Gratis",
    "cart-tax": "BTW (21%)",
    "cart-total": "Totaal",
    "cart-promo-placeholder": "Kortingscode",
    "cart-promo-apply": "Toepassen",
    "cart-checkout": "Afrekenen",
    "cart-estimated-delivery": "Geschatte levering: 1-3 werkdagen",

    // Checkout
    "checkout-title": "Afrekenen",
    "checkout-step-contact": "Contact",
    "checkout-step-shipping": "Verzending",
    "checkout-step-payment": "Betaling",
    "checkout-step-review": "Overzicht",
    "checkout-email": "E-mailadres",
    "checkout-name": "Volledige naam",
    "checkout-first-name": "Voornaam",
    "checkout-last-name": "Achternaam",
    "checkout-address": "Adres",
    "checkout-city": "Stad",
    "checkout-postal": "Postcode",
    "checkout-country": "Land",
    "checkout-phone": "Telefoonnummer",
    "checkout-payment-method": "Betaalmethode",
    "checkout-place-order": "Bestelling plaatsen",
    "checkout-secure": "Veilig afrekenen met SSL-versleuteling",

    // Account
    "account-title": "Mijn Account",
    "account-dashboard": "Dashboard",
    "account-orders": "Bestellingen",
    "account-wishlist": "Verlanglijstje",
    "account-addresses": "Adressen",
    "account-payments": "Betaalmethodes",
    "account-sell-history": "Verkoop geschiedenis",
    "account-trade-history": "Inruil geschiedenis",
    "account-settings": "Instellingen",
    "account-logout": "Uitloggen",
    "account-welcome": "Welkom terug",
    "account-total-orders": "Totaal bestellingen",
    "account-active-trades": "Actieve inruilen",
    "account-wishlist-items": "Verlanglijst items",

    // General
    "save": "Opslaan",
    "cancel": "Annuleren",
    "close": "Sluiten",
    "loading": "Laden...",
    "view-all": "Bekijk alles",
    "learn-more": "Meer informatie",
    "or": "of",
    "from": "vanaf",
    "per": "per",
    "items": "items",
    "added-to-cart": "Toegevoegd aan winkelwagen",
    "removed-from-cart": "Verwijderd uit winkelwagen",
    "guest-checkout": "Afrekenen als gast"
  },

  en: {
    // Navigation
    "nav-home": "Home",
    "nav-buy": "Buy",
    "nav-sell": "Sell",
    "nav-trade": "Trade-in",
    "nav-sustainability": "Sustainability",
    "nav-about": "About",
    "nav-contact": "Contact",
    "nav-account": "Account",
    "nav-cart": "Cart",
    "nav-orders": "Orders",
    "nav-help": "Help",
    "search-placeholder": "Search smartphones, laptops, tablets...",

    // Top Bar
    "topbar-shipping": "Free shipping from €50",
    "topbar-warranty": "Up to 3 years warranty",
    "topbar-returns": "30-day returns",

    // Hero
    "hero-badge": "🌱 Sustainable & Affordable",
    "hero-title-1": "Premium Refurbished",
    "hero-title-2": "Electronics",
    "hero-subtitle": "Buy, sell and trade refurbished devices. Save up to 40% compared to new and make a sustainable choice for the planet.",
    "hero-cta-buy": "Shop now",
    "hero-cta-sell": "Sell your device",
    "hero-stat-customers": "customers",
    "hero-stat-warranty": "year warranty",
    "hero-stat-satisfaction": "satisfaction",
    "hero-stat-co2": "CO₂ saved",

    // Trust Bar
    "trust-warranty-title": "Up to 3 years warranty",
    "trust-warranty-text": "Extended protection on all devices",
    "trust-delivery-title": "Order before 22:30, delivered tomorrow",
    "trust-delivery-text": "Fast and free shipping",
    "trust-returns-title": "30-day returns",
    "trust-returns-text": "Not satisfied? Money back!",
    "trust-quality-title": "Professionally refurbished",
    "trust-quality-text": "Checked in 40+ steps",

    // Services
    "services-label": "Our Services",
    "services-title": "Buy, Sell or Trade-in",
    "services-subtitle": "Three simple ways to participate in the circular economy",
    "service-buy-title": "Buy",
    "service-buy-desc": "Discover premium refurbished devices at the best prices. All products are professionally tested with warranty.",
    "service-buy-cta": "Browse products",
    "service-sell-title": "Sell",
    "service-sell-desc": "Sell your used devices quickly and easily. Get an instant fair price and fast payment.",
    "service-sell-cta": "Sell your device",
    "service-trade-title": "Trade-in",
    "service-trade-desc": "Trade in your old device for a discount on a newer model. Simple, fast and sustainable.",
    "service-trade-cta": "Trade in now",

    // Categories
    "categories-label": "Categories",
    "categories-title": "Popular Categories",
    "categories-subtitle": "Find the perfect refurbished device for you",
    "cat-smartphones": "Smartphones",
    "cat-laptops": "Laptops",
    "cat-tablets": "Tablets",
    "cat-smartwatches": "Smartwatches",
    "cat-headphones": "Headphones",
    "cat-accessories": "Accessories",

    // Products
    "products-label": "Popular",
    "products-title": "Featured Products",
    "products-subtitle": "The best deals on professionally refurbished electronics",
    "products-view-all": "View all products",
    "product-condition-like-new": "Like new",
    "product-condition-excellent": "Excellent",
    "product-condition-good": "Good",
    "product-condition-fair": "Fair",
    "product-add-cart": "Add to cart",
    "product-quick-view": "Quick view",
    "product-warranty": "2-year warranty",
    "product-from": "from",

    // How It Works
    "how-label": "How it works",
    "how-title": "In 4 simple steps",
    "how-subtitle": "From order to your hands - it couldn't be easier",
    "how-step1-title": "Choose your device",
    "how-step1-text": "Browse our selection and find the perfect refurbished device",
    "how-step2-title": "Select condition",
    "how-step2-text": "Choose the condition that fits your budget",
    "how-step3-title": "Order securely",
    "how-step3-text": "Pay securely with iDEAL, credit card or Klarna",
    "how-step4-title": "Receive & enjoy",
    "how-step4-text": "Fast delivery with full warranty",

    // Testimonials
    "testimonials-label": "Reviews",
    "testimonials-title": "What our customers say",
    "testimonials-subtitle": "More than 2 million satisfied customers",
    "testimonial-verified": "Verified purchase",

    // Sustainability
    "sustainability-title": "Together we make a difference",
    "sustainability-text": "Every refurbished purchase contributes to a more sustainable world. Together we reduce e-waste and CO₂ emissions.",
    "sustainability-cta": "More about sustainability",
    "sustainability-co2": "tonnes CO₂ saved",
    "sustainability-trees": "trees planted",
    "sustainability-ewaste": "kg e-waste saved",

    // Newsletter
    "newsletter-title": "Stay updated",
    "newsletter-subtitle": "Get €15 off your first order and never miss a deal",
    "newsletter-placeholder": "Your email address",
    "newsletter-cta": "Subscribe",
    "newsletter-privacy": "We respect your privacy. Unsubscribe anytime.",

    // Footer
    "footer-desc": "Premium refurbished electronics for a more sustainable world. Buy, sell and trade with confidence.",
    "footer-products": "Products",
    "footer-smartphones": "Smartphones",
    "footer-laptops": "Laptops",
    "footer-tablets": "Tablets",
    "footer-watches": "Smartwatches",
    "footer-headphones": "Headphones",
    "footer-accessories": "Accessories",
    "footer-services": "Services",
    "footer-buy": "Buy",
    "footer-sell": "Sell",
    "footer-trade": "Trade-in",
    "footer-business": "Business",
    "footer-support": "Customer Service",
    "footer-help": "Help & FAQ",
    "footer-delivery": "Delivery",
    "footer-returns-link": "Returns",
    "footer-warranty-link": "Warranty",
    "footer-payment": "Payment Methods",
    "footer-contact-link": "Contact",
    "footer-company": "Company",
    "footer-about": "About us",
    "footer-sustainability-link": "Sustainability",
    "footer-careers": "Careers",
    "footer-press": "Press",
    "footer-blog": "Blog",
    "footer-legal": "Legal",
    "footer-terms": "Terms & Conditions",
    "footer-privacy": "Privacy Policy",
    "footer-cookies": "Cookie Policy",
    "footer-copyright": "© 2026 GSMStunter. All rights reserved.",

    // Product Listing Page
    "products-page-title": "All Products",
    "products-count": "products found",
    "breadcrumb-home": "Home",
    "breadcrumb-products": "Products",
    "breadcrumb-smartphones": "Smartphones",
    "breadcrumb-product": "iPhone 15 Pro",
    "filter-brand": "Brand",
    "filter-condition": "Condition",
    "filter-price": "Price",
    "filter-storage": "Storage",
    "filter-color": "Color",
    "filter-os": "Operating System",
    "filter-price-min": "Min",
    "filter-price-max": "Max",
    "filter-clear": "Clear filters",
    "sort-label": "Sort by:",
    "pagination-prev": "Previous",
    "pagination-next": "Next",
    "sort-popular": "Most popular",
    "sort-price-low": "Price: low-high",
    "sort-price-high": "Price: high-low",
    "sort-newest": "Newest",
    "sort-rating": "Best rated",

    // Product Detail
    "product-tabs-heading": "Product information",
    "tab-description": "Description",
    "tab-specifications": "Specifications",
    "tab-reviews": "Reviews",
    "product-condition-label": "Condition",
    "product-storage-label": "Storage",
    "product-quantity-label": "Quantity",
    "product-add-cart": "Add to cart",
    "product-buy-now": "Buy now",
    "product-guarantee-shipping": "Free shipping",
    "product-guarantee-warranty": "2-year warranty",
    "product-guarantee-returns": "30-day returns",
    "product-guarantee-refurbished": "Professionally refurbished",
    "product-title-iphone15": "iPhone 15 Pro",
    "product-subtitle-iphone15": "128GB · Space Black · iOS 17",
    "product-reviews": "4.8 (124 reviews)",
    "product-savings": "-25%",
    "similar-products-title": "Similar products",
    "description-heading": "About the iPhone 15 Pro",
    "description-p1": "The iPhone 15 Pro is Apple's most powerful smartphone with the revolutionary A17 Pro chip, titanium design and an advanced camera system. This refurbished unit has been professionally inspected and cleaned, so you enjoy premium quality at a sharp price.",
    "description-p2": "The 6.1-inch Super Retina XDR display delivers stunning brightness and colour accuracy. The 48MP main camera with ProRAW support enables impressive photos and videos. With USB-C, Action button and iOS 17, you have the latest features at your fingertips.",
    "description-p3": "All GSMStunter refurbished iPhones go through a rigorous 40-step inspection process. We guarantee full functionality, original parts where possible, and excellent device condition. Choose sustainably without compromising on quality.",
    "spec-merk": "Brand",
    "spec-model": "Model",
    "spec-processor": "Processor",
    "spec-scherm": "Display",
    "spec-camera": "Camera",
    "spec-batterij": "Battery",
    "spec-os": "OS",
    "spec-afmetingen": "Dimensions",
    "spec-gewicht": "Weight",
    "review1-text": "\"Very happy with my refurbished iPhone 15 Pro! Looks like new and works perfectly. The price was unbeatable. Highly recommended!\"",
    "review2-text": "\"Fast delivery and excellent condition. The A17 Pro chip is lightning fast. No difference from new, except the price!\"",
    "review3-text": "\"Second refurbished iPhone from GSMStunter. Always top quality and the 2-year warranty gives extra peace of mind. Recommended!\"",
    "review-verified": "Verified purchase",
    "detail-description": "Description",
    "detail-specs": "Specifications",
    "detail-reviews": "Reviews",
    "detail-whats-included": "What's in the box",
    "detail-condition-title": "Choose condition",
    "detail-storage-title": "Choose storage",
    "detail-add-cart": "Add to cart",
    "detail-buy-now": "Buy now",
    "detail-free-shipping": "Free shipping",
    "detail-warranty-info": "2-year warranty included",
    "detail-returns-info": "30-day returns",
    "detail-refurbished-info": "Professionally refurbished",
    "detail-similar": "Similar products",

    // Sell Page
    "sell-title": "Sell Your Device",
    "sell-subtitle": "Get an instant fair price for your used device",
    "sell-hero-title": "Sell Your Device",
    "sell-hero-subtitle": "Get a fair price for your device within minutes. Free shipping and fast payment.",
    "sell-step1": "Select device",
    "sell-step2": "Choose model",
    "sell-step3": "Describe condition",
    "sell-step4": "Get quote",
    "sell-step1-title": "Select device",
    "sell-step2-title": "Choose model",
    "sell-step3-title": "Describe condition",
    "sell-step4-title": "Get quote",
    "sell-device-type": "What do you want to sell?",
    "sell-device-smartphone": "Smartphone",
    "sell-device-laptop": "Laptop",
    "sell-device-tablet": "Tablet",
    "sell-device-smartwatch": "Smartwatch",
    "sell-models": "Models",
    "sell-select-brand": "Choose your brand",
    "sell-select-model": "Choose your model",
    "sell-condition-q1": "How is the screen condition?",
    "sell-condition-q2": "Does the device fully work?",
    "sell-condition-q3": "Is there any cosmetic damage?",
    "sell-q1-screen": "How is the screen condition?",
    "sell-q2-function": "Does everything work properly?",
    "sell-q3-cosmetic": "Cosmetic damage?",
    "sell-condition-perfect": "Perfect",
    "sell-condition-minor": "Minor scratches",
    "sell-condition-cracked": "Crack or break",
    "sell-condition-none": "None",
    "sell-condition-light": "Light wear",
    "sell-condition-heavy": "Visible damage",
    "sell-yes": "Yes",
    "sell-no": "No",
    "sell-minor-issues": "Minor issues",
    "sell-estimated-value": "Estimated value",
    "sell-quote-title": "Your estimated value",
    "sell-accept": "Accept offer",
    "sell-accept-offer": "Accept offer",
    "sell-next": "Next",
    "sell-back": "Back",
    "sell-free-shipping": "Free shipping label",
    "sell-fast-payment": "Fast payment within 48 hours",
    "sell-benefits-title": "Why sell at GSMStunter?",
    "sell-benefit1-title": "Free shipping label",
    "sell-benefit1-desc": "We send you a free shipping label. Pack and ship.",
    "sell-benefit2-title": "Fast payment",
    "sell-benefit2-desc": "Receive your money within 48 hours after we receive your device.",
    "sell-benefit3-title": "Up to €500 for your device",
    "sell-benefit3-desc": "Fair prices for all your devices.",
    "sell-faq-title": "Frequently asked questions about selling",
    "sell-faq1-q": "How does the selling process work?",
    "sell-faq1-a": "Select your device, choose the model, describe the condition and get an instant offer. Accept the offer, ship your device with the free label and receive your money within 48 hours.",
    "sell-faq2-q": "Is shipping free?",
    "sell-faq2-a": "Yes, we send you a free shipping label. You only need to pack your device and drop it off at a PostNL point.",
    "sell-faq3-q": "When will I get my money?",
    "sell-faq3-a": "After inspection of your device, you receive the amount within 48 hours to your account via iDEAL or bank transfer.",

    // Trade Page
    "trade-title": "Trade In Your Device",
    "trade-subtitle": "Swap your old device for a discount on a newer model",
    "trade-hero-title": "Trade In Your Device",
    "trade-hero-subtitle": "Trade in your old device for a new refurbished model. Save money and contribute to a more sustainable world.",
    "trade-calc-title": "Calculate your trade-in value",
    "trade-your-device": "Your current device",
    "trade-wanted-device": "Your desired device",
    "trade-device-type": "Device type",
    "trade-brand": "Brand",
    "trade-model": "Model",
    "trade-condition": "Condition",
    "trade-select-type": "Select type",
    "trade-select-brand": "Select brand",
    "trade-select-model": "Select model",
    "trade-select-condition": "Select condition",
    "trade-wanted-product": "Desired product",
    "trade-select-wanted": "Select product",
    "trade-value": "Trade-in value",
    "trade-in-value": "Trade-in value",
    "trade-additional": "Amount to pay",
    "trade-additional-payment": "To pay",
    "trade-calculate": "Calculate trade-in value",
    "trade-start": "Start trade-in",
    "trade-how-title": "How does trade-in work?",
    "trade-how-step1-title": "Select devices",
    "trade-how-step1-desc": "Choose your current device and the desired refurbished model.",
    "trade-how-step2-title": "Get trade-in value",
    "trade-how-step2-desc": "Calculate your trade-in value instantly and see what you still need to pay.",
    "trade-how-step3-title": "Ship & receive",
    "trade-how-step3-desc": "Ship your old device with the free label and receive your new one.",
    "trade-benefits-title": "Benefits of trading in",
    "trade-benefit1-title": "Save on your upgrade",
    "trade-benefit1-desc": "Lower the price of your new device with your trade-in value.",
    "trade-benefit2-title": "Sustainable",
    "trade-benefit2-desc": "Give your device a second life and reduce e-waste.",
    "trade-benefit3-title": "Certified refurbished",
    "trade-benefit3-desc": "Receive a like-new device with warranty.",
    "trade-faq-title": "Frequently asked questions about trade-in",
    "trade-faq1-q": "How is my trade-in value determined?",
    "trade-faq1-a": "The value depends on your device's brand, model, storage and condition. Our calculator gives an estimate; the final value follows after inspection.",
    "trade-faq2-q": "Can I trade in multiple devices?",
    "trade-faq2-a": "Yes, you can trade in multiple devices. The total trade-in value is deducted from the purchase price.",
    "trade-faq3-q": "When will I receive my new device?",
    "trade-faq3-a": "After receiving and approving your trade-in, we ship your new refurbished device within 2-3 business days.",

    // Cart
    "cart-title": "Shopping Cart",
    "cart-empty": "Your cart is empty",
    "cart-continue": "Continue shopping",
    "cart-summary-title": "Summary",
    "cart-subtotal": "Subtotal",
    "cart-shipping": "Shipping",
    "cart-shipping-free": "Free",
    "cart-tax": "VAT (21%)",
    "cart-total": "Total",
    "cart-promo-placeholder": "Promo code",
    "cart-promo-apply": "Apply",
    "cart-checkout": "Checkout",
    "cart-estimated-delivery": "Estimated delivery: 1-3 business days",

    // Checkout
    "checkout-title": "Checkout",
    "checkout-step-contact": "Contact",
    "checkout-step-shipping": "Shipping",
    "checkout-step-payment": "Payment",
    "checkout-step-review": "Review",
    "checkout-email": "Email address",
    "checkout-name": "Full name",
    "checkout-first-name": "First name",
    "checkout-last-name": "Last name",
    "checkout-address": "Address",
    "checkout-city": "City",
    "checkout-postal": "Postal code",
    "checkout-country": "Country",
    "checkout-phone": "Phone number",
    "checkout-payment-method": "Payment method",
    "checkout-place-order": "Place order",
    "checkout-secure": "Secure checkout with SSL encryption",

    // Account
    "account-title": "My Account",
    "account-dashboard": "Dashboard",
    "account-orders": "Orders",
    "account-wishlist": "Wishlist",
    "account-addresses": "Addresses",
    "account-payments": "Payment Methods",
    "account-sell-history": "Sell History",
    "account-trade-history": "Trade History",
    "account-settings": "Settings",
    "account-logout": "Log out",
    "account-welcome": "Welcome back",
    "account-total-orders": "Total orders",
    "account-active-trades": "Active trades",
    "account-wishlist-items": "Wishlist items",

    // General
    "save": "Save",
    "cancel": "Cancel",
    "close": "Close",
    "loading": "Loading...",
    "view-all": "View all",
    "learn-more": "Learn more",
    "or": "or",
    "from": "from",
    "per": "per",
    "items": "items",
    "added-to-cart": "Added to cart",
    "removed-from-cart": "Removed from cart",
    "guest-checkout": "Checkout as guest"
  }
};

/* ── App State ── */
const AppState = {
  language: localStorage.getItem('gsmstunter-lang') || 'nl',
  cart: JSON.parse(localStorage.getItem('gsmstunter-cart') || '[]'),
  wishlist: JSON.parse(localStorage.getItem('gsmstunter-wishlist') || '[]'),
  viewMode: 'grid',
  currentPage: 1,
  filters: {
    brand: [],
    condition: [],
    priceMin: 0,
    priceMax: 2000,
    storage: [],
    color: [],
    os: []
  }
};

/* ── Language Switcher ── */
function setLanguage(lang) {
  AppState.language = lang;
  localStorage.setItem('gsmstunter-lang', lang);
  applyTranslations();
  updateLangSwitcher();
}

function applyTranslations() {
  const lang = AppState.language;
  const t = translations[lang];
  if (!t) return;

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key]) {
      el.textContent = t[key];
    }
  });

  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if (t[key]) {
      el.placeholder = t[key];
    }
  });

  document.querySelectorAll('[data-i18n-title]').forEach(el => {
    const key = el.getAttribute('data-i18n-title');
    if (t[key]) {
      el.title = t[key];
    }
  });

  document.querySelectorAll('[data-i18n-aria-label]').forEach(el => {
    const key = el.getAttribute('data-i18n-aria-label');
    if (t[key]) {
      el.setAttribute('aria-label', t[key]);
    }
  });

  document.documentElement.lang = lang;
}

function updateLangSwitcher() {
  const activeLang = AppState.language;
  document.querySelectorAll('.lang-dropdown__item').forEach(item => {
    item.classList.toggle('active', item.dataset.lang === activeLang);
  });

  const switcher = document.querySelector('.lang-switcher');
  if (switcher) {
    const flagImg = switcher.querySelector('.lang-switcher__flag');
    const textEl = switcher.querySelector('.lang-switcher__text');
    if (flagImg) {
      flagImg.src = activeLang === 'nl'
        ? 'https://flagcdn.com/w40/nl.png'
        : 'https://flagcdn.com/w40/gb.png';
      flagImg.alt = activeLang === 'nl' ? 'Nederlands' : 'English';
    }
    if (textEl) {
      textEl.textContent = activeLang.toUpperCase();
    }
  }
}

/* ── Cart Management ── */
function addToCart(product) {
  const qty = product.quantity || 1;
  const existing = AppState.cart.find(item => item.id === product.id && item.condition === product.condition && item.storage === product.storage);
  if (existing) {
    existing.quantity = (existing.quantity || 1) + qty;
  } else {
    AppState.cart.push({ ...product, quantity: qty });
  }
  saveCart();
  updateCartCount();
  showToast('success', translate('added-to-cart'), product.name);
}

function removeFromCart(index) {
  AppState.cart.splice(index, 1);
  saveCart();
  updateCartCount();
  showToast('info', translate('removed-from-cart'));
}

function updateCartQuantity(index, quantity) {
  if (quantity <= 0) {
    removeFromCart(index);
    return;
  }
  AppState.cart[index].quantity = quantity;
  saveCart();
  updateCartCount();
}

function saveCart() {
  localStorage.setItem('gsmstunter-cart', JSON.stringify(AppState.cart));
}

function getCartTotal() {
  return AppState.cart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
}

function getCartItemCount() {
  return AppState.cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
}

function updateCartCount() {
  document.querySelectorAll('.cart-count').forEach(el => {
    const count = getCartItemCount();
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}

/* ── Wishlist ── */
function toggleWishlist(productId) {
  const index = AppState.wishlist.indexOf(productId);
  if (index > -1) {
    AppState.wishlist.splice(index, 1);
  } else {
    AppState.wishlist.push(productId);
  }
  localStorage.setItem('gsmstunter-wishlist', JSON.stringify(AppState.wishlist));
  updateWishlistButtons();
}

function updateWishlistButtons() {
  document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
    const id = btn.dataset.wishlistId;
    const isWished = AppState.wishlist.includes(id);
    btn.classList.toggle('active', isWished);
    const icon = btn.querySelector('i');
    if (icon) {
      icon.className = isWished ? 'fas fa-heart' : 'far fa-heart';
    }
  });
}

/* ── Toast Notifications ── */
function showToast(type = 'success', title, message = '') {
  const container = document.querySelector('.toast-container') || createToastContainer();

  const iconMap = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  };

  const colorMap = {
    success: 'var(--color-success)',
    error: 'var(--color-error)',
    warning: 'var(--color-warning)',
    info: 'var(--color-info)'
  };

  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.innerHTML = `
    <i class="${iconMap[type]} toast__icon" style="color: ${colorMap[type]}"></i>
    <div class="toast__content">
      <div class="toast__title">${title}</div>
      ${message ? `<div class="toast__message">${message}</div>` : ''}
    </div>
    <button class="toast__close" onclick="this.closest('.toast').remove()">
      <i class="fas fa-times"></i>
    </button>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function createToastContainer() {
  const container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

/* ── Helper: Translate ── */
function translate(key) {
  return translations[AppState.language]?.[key] || key;
}

/* ── Header Scroll Effect ── */
function initStickyHeader() {
  const header = document.querySelector('.header');
  if (!header) return;

  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 10);
  }, { passive: true });
}

/* ── Mobile Menu ── */
function initMobileMenu() {
  const toggle = document.querySelector('.mobile-menu-toggle');
  const menu = document.querySelector('.mobile-menu');
  const close = document.querySelector('.mobile-menu__close');
  const overlay = document.querySelector('.mobile-menu__overlay');

  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    menu.classList.add('active');
    document.body.style.overflow = 'hidden';
  });

  const closeMenu = () => {
    menu.classList.remove('active');
    document.body.style.overflow = '';
  };

  if (close) close.addEventListener('click', closeMenu);
  if (overlay) overlay.addEventListener('click', closeMenu);
}

/* ── Language Dropdown ── */
function initLangDropdown() {
  const switcher = document.querySelector('.lang-switcher');
  const dropdown = document.querySelector('.lang-dropdown');
  if (!switcher || !dropdown) return;

  switcher.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('active');
  });

  dropdown.querySelectorAll('.lang-dropdown__item').forEach(item => {
    item.addEventListener('click', () => {
      setLanguage(item.dataset.lang);
      dropdown.classList.remove('active');
    });
  });

  document.addEventListener('click', () => {
    dropdown.classList.remove('active');
  });
}

/* ── Search Autocomplete ── */
function initSearch() {
  const input = document.querySelector('.search-bar__input');
  const dropdown = document.querySelector('.search-autocomplete');
  if (!input || !dropdown) return;

  const products = [
    { name: 'iPhone 15 Pro Max', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=80&h=80&fit=crop' },
    { name: 'iPhone 14 Pro', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1663499482523-1c0c1bae4ce1?w=80&h=80&fit=crop' },
    { name: 'Samsung Galaxy S24', category: 'Smartphones', img: 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=80&h=80&fit=crop' },
    { name: 'MacBook Pro M3', category: 'Laptops', img: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=80&h=80&fit=crop' },
    { name: 'iPad Pro 12.9"', category: 'Tablets', img: 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=80&h=80&fit=crop' },
    { name: 'Apple Watch Series 9', category: 'Smartwatches', img: 'https://images.unsplash.com/photo-1546868871-af0de0ae72be?w=80&h=80&fit=crop' },
    { name: 'AirPods Pro', category: 'Headphones', img: 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=80&h=80&fit=crop' }
  ];

  input.addEventListener('input', () => {
    const query = input.value.toLowerCase().trim();
    if (query.length < 2) {
      dropdown.classList.remove('active');
      return;
    }

    const matches = products.filter(p =>
      p.name.toLowerCase().includes(query) || p.category.toLowerCase().includes(query)
    ).slice(0, 5);

    if (matches.length === 0) {
      dropdown.classList.remove('active');
      return;
    }

    dropdown.innerHTML = matches.map(p => `
      <a href="products.html" class="search-autocomplete__item">
        <img src="${p.img}" alt="${p.name}" loading="lazy">
        <div>
          <div style="font-weight: 500; font-size: 0.875rem;">${p.name}</div>
          <div style="font-size: 0.75rem; color: var(--color-text-muted);">${p.category}</div>
        </div>
      </a>
    `).join('');

    dropdown.classList.add('active');
  });

  input.addEventListener('focus', () => {
    if (input.value.length >= 2) dropdown.classList.add('active');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-bar')) {
      dropdown.classList.remove('active');
    }
  });
}

/* ── Scroll Animations ── */
function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
    observer.observe(el);
  });
}

/* ── Accordion ── */
function initAccordions() {
  document.querySelectorAll('.accordion-item__header').forEach(header => {
    header.addEventListener('click', () => {
      const item = header.closest('.accordion-item');
      const isActive = item.classList.contains('active');

      // Close siblings
      item.closest('.accordion')?.querySelectorAll('.accordion-item').forEach(sib => {
        sib.classList.remove('active');
      });

      if (!isActive) {
        item.classList.add('active');
      }
    });
  });

  // FAQ accordion (sell/trade pages)
  document.querySelectorAll('.faq-item__question').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const isActive = item.classList.contains('active');
      const accordion = item.closest('.faq-accordion');

      accordion?.querySelectorAll('.faq-item').forEach(sib => sib.classList.remove('active'));

      if (!isActive) {
        item.classList.add('active');
      }
    });
  });
}

/* ── Tabs ── */
function initTabs() {
  document.querySelectorAll('.tabs').forEach(tabContainer => {
    const tabs = tabContainer.querySelectorAll('.tab');
    const panels = tabContainer.parentElement.querySelectorAll('.tab-panel');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;

        tabs.forEach(t => t.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        tab.classList.add('active');
        const panel = document.getElementById(target);
        if (panel) panel.classList.add('active');
      });
    });
  });
}

/* ── Modal ── */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

function initModals() {
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });

  document.querySelectorAll('.modal__close').forEach(btn => {
    btn.addEventListener('click', () => {
      const overlay = btn.closest('.modal-overlay');
      if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });
}

/* ── Product Filtering (Products Page) ── */
function initProductFilters() {
  const filterCheckboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
  const sortSelect = document.querySelector('.products-toolbar__sort select');

  filterCheckboxes.forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  if (sortSelect) {
    sortSelect.addEventListener('change', applyFilters);
  }
}

function applyFilters() {
  const cards = document.querySelectorAll('.product-card[data-brand]');
  if (cards.length === 0) return;

  const activeFilters = {};
  document.querySelectorAll('.filter-option input:checked').forEach(cb => {
    const group = cb.dataset.filterGroup;
    if (!activeFilters[group]) activeFilters[group] = [];
    activeFilters[group].push(cb.value.toLowerCase());
  });

  let visibleCount = 0;
  cards.forEach(card => {
    let show = true;

    Object.keys(activeFilters).forEach(group => {
      const filterValues = activeFilters[group];
      if (filterValues.length === 0) return;

      const cardValue = (card.dataset[group] || '').toLowerCase();
      if (!filterValues.includes(cardValue)) {
        show = false;
      }
    });

    card.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  const countEl = document.querySelector('.products-toolbar__count span');
  if (countEl) countEl.textContent = visibleCount;
}

/* ── View Toggle (Grid/List) ── */
function initViewToggle() {
  const buttons = document.querySelectorAll('.view-toggle__btn');
  const grid = document.querySelector('.products-grid');
  if (!buttons.length || !grid) return;

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      buttons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const view = btn.dataset.view;
      grid.classList.toggle('products-grid--list', view === 'list');
    });
  });
}

/* ── Product Detail Page ── */
function addProductDetailToCart() {
  const priceEl = document.querySelector('.product-info__price, .product-detail__price');
  const conditionOpt = document.querySelector('.condition-option input:checked');
  const storageOpt = document.querySelector('.storage-option input:checked');
  const qtyEl = document.querySelector('.quantity-selector__value');
  const price = priceEl ? parseInt(priceEl.textContent.replace(/[^\d]/g, '')) : 899;
  const condition = conditionOpt ? conditionOpt.closest('.condition-option').querySelector('.condition-option__text')?.textContent || 'Als nieuw' : 'Als nieuw';
  const storage = storageOpt ? (storageOpt.value === '1024' ? '1TB' : storageOpt.value + 'GB') : '128GB';
  const quantity = qtyEl ? parseInt(qtyEl.textContent) || 1 : 1;
  addToCart({
    id: 'iphone15pro',
    name: 'iPhone 15 Pro',
    price,
    image: 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=200&h=200&fit=crop',
    condition,
    storage,
    quantity
  });
}

/* ── Product Gallery (Detail Page) ── */
function initProductGallery() {
  const thumbs = document.querySelectorAll('.product-gallery__thumb');
  const mainImg = document.querySelector('.product-gallery__main img');
  if (!thumbs.length || !mainImg) return;

  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      thumbs.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      const imgSrc = thumb.dataset.image;
      if (imgSrc) {
        mainImg.src = imgSrc;
      }
    });
  });
}

/* ── Condition Selector (Detail Page) ── */
function initConditionSelector() {
  document.querySelectorAll('.condition-option').forEach(option => {
    option.addEventListener('click', () => {
      option.closest('.condition-selector__options')
        .querySelectorAll('.condition-option')
        .forEach(o => o.classList.remove('active'));
      option.classList.add('active');

      const priceEl = document.querySelector('.product-info__price');
      if (priceEl && option.dataset.price) {
        priceEl.textContent = '€' + option.dataset.price;
      }
    });
  });
}

/* ── Storage Selector (Detail Page) ── */
function initStorageSelector() {
  document.querySelectorAll('.storage-option').forEach(option => {
    option.addEventListener('click', () => {
      option.closest('.storage-selector__options')
        .querySelectorAll('.storage-option')
        .forEach(o => o.classList.remove('active'));
      option.classList.add('active');
    });
  });
}

/* ── Quantity Selector ── */
function initQuantitySelectors() {
  document.querySelectorAll('.quantity-selector').forEach(selector => {
    const minusBtn = selector.querySelector('.quantity-selector__btn:first-child');
    const plusBtn = selector.querySelector('.quantity-selector__btn:last-child');
    const valueEl = selector.querySelector('.quantity-selector__value');

    if (!minusBtn || !plusBtn || !valueEl) return;

    minusBtn.addEventListener('click', () => {
      let val = parseInt(valueEl.textContent) || 1;
      if (val > 1) valueEl.textContent = val - 1;
    });

    plusBtn.addEventListener('click', () => {
      let val = parseInt(valueEl.textContent) || 1;
      if (val < 10) valueEl.textContent = val + 1;
    });
  });
}

/* ── Sell Wizard ── */
function initSellWizard() {
  const wizard = document.querySelector('.sell-wizard');
  if (!wizard) return;

  let currentStep = 1;
  const totalSteps = 4;

  window.sellWizardNext = function () {
    if (currentStep < totalSteps) {
      currentStep++;
      updateWizardStep();
    }
  };

  window.sellWizardBack = function () {
    if (currentStep > 1) {
      currentStep--;
      updateWizardStep();
    }
  };

  function updateWizardStep() {
    wizard.querySelectorAll('.sell-wizard__panel').forEach((panel, index) => {
      panel.classList.toggle('active', index === currentStep - 1);
    });

    wizard.querySelectorAll('.sell-wizard__step').forEach((step, index) => {
      step.classList.remove('active', 'completed');
      if (index + 1 === currentStep) step.classList.add('active');
      else if (index + 1 < currentStep) step.classList.add('completed');
    });
  }

  // Device option selection
  wizard.querySelectorAll('.device-option').forEach(option => {
    option.addEventListener('click', () => {
      const parent = option.closest('.sell-wizard__device-grid, .sell-wizard__brand-grid, .sell-wizard__model-grid');
      if (parent) {
        parent.querySelectorAll('.device-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
      }
    });
  });
}

/* ── Trade Calculator ── */
function initTradeCalculator() {
  const calcBtn = document.querySelector('[data-trade-calculate]');
  if (!calcBtn) return;

  calcBtn.addEventListener('click', () => {
    const tradeValue = Math.floor(Math.random() * 300) + 100;
    const desiredPrice = 899;
    const toPay = Math.max(0, desiredPrice - tradeValue);

    const valueEl = document.querySelector('.trade-value__amount');
    const toPayEl = document.querySelector('.trade-topay__amount');

    if (valueEl) valueEl.textContent = '€' + tradeValue;
    if (toPayEl) toPayEl.textContent = '€' + toPay;

    const resultSection = document.querySelector('.trade-result');
    if (resultSection) {
      resultSection.style.display = 'block';
      resultSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
}

/* ── Cart Page ── */
function initCartPage() {
  if (!document.querySelector('.cart-layout')) return;
  renderCart();
}

function renderCart() {
  const cartItems = document.querySelector('.cart-items');
  const summaryEl = document.querySelector('.cart-summary');
  if (!cartItems) return;

  if (AppState.cart.length === 0) {
    cartItems.innerHTML = `
      <div style="text-align: center; padding: 4rem 2rem;">
        <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--color-text-muted); margin-bottom: 1rem;"></i>
        <h3 data-i18n="cart-empty">${translate('cart-empty')}</h3>
        <a href="products.html" class="btn btn--primary" style="margin-top: 1rem;" data-i18n="cart-continue">${translate('cart-continue')}</a>
      </div>
    `;
    return;
  }

  cartItems.innerHTML = AppState.cart.map((item, index) => `
    <div class="cart-item">
      <div class="cart-item__image">
        <img src="${item.image}" alt="${item.name}" loading="lazy">
      </div>
      <div class="cart-item__details">
        <h3 class="cart-item__title">${item.name}</h3>
        <p class="cart-item__specs">${item.condition || ''} · ${item.storage || ''}</p>
        <div class="cart-item__actions">
          <div class="quantity-selector">
            <button class="quantity-selector__btn" onclick="updateCartQuantity(${index}, ${(item.quantity || 1) - 1})">−</button>
            <span class="quantity-selector__value">${item.quantity || 1}</span>
            <button class="quantity-selector__btn" onclick="updateCartQuantity(${index}, ${(item.quantity || 1) + 1})">+</button>
          </div>
          <button class="cart-item__remove" onclick="removeFromCart(${index}); renderCart();">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>
      <div class="cart-item__price">€${(item.price * (item.quantity || 1)).toFixed(2)}</div>
    </div>
  `).join('');

  if (summaryEl) {
    const subtotal = getCartTotal();
    const shipping = subtotal >= 50 ? 0 : 4.95;
    const tax = subtotal * 0.21;
    const total = subtotal + shipping;

    summaryEl.querySelector('.summary-subtotal').textContent = '€' + subtotal.toFixed(2);
    summaryEl.querySelector('.summary-shipping').textContent = shipping === 0 ? translate('cart-shipping-free') : '€' + shipping.toFixed(2);
    summaryEl.querySelector('.summary-tax').textContent = '€' + tax.toFixed(2);
    summaryEl.querySelector('.summary-total').textContent = '€' + total.toFixed(2);
  }
}

/* ── Checkout ── */
function initCheckout() {
  const form = document.querySelector('.checkout-form');
  if (!form) return;

  let step = 1;
  const totalSteps = 4;

  window.checkoutNext = function () {
    if (validateCheckoutStep(step)) {
      if (step < totalSteps) {
        step++;
        updateCheckoutStep(step);
      }
    }
  };

  window.checkoutBack = function () {
    if (step > 1) {
      step--;
      updateCheckoutStep(step);
    }
  };

  function updateCheckoutStep(s) {
    form.querySelectorAll('.checkout-section').forEach((section, i) => {
      section.style.display = i === s - 1 ? 'block' : 'none';
    });

    document.querySelectorAll('.checkout-step').forEach((el, i) => {
      el.classList.remove('active', 'completed');
      if (i + 1 === s) el.classList.add('active');
      else if (i + 1 < s) el.classList.add('completed');
    });
  }

  function validateCheckoutStep(s) {
    const section = form.querySelectorAll('.checkout-section')[s - 1];
    if (!section) return true;

    const required = section.querySelectorAll('input[required]');
    let valid = true;

    required.forEach(input => {
      if (!input.value.trim()) {
        input.classList.add('error');
        valid = false;
      } else {
        input.classList.remove('error');
      }
    });

    return valid;
  }
}

/* ── Form Validation ── */
function initFormValidation() {
  document.querySelectorAll('input[required]').forEach(input => {
    input.addEventListener('blur', () => {
      if (!input.value.trim()) {
        input.classList.add('error');
      } else {
        input.classList.remove('error');
      }
    });

    input.addEventListener('input', () => {
      input.classList.remove('error');
    });
  });
}

/* ── Newsletter Form ── */
function initNewsletter() {
  const form = document.querySelector('.newsletter__form');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = form.querySelector('input[type="email"]');
    if (email && email.value.trim()) {
      showToast('success', AppState.language === 'nl' ? 'Bedankt voor je aanmelding!' : 'Thanks for subscribing!', email.value);
      email.value = '';
    }
  });
}

/* ── Lazy Loading Images ── */
function initLazyLoading() {
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          observer.unobserve(img);
        }
      });
    }, { rootMargin: '100px' });

    document.querySelectorAll('img[data-src]').forEach(img => observer.observe(img));
  }
}

/* ── Counter Animation ── */
function initCounterAnimations() {
  const counters = document.querySelectorAll('[data-counter]');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.dataset.counter);
        const suffix = el.dataset.counterSuffix || '';
        const prefix = el.dataset.counterPrefix || '';
        const duration = 2000;
        const start = Date.now();

        const tick = () => {
          const elapsed = Date.now() - start;
          const progress = Math.min(elapsed / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          const current = Math.floor(target * eased);
          el.textContent = prefix + current.toLocaleString() + suffix;

          if (progress < 1) requestAnimationFrame(tick);
        };

        tick();
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
}

/* ── Smooth Scroll for Anchor Links ── */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/* ── Filter Group Toggle ── */
function initFilterToggles() {
  document.querySelectorAll('.filter-group__header').forEach(header => {
    header.addEventListener('click', () => {
      const body = header.nextElementSibling;
      const icon = header.querySelector('.filter-group__toggle');
      if (body) {
        const isOpen = body.style.maxHeight && body.style.maxHeight !== '0px';
        body.style.maxHeight = isOpen ? '0px' : body.scrollHeight + 'px';
        if (icon) icon.classList.toggle('collapsed', isOpen);
      }
    });
  });
}

/* ── Mega Menu ── */
function initMegaMenu() {
  const triggers = document.querySelectorAll('[data-mega-menu]');

  triggers.forEach(trigger => {
    const menuId = trigger.dataset.megaMenu;
    const menu = document.getElementById(menuId);
    if (!menu) return;

    trigger.addEventListener('mouseenter', () => {
      menu.classList.add('active');
    });

    trigger.addEventListener('mouseleave', (e) => {
      if (!menu.contains(e.relatedTarget)) {
        menu.classList.remove('active');
      }
    });

    menu.addEventListener('mouseleave', () => {
      menu.classList.remove('active');
    });
  });
}

/* ── Initialize All ── */
document.addEventListener('DOMContentLoaded', () => {
  // Core
  applyTranslations();
  updateLangSwitcher();
  updateCartCount();
  updateWishlistButtons();

  // UI Components
  initStickyHeader();
  initMobileMenu();
  initLangDropdown();
  initSearch();
  initScrollAnimations();
  initAccordions();
  initTabs();
  initModals();
  initMegaMenu();
  initSmoothScroll();
  initLazyLoading();
  initCounterAnimations();
  initFormValidation();
  initNewsletter();

  // Page-specific
  initProductFilters();
  initViewToggle();
  initFilterToggles();
  initProductGallery();
  initConditionSelector();
  initStorageSelector();
  initQuantitySelectors();
  initSellWizard();
  initTradeCalculator();
  initCartPage();
  initCheckout();
});
