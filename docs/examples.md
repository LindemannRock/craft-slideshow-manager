# Slideshow Manager Examples

Comprehensive examples demonstrating various features and use cases for the Slideshow Manager plugin.

## Table of Contents

- [Basic Slideshow](#basic-slideshow)
- [Custom Styling with CSS Variables](#custom-styling-with-css-variables)
- [Runtime Config Overrides](#runtime-config-overrides)
- [Debug Mode](#debug-mode)
- [Grid Mode](#grid-mode)
- [Different Effects](#different-effects)
- [Responsive Visibility](#responsive-visibility)
- [Autoplay Configuration](#autoplay-configuration)
- [Responsive Breakpoints](#responsive-breakpoints)
- [Multiple Sliders on One Page](#multiple-sliders-on-one-page)
- [Using Entry Field Config vs Global Settings](#using-entry-field-config-vs-global-settings)
- [Custom Swiper Installation](#custom-swiper-installation)
- [Accessing Swiper Instance](#accessing-swiper-instance)
- [Advanced: Thumbnail Navigation](#advanced-thumbnail-navigation)

---

## Basic Slideshow

The simplest implementation using entry field content and auto-loading:

```twig
{# Get config from entry field or fall back to global settings #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper"
     id="{{ sliderId }}"
     data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">

    <div class="swiper-wrapper">
        {% for slide in entry.slides %}
            <div class="swiper-slide">
                <img src="{{ slide.image.one().url }}" alt="{{ slide.title }}">
                <div class="slide-caption">
                    <h3>{{ slide.title }}</h3>
                </div>
            </div>
        {% endfor %}
    </div>

    {# Navigation buttons #}
    <div class="swiper-button-prev swiper-button-prev-{{ sliderId }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ sliderId }}"></div>

    {# Pagination #}
    <div class="swiper-pagination swiper-pagination-{{ sliderId }}"></div>
</div>

{# Initialize the slider #}
{{ craft.slideshowManager.initSlider(sliderId) }}
```

---

## Custom Styling with CSS Variables

Use CSS custom properties to customize Swiper's appearance:

```twig
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

{# Build CSS variables from plugin settings #}
{% set cssVars = craft.slideshowManager.buildCssVars() %}

<div class="swiper"
     id="{{ sliderId }}"
     style="{{ cssVars }}"
     data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">

    <div class="swiper-wrapper">
        {# slides... #}
    </div>

    <div class="swiper-button-prev swiper-button-prev-{{ sliderId }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ sliderId }}"></div>
    <div class="swiper-pagination swiper-pagination-{{ sliderId }}"></div>
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

**Override CSS variables inline:**

```twig
<div class="swiper"
     id="{{ sliderId }}"
     style="{{ cssVars }}; --_swiper-navigation-color: #ff0000; --_swiper-pagination-color: #0000ff;"
     data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# ... #}
</div>
```

**Or in your stylesheet:**

```css
.my-custom-slider {
    /* Override navigation color */
    --_swiper-navigation-color: #ff6600;
    --_swiper-navigation-bg: rgba(255, 255, 255, 0.9);
    --_swiper-navigation-size: 32px;

    /* Override pagination */
    --_swiper-pagination-color: #ff6600;
    --_swiper-pagination-bullet-size: 12px;
    --_swiper-pagination-bullet-inactive-opacity: 0.3;
}
```

---

## Runtime Config Overrides

Override Swiper configuration at initialization time without modifying the field config:

```twig
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper"
     id="{{ sliderId }}"
     data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{# Override speed and add custom event callbacks #}
{{ craft.slideshowManager.initSlider(sliderId, {
    speed: 1000,
    on: {
        slideChange: function() {
            console.log('Slide changed to:', this.activeIndex);
        },
        reachEnd: function() {
            console.log('Reached the end!');
        }
    }
}) }}
```

**Common override use cases:**

```twig
{# Disable loop on this specific instance #}
{{ craft.slideshowManager.initSlider(sliderId, { loop: false }) }}

{# Change autoplay delay #}
{{ craft.slideshowManager.initSlider(sliderId, {
    autoplay: { delay: 5000 }
}) }}

{# Add keyboard control #}
{{ craft.slideshowManager.initSlider(sliderId, {
    keyboard: {
        enabled: true,
        onlyInViewport: true
    }
}) }}
```

---

## Debug Mode

Enable debug logging to troubleshoot initialization issues:

```twig
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{# Enable debug mode - logs config and instance to console #}
{{ craft.slideshowManager.initSlider(sliderId, [], true) }}
```

This will output:
```
Initializing Swiper "slider-123456" with config: { slidesPerView: 1, ... }
Swiper "slider-123456" initialized: Swiper {params: {...}, ...}
```

---

## Grid Mode

Display multiple rows of slides.

**Note:** If using npm installation, import the Grid module:
```javascript
import { Grid } from 'swiper/modules';
import 'swiper/css/grid';
Swiper.use([Grid]);
```

```twig
{# Assumes config field has gridEnabled: true, gridRows: 2 #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper"
     id="{{ sliderId }}"
     data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">

    <div class="swiper-wrapper">
        {% for product in craft.entries.section('products').limit(12).all() %}
            <div class="swiper-slide">
                <div class="product-card">
                    <img src="{{ product.image.one().url }}" alt="{{ product.title }}">
                    <h4>{{ product.title }}</h4>
                    <p>{{ product.price }}</p>
                </div>
            </div>
        {% endfor %}
    </div>

    <div class="swiper-button-prev swiper-button-prev-{{ sliderId }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ sliderId }}"></div>
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

**Or override grid settings at runtime:**

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    grid: {
        rows: 3,
        fill: 'row'
    },
    slidesPerView: 4,
    spaceBetween: 20
}) }}
```

---

## Different Effects

Swiper supports various transition effects.

**Note:** If you're using npm installation instead of CDN, you must import the effect modules and their CSS:
```javascript
import { EffectFade, EffectCube, EffectCoverflow, EffectCards } from 'swiper/modules';
import 'swiper/css/effect-fade';
import 'swiper/css/effect-cube';
import 'swiper/css/effect-coverflow';
import 'swiper/css/effect-cards';

Swiper.use([EffectFade, EffectCube, EffectCoverflow, EffectCards]);
```

### Fade Effect

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    effect: 'fade',
    fadeEffect: {
        crossFade: true
    }
}) }}
```

### Cube Effect

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    effect: 'cube',
    cubeEffect: {
        shadow: true,
        slideShadows: true,
        shadowOffset: 20,
        shadowScale: 0.94
    }
}) }}
```

### Coverflow Effect

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    effect: 'coverflow',
    coverflowEffect: {
        rotate: 50,
        stretch: 0,
        depth: 100,
        modifier: 1,
        slideShadows: true
    }
}) }}
```

### Cards Effect

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    effect: 'cards',
    cardsEffect: {
        perSlideOffset: 8,
        perSlideRotate: 2,
        rotate: true,
        slideShadows: true
    }
}) }}
```

---

## Responsive Visibility

Control navigation/pagination visibility on different screen sizes:

```twig
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    <div class="swiper-wrapper">
        {# slides... #}
    </div>

    {# Hide navigation on mobile, show on desktop #}
    <div class="swiper-button-prev swiper-button-prev-{{ sliderId }} {{ craft.slideshowManager.getVisibilityClasses('hide-mobile') }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ sliderId }} {{ craft.slideshowManager.getVisibilityClasses('hide-mobile') }}"></div>

    {# Show pagination on mobile only #}
    <div class="swiper-pagination swiper-pagination-{{ sliderId }} {{ craft.slideshowManager.getVisibilityClasses('mobile-only') }}"></div>
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

**Available visibility options:**
- `default` - Always visible
- `hide-mobile` - Hidden on mobile, visible on desktop (hidden md:block)
- `hide-desktop` - Visible on mobile, hidden on desktop (block md:hidden)
- `mobile-only` - Visible on mobile only (block md:hidden)
- `desktop-only` - Visible on desktop only (hidden md:block)

---

## Autoplay Configuration

Various autoplay patterns.

**Note:** If using npm installation, import the Autoplay module:
```javascript
import { Autoplay } from 'swiper/modules';
Swiper.use([Autoplay]);
```

### Basic Autoplay

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    autoplay: {
        delay: 3000,
        disableOnInteraction: false
    }
}) }}
```

### Pause on Hover

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    autoplay: {
        delay: 3000,
        pauseOnMouseEnter: true
    }
}) }}
```

### Reverse Direction

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    autoplay: {
        delay: 2500,
        reverseDirection: true
    }
}) }}
```

### Stop After Last Slide

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    autoplay: {
        delay: 3000,
        stopOnLastSlide: true
    },
    loop: false
}) }}
```

---

## Responsive Breakpoints

Adjust slides per view based on screen size:

```twig
{# Assumes config field has breakpoints configured #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

**Or define breakpoints at runtime:**

```twig
{{ craft.slideshowManager.initSlider(sliderId, {
    slidesPerView: 1,
    spaceBetween: 10,
    breakpoints: {
        640: {
            slidesPerView: 2,
            spaceBetween: 20
        },
        768: {
            slidesPerView: 3,
            spaceBetween: 30
        },
        1024: {
            slidesPerView: 4,
            spaceBetween: 40
        }
    }
}) }}
```

---

## Multiple Sliders on One Page

Handle multiple independent sliders:

```twig
{# Hero Slider #}
{% set heroId = 'hero-slider' %}
{% set heroConfig = craft.slideshowManager.buildSwiperConfig(entry.heroConfig, heroId) %}

<div class="swiper" id="{{ heroId }}" data-swiper-config="{{ heroConfig|json_encode|e('html_attr') }}">
    <div class="swiper-wrapper">
        {% for slide in entry.heroSlides %}
            <div class="swiper-slide">{# ... #}</div>
        {% endfor %}
    </div>
    <div class="swiper-button-prev swiper-button-prev-{{ heroId }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ heroId }}"></div>
</div>

{{ craft.slideshowManager.initSlider(heroId) }}

{# Testimonials Slider #}
{% set testimonialsId = 'testimonials-slider' %}
{% set testimonialsConfig = craft.slideshowManager.buildSwiperConfig(entry.testimonialsConfig, testimonialsId) %}

<div class="swiper" id="{{ testimonialsId }}" data-swiper-config="{{ testimonialsConfig|json_encode|e('html_attr') }}">
    <div class="swiper-wrapper">
        {% for testimonial in entry.testimonials %}
            <div class="swiper-slide">{# ... #}</div>
        {% endfor %}
    </div>
    <div class="swiper-pagination swiper-pagination-{{ testimonialsId }}"></div>
</div>

{{ craft.slideshowManager.initSlider(testimonialsId, { autoplay: { delay: 5000 } }) }}

{# Products Slider #}
{% set productsId = 'products-slider' %}
{% set productsConfig = craft.slideshowManager.buildSwiperConfig(craft.slideshowManager.settings.defaultSwiperConfig, productsId) %}

<div class="swiper" id="{{ productsId }}" data-swiper-config="{{ productsConfig|json_encode|e('html_attr') }}">
    <div class="swiper-wrapper">
        {% for product in craft.entries.section('products').limit(10).all() %}
            <div class="swiper-slide">{# ... #}</div>
        {% endfor %}
    </div>
    <div class="swiper-button-prev swiper-button-prev-{{ productsId }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ productsId }}"></div>
</div>

{{ craft.slideshowManager.initSlider(productsId, {
    slidesPerView: 4,
    spaceBetween: 20
}) }}
```

---

## Using Entry Field Config vs Global Settings

### Entry Field Config (Preferred for Content-Specific Slideshows)

```twig
{# Use config from entry's slideshow config field #}
{% set config = entry.slideshowConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

### Global Settings (For Consistent Styling Across Site)

```twig
{# Use global plugin settings #}
{% set config = craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

### Fallback Pattern (Recommended)

```twig
{# Try entry field first, fall back to global settings #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

---

## Custom Swiper Installation

If you prefer to bundle Swiper yourself instead of using the CDN:

### Step 1: Install Swiper

```bash
npm install swiper
# or
yarn add swiper
# or
pnpm add swiper
```

### Step 2: Import in Your JavaScript

```javascript
// src/js/app.js
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade, EffectCube, EffectCoverflow, EffectCards, Grid } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/effect-cube';
import 'swiper/css/effect-coverflow';
import 'swiper/css/effect-cards';
import 'swiper/css/grid';

// Configure Swiper to use modules globally
Swiper.use([Navigation, Pagination, Autoplay, EffectFade, EffectCube, EffectCoverflow, EffectCards, Grid]);

// Make Swiper globally available
window.Swiper = Swiper;
```

**Important:** Only import the modules you actually need. This example includes the most commonly used modules, but you can reduce bundle size by only importing what you use.

**Available modules:**
- **Navigation & Controls:** `Navigation`, `Pagination`, `Scrollbar`, `Keyboard`, `Mousewheel`
- **Effects:** `EffectFade`, `EffectCube`, `EffectFlip`, `EffectCoverflow`, `EffectCards`, `EffectCreative`
- **Layout:** `Grid`, `FreeMode`, `Thumbs`, `Parallax`, `Zoom`, `Virtual`
- **Other:** `Autoplay`, `HashNavigation`, `History`, `Controller`, `A11y`, `Manipulation`

See [Swiper Modules Documentation](https://swiperjs.com/swiper-api#using-js-modules) for complete details.

### Step 3: Disable Plugin Auto-Loading

In `config/slideshow-manager.php`:

```php
return [
    'autoLoadSwiperCss' => false,
    'autoLoadSwiperJs' => false,
];
```

### Step 4: Use as Normal

```twig
{# Plugin won't load Swiper, but initSlider() still works #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}

<div class="swiper" id="{{ sliderId }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    {# slides... #}
</div>

{{ craft.slideshowManager.initSlider(sliderId) }}
```

---

## Accessing Swiper Instance

The Swiper instance is stored on the element for programmatic access:

```twig
{% set sliderId = 'my-slider' %}
{# ... slideshow markup ... #}
{{ craft.slideshowManager.initSlider(sliderId) }}

{% js %}
// Access the Swiper instance
const swiperEl = document.getElementById('my-slider');
const swiper = swiperEl.swiper;

// Control the slider programmatically
document.getElementById('custom-next').addEventListener('click', () => {
    swiper.slideNext();
});

document.getElementById('custom-prev').addEventListener('click', () => {
    swiper.slidePrev();
});

document.getElementById('pause-btn').addEventListener('click', () => {
    swiper.autoplay.stop();
});

document.getElementById('play-btn').addEventListener('click', () => {
    swiper.autoplay.start();
});

// Get current slide index
console.log('Current slide:', swiper.activeIndex);

// Get total slides
console.log('Total slides:', swiper.slides.length);

// Listen to events
swiper.on('slideChange', function () {
    console.log('Changed to slide:', this.activeIndex);
});
{% endjs %}
```

---

## Advanced: Thumbnail Navigation

Create a main slider with thumbnail navigation.

**Note:** If using npm installation, import the Thumbs and FreeMode modules:
```javascript
import { Thumbs, FreeMode } from 'swiper/modules';
Swiper.use([Thumbs, FreeMode]);
```

```twig
{% set mainId = 'main-slider' %}
{% set thumbsId = 'thumbs-slider' %}
{% set mainConfig = craft.slideshowManager.buildSwiperConfig(entry.slideshowConfig, mainId) %}

{# Thumbnails slider #}
<div class="swiper"
     id="{{ thumbsId }}"
     style="margin-bottom: 20px;">
    <div class="swiper-wrapper">
        {% for slide in entry.slides %}
            <div class="swiper-slide" style="width: auto; cursor: pointer;">
                <img src="{{ slide.image.one().url }}"
                     alt="{{ slide.title }}"
                     style="width: 100px; height: 100px; object-fit: cover;">
            </div>
        {% endfor %}
    </div>
</div>

{# Main slider #}
<div class="swiper"
     id="{{ mainId }}"
     data-swiper-config="{{ mainConfig|json_encode|e('html_attr') }}">
    <div class="swiper-wrapper">
        {% for slide in entry.slides %}
            <div class="swiper-slide">
                <img src="{{ slide.image.one().url }}"
                     alt="{{ slide.title }}"
                     style="width: 100%; height: 500px; object-fit: cover;">
            </div>
        {% endfor %}
    </div>
    <div class="swiper-button-prev swiper-button-prev-{{ mainId }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ mainId }}"></div>
</div>

{% js %}
document.addEventListener('DOMContentLoaded', function() {
    // Initialize thumbnails slider first
    const thumbsSwiper = new Swiper('#{{ thumbsId }}', {
        spaceBetween: 10,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
    });

    // Initialize main slider with thumbs
    const mainEl = document.getElementById('{{ mainId }}');
    const mainConfig = JSON.parse(mainEl.getAttribute('data-swiper-config'));

    const mainSwiper = new Swiper('#{{ mainId }}', {
        ...mainConfig,
        thumbs: {
            swiper: thumbsSwiper
        }
    });

    // Store instance on element
    mainEl.swiper = mainSwiper;
    document.getElementById('{{ thumbsId }}').swiper = thumbsSwiper;
});
{% endjs %}
```

---

## Need More Help?

- Check the [Swiper API documentation](https://swiperjs.com/swiper-api) for all available options
- Review the plugin's [README.md](../README.md) for configuration details
- Enable [Debug Mode](#debug-mode) to troubleshoot initialization issues
