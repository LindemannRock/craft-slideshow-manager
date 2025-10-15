# Slideshow Manager for Craft CMS

A comprehensive slideshow management plugin with Swiper.js integration for Craft CMS 5.x.

## Table of Contents

- [Slideshow Manager for Craft CMS](#slideshow-manager-for-craft-cms)
  - [Table of Contents](#table-of-contents)
  - [Quick Start](#quick-start)
  - [Features](#features)
  - [Requirements](#requirements)
  - [Installation](#installation)
    - [Via Composer (Development)](#via-composer-development)
    - [Via Composer (Production - Coming Soon)](#via-composer-production---coming-soon)
    - [Via Control Panel](#via-control-panel)
  - [Custom Swiper Installation](#custom-swiper-installation)
    - [Option 1: Using npm (Recommended for Production)](#option-1-using-npm-recommended-for-production)
    - [Option 2: Using CDN (Default)](#option-2-using-cdn-default)
    - [Option 3: Custom CDN or Version](#option-3-custom-cdn-or-version)
  - [Configuration](#configuration)
    - [Plugin Settings](#plugin-settings)
      - [General Settings](#general-settings)
      - [Swiper Configuration Tabs](#swiper-configuration-tabs)
    - [Config File](#config-file)
    - [Environment-Specific Configuration](#environment-specific-configuration)
  - [Usage](#usage)
    - [Field Types](#field-types)
      - [Slideshow Config Field](#slideshow-config-field)
    - [Global Settings](#global-settings)
    - [Template Usage](#template-usage)
      - [Basic Slideshow](#basic-slideshow)
      - [With Debug Logging](#with-debug-logging)
      - [With Runtime Overrides](#with-runtime-overrides)
      - [With Visibility Classes](#with-visibility-classes)
    - [Template Variables](#template-variables)
      - [`settings`](#settings)
      - [`buildSwiperConfig(config, sliderId)`](#buildswiperconfigconfig-sliderid)
      - [`buildCssVars(cssVars)`](#buildcssvarscssvars)
      - [`initSlider(sliderId, overrides, debug)`](#initslidersliderid-overrides-debug)
      - [`getVisibilityClasses(visibility)`](#getvisibilityclassesvisibility)
  - [CSS Custom Properties](#css-custom-properties)
    - [Available CSS Variables](#available-css-variables)
      - [Theme](#theme)
      - [Navigation](#navigation)
      - [Pagination](#pagination)
      - [Scrollbar](#scrollbar)
      - [Other](#other)
    - [Usage Pattern](#usage-pattern)
  - [Swiper Configuration](#swiper-configuration)
  - [Examples](#examples)
  - [Logging](#logging)
    - [Log Levels](#log-levels)
    - [Configuration](#configuration-1)
    - [Log Files](#log-files)
    - [What's Logged](#whats-logged)
    - [Log Management](#log-management)
  - [Troubleshooting](#troubleshooting)
    - [Slideshow Not Initializing](#slideshow-not-initializing)
    - [Navigation/Pagination Not Showing](#navigationpagination-not-showing)
    - [CSS Variables Not Working](#css-variables-not-working)
    - [Field Not Saving](#field-not-saving)
  - [Support](#support)
  - [License](#license)

## Quick Start

Get up and running with Slideshow Manager in 5 minutes:

**1. Install the plugin**
```bash
composer require lindemannrock/craft-slideshow-manager
./craft plugin/install slideshow-manager
```

**2. Configure global settings (optional)**
- Go to **Slideshow Manager → Settings** in the Control Panel
- Configure default Swiper behavior (navigation, pagination, autoplay, etc.)
- Set CSS custom properties for styling

**3. Add a field to your section**
- Go to **Settings → Fields**
- Create a new **"Slideshow Config"** field (for per-entry config)
- Add it to your entry type

**4. Use in templates**
```twig
{# Get config from entry field or use global defaults #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}
{% set sliderId = 'slider-' ~ random() %}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}
{% set cssVars = craft.slideshowManager.buildCssVars() %}

{# Build slideshow - CSS/JS auto-loaded by plugin #}
<div class="swiper" id="{{ sliderId }}" style="{{ cssVars }}" data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">
    <div class="swiper-wrapper">
        {% for slide in entry.slides %}
            <div class="swiper-slide">
                {{ slide.content }}
            </div>
        {% endfor %}
    </div>

    {# Navigation/pagination if enabled #}
    {% if config.navigation %}
        <div class="swiper-button-prev swiper-button-prev-{{ sliderId }}"></div>
        <div class="swiper-button-next swiper-button-next-{{ sliderId }}"></div>
    {% endif %}

    {% if config.paginationEnabled %}
        <div class="swiper-pagination swiper-pagination-{{ sliderId }}"></div>
    {% endif %}
</div>

{# Initialize slider - simple one-liner! #}
{{ craft.slideshowManager.initSlider(sliderId) }}
```

**That's it!** Your slideshow is live. See [Usage](#usage) for more details and [Examples](docs/examples.md) for comprehensive use cases.

## Features

- **Automatic Asset Loading**: CSS and JS automatically injected when enabled in settings
- **Two Field Types**: Slideshow field (Matrix-based) and Config field (per-entry settings)
- **Comprehensive Configuration**: 30+ Swiper options across 5 organized settings tabs
- **Per-Entry Customization**: Optional config field for entry-specific slideshow settings
- **Responsive Controls**: Breakpoint-based configuration with visual UI
- **Visibility Management**: Control navigation/pagination display per device (mobile/desktop)
- **CSS Custom Properties**: Full Swiper styling customization via CSS variables
- **Config File Overrides**: Environment-specific settings
- **Helper Methods**: Simple template methods for initialization and config transformation
- **Integrated Logging**: Built-in logging with configurable levels

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

### Via Composer (Development)

Until published on Packagist, install directly from the repository:

```bash
cd /path/to/project
composer config repositories.slideshow-manager vcs https://github.com/LindemannRock/craft-slideshow-manager
composer require lindemannrock/craft-slideshow-manager:dev-main
./craft plugin/install slideshow-manager
```

### Via Composer (Production - Coming Soon)

Once published on Packagist:

```bash
cd /path/to/project
composer require lindemannrock/craft-slideshow-manager
./craft plugin/install slideshow-manager
```

### Via Control Panel

In the Control Panel, go to **Settings → Plugins** and click "Install" for Slideshow Manager.

**Note:** Frontend assets are pre-built and included. No build step required for installation.

## Custom Swiper Installation

By default, the plugin auto-loads Swiper from CDN. However, you can use your own Swiper installation:

### Option 1: Using npm (Recommended for Production)

**1. Install Swiper via npm:**
```bash
npm install swiper
# or
yarn add swiper
# or
pnpm add swiper
```

**2. Import in your JavaScript:**
```javascript
// Your main JS file (e.g., src/js/app.js)
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

**Note:** Only import the modules you need. Available modules include:
- `Navigation`, `Pagination`, `Scrollbar`
- `Autoplay`, `EffectFade`, `EffectCube`, `EffectFlip`, `EffectCoverflow`, `EffectCards`, `EffectCreative`
- `Grid`, `FreeMode`, `Thumbs`, `Parallax`, `Zoom`
- `Keyboard`, `Mousewheel`, `HashNavigation`, `History`, `Controller`, `A11y`, `Virtual`, `Manipulation`

See [Swiper Modules Documentation](https://swiperjs.com/swiper-api#using-js-modules) for complete details.

**3. Disable auto-loading in plugin settings:**

Via Control Panel:
- Go to **Slideshow Manager → Settings → General**
- Disable "Auto Load Swiper CSS"
- Disable "Auto Load Swiper JS"

Or via config file:
```php
// config/slideshow-manager.php
return [
    'autoLoadSwiperCss' => false,
    'autoLoadSwiperJs' => false,
];
```

**4. Use the plugin normally:**
The `initSlider()` helper will use your bundled Swiper installation automatically.

### Option 2: Using CDN (Default)

The plugin auto-loads Swiper from CDN when enabled in settings:
- **CSS**: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css`
- **JS**: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js`

This is the easiest option but may not be ideal for production due to:
- Additional HTTP requests
- No control over caching
- Dependency on external CDN availability

### Option 3: Custom CDN or Version

You can use a different CDN or Swiper version by:

1. Disabling auto-loading in settings
2. Loading Swiper manually in your template:

```twig
{# In your layout template #}
<link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css">
<script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
```

## Configuration

### Plugin Settings

Settings can be configured in the Control Panel at **Slideshow Manager → Settings**, or via a config file.

#### General Settings

- **Plugin Name**: Customize the plugin display name
- **Auto Load Swiper CSS**: Automatically inject Swiper CSS on frontend pages
- **Auto Load Swiper JS**: Automatically inject Swiper JS on frontend pages
- **Enable Cache**: Cache slideshow data for better performance
- **Cache Duration**: How long to cache data (in seconds)
- **Log Level**: Logging verbosity (debug, info, warning, error)

#### Swiper Configuration Tabs

- **Basic Settings**: Navigation, pagination, autoplay, effects, loop
- **Layout & Responsive**: Slides per view, spacing, grid mode, breakpoints
- **Controls**: Keyboard, mousewheel, scrollbar, hash navigation
- **Advanced**: Free mode, lazy loading, parallax, zoom, virtual slides

### Config File

Create a `config/slideshow-manager.php` file to override default settings:

```php
<?php

return [
    // Plugin settings
    'pluginName' => 'Slideshow',
    'autoLoadSwiperCss' => true,
    'autoLoadSwiperJs' => true,
    'enableCache' => true,
    'cacheDuration' => 3600,
    'logLevel' => 'error', // debug, info, warning, error

    // Default Swiper configuration
    'defaultSwiperConfig' => [
        // Basic
        'loop' => true,
        'speed' => 300,
        'effect' => 'slide', // slide, fade, cube, coverflow, flip, cards, creative

        // Navigation
        'navigation' => true,
        'navigationVisibility' => 'default', // default, hide-mobile, hide-desktop, mobile-only, desktop-only

        // Pagination
        'paginationEnabled' => true,
        'paginationClickable' => true,
        'paginationType' => 'bullets', // bullets, fraction, progressbar
        'paginationVisibility' => 'default',

        // Autoplay
        'autoplayEnabled' => false,
        'autoplayDelay' => 3000,
        'autoplayDisableOnInteraction' => false,

        // Layout
        'slidesPerView' => 1,
        'spaceBetween' => 0,
        'centeredSlides' => false,

        // Grid
        'gridEnabled' => false,
        'gridRows' => 1,
        'gridFill' => 'row', // row, column

        // Responsive breakpoints
        'breakpoints' => [
            ['width' => 0, 'slidesPerView' => 1, 'spaceBetween' => 0],
            ['width' => 640, 'slidesPerView' => 1, 'spaceBetween' => 10],
            ['width' => 768, 'slidesPerView' => 2, 'spaceBetween' => 20],
            ['width' => 1024, 'slidesPerView' => 3, 'spaceBetween' => 30],
        ],

        // See src/config.php for all available options
    ],

    // Swiper CSS Custom Properties
    'swiperCssVars' => [
        // Theme
        'themeColor' => 'var(--color-brand)',

        // Navigation
        'navigationColor' => 'var(--color-brand)',
        // 'navigationSize' => '44px',

        // Pagination
        'paginationColor' => 'var(--color-brand)',
        // 'paginationBulletSize' => '8px',

        // See src/config.php for all available CSS variables
    ],
];
```

Settings defined in the config file will override CP settings.

### Environment-Specific Configuration

```php
<?php

return [
    '*' => [
        // Global defaults
        'logLevel' => 'error',
        'enableCache' => true,
    ],
    'dev' => [
        // Development - verbose logging
        'logLevel' => 'debug',
        'cacheDuration' => 3600, // 1 hour
    ],
    'staging' => [
        // Staging - moderate logging
        'logLevel' => 'info',
        'cacheDuration' => 86400, // 1 day
    ],
    'production' => [
        // Production - minimal logging
        'logLevel' => 'error',
        'cacheDuration' => 2592000, // 30 days
    ],
];
```

## Usage

### Field Types

#### Slideshow Config Field

The Config field stores per-entry Swiper configuration:

1. Go to **Settings → Fields**
2. Create a new field, select **"Slideshow Config"**
3. Add to entry types where custom config is needed
4. Configure in entries:
   - Navigation (on/off, visibility controls)
   - Pagination (on/off, type, visibility controls)
   - Autoplay (enabled, delay, disable on interaction)
   - Loop, Speed, Effect
   - Slides Per View, Spacing, Centered Slides
   - Grid Mode
   - Responsive Breakpoints

### Global Settings

Configure default Swiper settings at **Slideshow Manager → Settings**:

- **General**: Plugin name, asset loading, caching, logging
- **Basic Settings**: Navigation, pagination, autoplay, effects, loop
- **Layout & Responsive**: Slides display, grid mode, breakpoints
- **Controls**: Keyboard, mousewheel, scrollbar, hash navigation
- **Advanced**: Free mode, lazy loading, parallax, zoom, virtual slides

### Template Usage

#### Basic Slideshow

```twig
{# Get config (from entry field or global defaults) #}
{% set config = entry.slideshowConfig ?? craft.slideshowManager.settings.defaultSwiperConfig %}

{# Generate unique slider ID #}
{% set sliderId = 'slider-' ~ random() %}

{# Build Swiper config and CSS vars #}
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}
{% set cssVars = craft.slideshowManager.buildCssVars() %}

{# Render slideshow - CSS/JS auto-loaded if enabled in settings #}
<div class="swiper"
     id="{{ sliderId }}"
     style="{{ cssVars }}"
     data-swiper-config="{{ swiperConfig|json_encode|e('html_attr') }}">

    <div class="swiper-wrapper">
        {% for slide in entry.slides %}
            <div class="swiper-slide">
                <img src="{{ slide.image.url }}" alt="{{ slide.image.title }}">
                <h3>{{ slide.title }}</h3>
                <p>{{ slide.description }}</p>
            </div>
        {% endfor %}
    </div>

    {# Navigation buttons (if enabled in config) #}
    {% if config.navigation %}
        <div class="swiper-button-prev swiper-button-prev-{{ sliderId }}"></div>
        <div class="swiper-button-next swiper-button-next-{{ sliderId }}"></div>
    {% endif %}

    {# Pagination (if enabled in config) #}
    {% if config.paginationEnabled %}
        <div class="swiper-pagination swiper-pagination-{{ sliderId }}"></div>
    {% endif %}
</div>

{# Initialize slider #}
{{ craft.slideshowManager.initSlider(sliderId) }}
```

#### With Debug Logging

```twig
{# Enable console logging for debugging #}
{{ craft.slideshowManager.initSlider(sliderId, {}, true) }}
```

This outputs initialization details to the browser console.

#### With Runtime Overrides

```twig
{# Override config at initialization #}
{{ craft.slideshowManager.initSlider(sliderId, {
    speed: 1000,
    autoplay: {
        delay: 5000,
        disableOnInteraction: true
    }
}) }}
```

#### With Visibility Classes

```twig
{# Hide navigation on mobile, show on desktop #}
{% if config.navigation %}
    <div class="swiper-button-prev swiper-button-prev-{{ sliderId }} {{ craft.slideshowManager.getVisibilityClasses('hide-mobile') }}"></div>
    <div class="swiper-button-next swiper-button-next-{{ sliderId }} {{ craft.slideshowManager.getVisibilityClasses('hide-mobile') }}"></div>
{% endif %}
```

Visibility options:
- `default` - Always visible
- `hide-mobile` - Hidden on mobile, visible on desktop
- `hide-desktop` - Visible on mobile, hidden on desktop
- `mobile-only` - Only visible on mobile
- `desktop-only` - Only visible on desktop

### Template Variables

#### `settings`

Access plugin settings:

```twig
{# Get all settings #}
{% set settings = craft.slideshowManager.settings %}

{# Access specific settings #}
{% set defaultConfig = craft.slideshowManager.settings.defaultSwiperConfig %}
{% set pluginName = craft.slideshowManager.settings.pluginName %}
{% set autoLoadCss = craft.slideshowManager.settings.autoLoadSwiperCss %}
```

**Returns:** Settings model with all plugin configuration

#### `buildSwiperConfig(config, sliderId)`

Transforms flat field structure into nested Swiper format:

```twig
{% set swiperConfig = craft.slideshowManager.buildSwiperConfig(config, sliderId) %}
```

**Parameters:**
- `config` (array) - Merged configuration (field + global)
- `sliderId` (string) - Unique identifier for this slider

**Returns:** Array ready for Swiper initialization

#### `buildCssVars(cssVars)`

Builds inline CSS custom properties for Swiper styling:

```twig
{% set cssVars = craft.slideshowManager.buildCssVars() %}
{# Or with custom vars: #}
{% set cssVars = craft.slideshowManager.buildCssVars({
    themeColor: '#007bff',
    navigationColor: '#0056b3',
    paginationColor: '#6c757d'
}) %}
```

**Parameters:**
- `cssVars` (array|null) - Optional CSS variables (uses settings if null)

**Returns:** String of CSS custom properties for inline style

#### `initSlider(sliderId, overrides, debug)`

Initializes Swiper for a specific slider:

```twig
{{ craft.slideshowManager.initSlider(sliderId) }}
```

**Parameters:**
- `sliderId` (string, required) - The unique slider ID
- `overrides` (array, optional) - Override/extend config at runtime
- `debug` (bool, optional) - Enable console logging

**Returns:** Empty markup (JS is registered with Craft's view)

#### `getVisibilityClasses(visibility)`

Gets CSS classes for navigation/pagination visibility:

```twig
{{ craft.slideshowManager.getVisibilityClasses('hide-mobile') }}
```

**Parameters:**
- `visibility` (string) - Visibility option (default, hide-mobile, hide-desktop, mobile-only, desktop-only)

**Returns:** CSS classes string

## CSS Custom Properties

Slideshow Manager supports full Swiper styling customization via CSS custom properties. These can be configured globally in settings or per-slideshow via the `buildCssVars()` method.

### Available CSS Variables

#### Theme
- `themeColor` - Main theme color

#### Navigation
- `navigationSize` - Navigation button size
- `navigationTopOffset` - Top offset for navigation
- `navigationSidesOffset` - Side offset for navigation
- `navigationColor` - Navigation icon color
- `navigationInactiveColor` - Inactive navigation color
- `navigationBg` - Navigation background
- `navigationBgHover` - Navigation background on hover
- `navigationPadding` - Navigation padding
- `navigationBorderColor` - Navigation border color
- `navigationBorderColorHover` - Navigation border color on hover
- `navigationShadow` - Navigation shadow
- `navigationShadowHover` - Navigation shadow on hover

#### Pagination
- `paginationColor` - Active pagination color
- `paginationBulletSize` - Bullet size
- `paginationBulletWidth` - Bullet width
- `paginationBulletHeight` - Bullet height
- `paginationBulletInactiveColor` - Inactive bullet color
- `paginationBulletInactiveOpacity` - Inactive bullet opacity
- `paginationBulletOpacity` - Active bullet opacity
- `paginationBulletHorizontalGap` - Horizontal gap between bullets
- `paginationBulletVerticalGap` - Vertical gap between bullets
- `paginationFractionColor` - Fraction text color
- `paginationProgressbarBgColor` - Progressbar background
- `paginationProgressbarSize` - Progressbar size
- `paginationLeft` - Left position
- `paginationRight` - Right position
- `paginationTop` - Top position
- `paginationBottom` - Bottom position

#### Scrollbar
- `scrollbarBorderRadius` - Scrollbar border radius
- `scrollbarTop` - Top position
- `scrollbarBottom` - Bottom position
- `scrollbarLeft` - Left position
- `scrollbarRight` - Right position
- `scrollbarSidesOffset` - Side offset
- `scrollbarBgColor` - Background color
- `scrollbarDragBgColor` - Drag handle background
- `scrollbarSize` - Scrollbar size

#### Other
- `thumbActiveColor` - Active thumbnail color
- `slideBgColor` - Slide background color

### Usage Pattern

All CSS variables use a fallback pattern for easy customization:

```css
--swiper-theme-color: var(--_swiper-theme-color, #007bff);
```

This allows you to override in your own CSS:

```css
.swiper {
    --_swiper-theme-color: var(--color-primary);
    --_swiper-navigation-size: 60px;
    --_swiper-pagination-color: var(--color-accent);
}
```

## Swiper Configuration

The plugin supports all Swiper.js configuration options. Configuration can be set:

1. **Globally** - In plugin settings or config file
2. **Per-Entry** - Using the Slideshow Config field
3. **At Runtime** - Via `initSlider()` overrides parameter

See [Swiper API Documentation](https://swiperjs.com/swiper-api) for all available options.

## Examples

For comprehensive examples covering all features, see **[docs/examples.md](docs/examples.md)**.

Includes examples for:
- Basic slideshow setup
- Custom styling with CSS variables
- Runtime config overrides and debug mode
- Grid mode and different effects (fade, cube, coverflow, cards)
- Responsive visibility controls
- Autoplay configurations
- Responsive breakpoints
- Multiple sliders on one page
- Custom Swiper installation (npm/yarn/pnpm)
- Programmatic control and accessing Swiper instances
- Advanced techniques like thumbnail navigation

## Logging

Slideshow Manager uses the [LindemannRock Logging Library](https://github.com/LindemannRock/craft-logging-library) for centralized, structured logging across all LindemannRock plugins.

### Log Levels
- **Error**: Critical errors only
- **Warning**: Errors and warnings
- **Info**: General information
- **Debug**: Detailed debugging (includes performance metrics, requires devMode)

### Configuration
```php
// config/slideshow-manager.php
return [
    'logLevel' => 'info', // error, warning, info, or debug
];
```

**Note:** Debug level requires Craft's `devMode` to be enabled. If set to debug with devMode disabled, it automatically falls back to info level.

### Log Files
- **Location**: `storage/logs/slideshow-manager-YYYY-MM-DD.log`
- **Retention**: 30 days (automatic cleanup via Logging Library)
- **Format**: Structured JSON logs with context data
- **Web Interface**: View and filter logs in CP at Slideshow Manager → Logs

### What's Logged
- **Error**: Configuration errors, field save failures, asset loading errors
- **Warning**: Invalid configurations, missing dependencies, slow operations (>1s)
- **Info**: Configuration changes, field saves, settings updates
- **Debug**: Detailed configuration processing, Swiper initialization, cache operations, performance timing

### Log Management
Access logs through the Control Panel:
1. Navigate to Slideshow Manager → Logs
2. Filter by date, level, or search terms
3. Download log files for external analysis
4. View file sizes and entry counts
5. Auto-cleanup after 30 days (configurable via Logging Library)

**Requires:** `lindemannrock/logginglibrary` plugin (installed automatically as dependency)

## Troubleshooting

### Slideshow Not Initializing

**Check browser console:**
- Look for JavaScript errors
- Verify Swiper.js is loaded
- Check `data-swiper-config` attribute exists

**Verify auto-loading is enabled:**
- Go to **Settings → Slideshow Manager → General**
- Ensure "Auto Load Swiper JS" is enabled

**Clear caches:**
```bash
./craft clear-caches/all
```

### Navigation/Pagination Not Showing

**Check config:**
- Verify `navigation` or `paginationEnabled` is true in your config
- Check visibility settings aren't hiding elements

**Check element classes:**
- Navigation: `.swiper-button-prev-{{ sliderId }}` and `.swiper-button-next-{{ sliderId }}`
- Pagination: `.swiper-pagination-{{ sliderId }}`

### CSS Variables Not Working

**Verify auto-loading:**
- "Auto Load Swiper CSS" must be enabled in settings

**Check config:**
- CSS vars must be configured in settings or config file
- Use `buildCssVars()` in template

**Browser support:**
- CSS custom properties require modern browsers
- IE11 not supported

### Field Not Saving

**Check permissions:**
- Verify user has permission to edit entries

**Clear caches:**
- Clear Craft caches and reload the entry
- Check browser console for errors

**Verify Live Preview:**
- Field works differently in Live Preview mode
- Save the entry to persist changes

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-slideshow-manager](https://github.com/LindemannRock/craft-slideshow-manager)
- **Issues**: [https://github.com/LindemannRock/craft-slideshow-manager/issues](https://github.com/LindemannRock/craft-slideshow-manager/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
