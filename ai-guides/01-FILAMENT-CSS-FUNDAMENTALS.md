# Filament CSS Fundamentals for AI Models

**Part 1 of 5: Understanding the Foundation**

**Audience:** AI Models learning to style Filament applications  
**Focus:** Core concepts, architecture, and mental models  
**Prerequisites:** Basic CSS knowledge  
**Last Updated:** October 14, 2025

---

## Table of Contents

1. [What is Filament?](#what-is-filament)
2. [The CSS Architecture](#the-css-architecture)
3. [Class Naming System](#class-naming-system)
4. [Component Hierarchy](#component-hierarchy)
5. [The Styling Layer Cake](#the-styling-layer-cake)
6. [CSS Cascade in Filament](#css-cascade-in-filament)
7. [Dark Mode Architecture](#dark-mode-architecture)
8. [OKLCH Color Space](#oklch-color-space)
9. [CSS Variables System](#css-variables-system)
10. [Mental Models for Styling](#mental-models-for-styling)

---

## What is Filament?

### Server-Driven UI Framework

Filament is a **Server-Driven UI (SDUI)** framework for Laravel. This means:

1. **PHP defines the UI** - Not HTML/Blade directly
2. **Components render dynamically** - Based on PHP configuration
3. **CSS targets rendered output** - Not the PHP classes

```php
// PHP Configuration (what you write)
Forms\Components\TextInput::make('name')
    ->label('Full Name')
    ->required();

// Rendered HTML (what you style)
<div class="fi-fo-field fi-fo-text-input">
    <label class="fi-fo-field-label">Full Name</label>
    <input class="fi-input" type="text" />
</div>
```

### Key Insight

**You're styling the OUTPUT, not the INPUT.**

The PHP classes define behavior and structure. The CSS classes (fi-*) are what you actually style.

---

## The CSS Architecture

### Three-Layer System

```
┌─────────────────────────────────────┐
│  Layer 3: Your Custom Theme         │  ← Your creative space
│  (resources/css/filament/*.css)     │
├─────────────────────────────────────┤
│  Layer 2: Filament Core CSS         │  ← Framework defaults
│  (vendor/filament/*/resources/css)  │
├─────────────────────────────────────┤
│  Layer 1: Tailwind CSS              │  ← Utility foundation
│  (base, components, utilities)      │
└─────────────────────────────────────┘
```

### How the Layers Interact

1. **Tailwind** provides utility classes (`flex`, `text-sm`, `bg-blue-500`)
2. **Filament Core** adds component classes (`fi-btn`, `fi-badge`)
3. **Your Theme** overrides and extends both

### Loading Order (Specificity)

```css
/* 1. Tailwind base (lowest specificity) */
@import "tailwindcss";

/* 2. Filament core (medium specificity) */
/* Loaded automatically from vendor */

/* 3. Your custom theme (highest specificity) */
/* In resources/css/filament/[panel-name]/theme.css */
```

---

## Class Naming System

### The fi-* Prefix Convention

**ALL** Filament classes start with `fi-`:

```
fi-btn          (button)
fi-badge        (badge)
fi-section      (section container)
fi-ta-row       (table row)
fi-fo-field     (form field)
```

### Prefix Taxonomy

| Prefix | Category | Examples |
|--------|----------|----------|
| `fi-` | Global/Base | `fi-btn`, `fi-badge`, `fi-panel` |
| `fi-ac-` | Actions | `fi-ac-action`, `fi-ac-modal` |
| `fi-fo-` | Forms | `fi-fo-field`, `fi-fo-builder` |
| `fi-in-` | Infolists | `fi-in-entry`, `fi-in-section` |
| `fi-no-` | Notifications | `fi-no-notification` |
| `fi-pa-` | Pages | `fi-page`, `fi-page-header` |
| `fi-sc-` | Schemas | `fi-sc-section`, `fi-sc-wizard` |
| `fi-ta-` | Tables | `fi-ta-table`, `fi-ta-cell` |
| `fi-wi-` | Widgets | `fi-wi-chart`, `fi-wi-stats` |

### Suffix Patterns

#### Element Suffixes

```
-ctn        Container      .fi-modal-ctn
-wrapper    Wrapper        .fi-ta-wrapper
-header     Header         .fi-section-header
-content    Content        .fi-section-content
-footer     Footer         .fi-section-footer
-item       Item           .fi-dropdown-item
-btn        Button         .fi-sidebar-item-btn
-icon       Icon           .fi-badge-icon
-label      Label          .fi-fo-field-label
-window     Window         .fi-modal-window
```

#### State Suffixes

```
-active     Active state   .fi-sidebar-item-active
-disabled   Disabled       .fi-btn-disabled
-selected   Selected       .fi-ta-row-selected
-open       Open/expanded  .fi-dropdown-open
-closed     Closed         .fi-dropdown-closed
```

### Reading Class Names

**Pattern:** `fi-[package]-[component]-[element]-[state]`

**Examples:**

```css
fi-ta-row-selected
│  │  │   └─ state
│  │  └───── component
│  └──────── package (tables)
└─────────── filament prefix

fi-fo-field-wrapper-disabled
│  │  │     │       └─ state
│  │  │     └───────── element
│  │  └─────────────── component
│  └────────────────── package (forms)
└───────────────────── filament prefix
```

---

## Component Hierarchy

### Understanding Parent-Child Relationships

Filament uses **nested component structures**. Understanding the hierarchy is crucial for effective styling.

### Example: Button Hierarchy

```html
<button class="fi-btn fi-color-primary fi-size-md">
    <span class="fi-btn-label">
        Click Me
    </span>
</button>
```

**Hierarchy:**
```
fi-btn (parent)
└── fi-btn-label (child)
```

**Styling implications:**

```css
/* Style the button container */
.fi-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
}

/* Style the text inside */
.fi-btn-label {
    font-weight: 600;
}
```

### Example: Section Hierarchy

```html
<section class="fi-section">
    <header class="fi-section-header">
        <h3 class="fi-section-header-heading">Title</h3>
        <p class="fi-section-header-description">Description</p>
    </header>
    <div class="fi-section-content">
        <!-- Content here -->
    </div>
    <footer class="fi-section-footer">
        <!-- Footer here -->
    </footer>
</section>
```

**Hierarchy:**
```
fi-section (parent)
├── fi-section-header
│   ├── fi-section-header-heading
│   └── fi-section-header-description
├── fi-section-content
└── fi-section-footer
```

### Example: Table Hierarchy

```html
<div class="fi-ta-ctn">
    <div class="fi-ta-wrapper">
        <table class="fi-ta-table">
            <thead class="fi-ta-header">
                <tr class="fi-ta-header-row">
                    <th class="fi-ta-header-cell">Name</th>
                </tr>
            </thead>
            <tbody class="fi-ta-body">
                <tr class="fi-ta-row">
                    <td class="fi-ta-cell">
                        <div class="fi-ta-text">John Doe</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

**Hierarchy:**
```
fi-ta-ctn (outer container)
└── fi-ta-wrapper
    └── fi-ta-table
        ├── fi-ta-header
        │   └── fi-ta-header-row
        │       └── fi-ta-header-cell
        └── fi-ta-body
            └── fi-ta-row
                └── fi-ta-cell
                    └── fi-ta-text
```

### Hierarchy Styling Strategy

**1. Style the container first:**
```css
.fi-section {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
```

**2. Then style direct children:**
```css
.fi-section-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}
```

**3. Finally, style nested elements:**
```css
.fi-section-header-heading {
    font-size: 1.125rem;
    font-weight: 700;
}
```

---

## The Styling Layer Cake

### Understanding CSS Specificity Layers

```
Priority (High → Low)
├── Inline styles (avoid!)
├── IDs (avoid in components!)
├── Classes, attributes, pseudo-classes
├── Elements
└── Universal selector (*)
```

### Filament Specificity Strategy

**Level 1: Single class (preferred)**
```css
.fi-btn {
    /* Base button styles */
}
```

**Level 2: Combined classes (for variants)**
```css
.fi-btn.fi-color-primary {
    /* Primary color variant */
}

.fi-btn.fi-size-lg {
    /* Large size variant */
}
```

**Level 3: Direct child (for structure)**
```css
.fi-section > .fi-section-header {
    /* Header within section */
}
```

**Level 4: Descendant (use sparingly)**
```css
.fi-ta-row .fi-badge {
    /* Badge inside table row */
}
```

### The !important Problem

```css
/* ❌ AVOID */
.fi-btn {
    color: red !important;
}

/* ✅ BETTER - Increase specificity */
.fi-btn.fi-color-custom {
    color: red;
}

/* ✅ BEST - Use CSS variables */
:root {
    --fi-btn-color: red;
}

.fi-btn {
    color: var(--fi-btn-color);
}
```

---

## CSS Cascade in Filament

### How Styles Cascade

```css
/* 1. Filament default (vendor/filament) */
.fi-btn {
    padding: 0.5rem 1rem;
    background: #3b82f6;
}

/* 2. Your theme (resources/css) - WINS! */
.fi-btn {
    padding: 0.75rem 1.5rem;  /* Overrides */
    background: #8b5cf6;      /* Overrides */
}
```

### Cascade with Variants

```css
/* Base class */
.fi-btn { }

/* Color variant */
.fi-btn.fi-color-primary { }

/* Size variant */
.fi-btn.fi-size-lg { }

/* Combined variants (highest specificity) */
.fi-btn.fi-color-primary.fi-size-lg { }
```

### Understanding Cascade Order

**Same specificity? Last one wins:**

```css
/* File loaded first */
.fi-btn { color: blue; }

/* File loaded second - WINS */
.fi-btn { color: red; }
```

**Different specificity? Higher wins:**

```css
/* Lower specificity */
.fi-btn { color: blue; }

/* Higher specificity - WINS */
.fi-btn.fi-color-primary { color: red; }
```

---

## Dark Mode Architecture

### How Filament Implements Dark Mode

Filament uses **class-based dark mode**:

```html
<!-- Light mode -->
<html class="fi-light">

<!-- Dark mode -->
<html class="fi-dark">
```

### Dark Mode Selectors

**Method 1: Pseudo-class (preferred)**
```css
.fi-btn {
    background: white;
    color: black;
}

.dark\:fi-btn,
.fi-dark .fi-btn {
    background: #1f2937;
    color: white;
}
```

**Method 2: Media query (responsive)**
```css
.fi-btn {
    background: white;
    color: black;
}

@media (prefers-color-scheme: dark) {
    .fi-btn {
        background: #1f2937;
        color: white;
    }
}
```

### Dark Mode Variables

```css
:root {
    --bg-primary: white;
    --text-primary: black;
}

.fi-dark {
    --bg-primary: #1f2937;
    --text-primary: white;
}

.fi-component {
    background: var(--bg-primary);
    color: var(--text-primary);
}
```

### Dark Mode Color Strategy

**Don't just invert colors!**

```css
/* ❌ BAD - Just inverted */
.fi-btn {
    background: white;
    color: black;
}

.fi-dark .fi-btn {
    background: black;  /* Too harsh! */
    color: white;
}

/* ✅ GOOD - Adjusted for readability */
.fi-btn {
    background: white;
    color: #1f2937;
}

.fi-dark .fi-btn {
    background: #374151;  /* Softer gray */
    color: #f9fafb;
}
```

---

## OKLCH Color Space

### What is OKLCH?

**OKLCH** = Oklab Lightness Chroma Hue

A perceptually uniform color space introduced in CSS Color Level 4.

### Why Filament Uses OKLCH

1. **Perceptually uniform** - Equal numeric changes = equal perceived changes
2. **Wider gamut** - More vibrant colors
3. **Better interpolation** - Smooth gradients
4. **Easier manipulation** - Adjust lightness independently

### OKLCH Syntax

```css
color: oklch(L C H / A);
```

- **L** = Lightness (0 to 1)
- **C** = Chroma (0 to 0.4, typically)
- **H** = Hue (0 to 360 degrees)
- **A** = Alpha (0 to 1, optional)

### Examples

```css
/* Pure white */
color: oklch(1 0 0);

/* Pure black */
color: oklch(0 0 0);

/* Blue (similar to #3b82f6) */
color: oklch(0.6 0.2 250);

/* Green (similar to #10b981) */
color: oklch(0.7 0.18 150);

/* Red (similar to #ef4444) */
color: oklch(0.6 0.25 25);

/* With alpha */
color: oklch(0.6 0.2 250 / 0.5);
```

### Creating Color Scales

**Traditional RGB (uneven perception):**
```css
--blue-300: #93c5fd;  /* Lightness: ~70% */
--blue-500: #3b82f6;  /* Lightness: ~50% */
--blue-700: #1d4ed8;  /* Lightness: ~35% (not 30%!) */
```

**OKLCH (even perception):**
```css
--blue-300: oklch(0.7 0.2 250);  /* Lightness: 70% */
--blue-500: oklch(0.5 0.2 250);  /* Lightness: 50% */
--blue-700: oklch(0.3 0.2 250);  /* Lightness: 30% (exactly!) */
```

### Adjusting OKLCH Colors

**Lighten/darken:**
```css
:root {
    --base-color: oklch(0.5 0.2 250);
}

.lighter {
    color: oklch(from var(--base-color) calc(l + 0.2) c h);
}

.darker {
    color: oklch(from var(--base-color) calc(l - 0.2) c h);
}
```

**More/less saturated:**
```css
.vibrant {
    color: oklch(from var(--base-color) l calc(c + 0.1) h);
}

.muted {
    color: oklch(from var(--base-color) l calc(c - 0.1) h);
}
```

**Rotate hue:**
```css
.rotated {
    color: oklch(from var(--base-color) l c calc(h + 60));
}
```

---

## CSS Variables System

### Filament's Variable Structure

**Global variables:**
```css
:root {
    --primary: 59 130 246;      /* RGB values */
    --success: 16 185 129;
    --danger: 239 68 68;
    --warning: 245 158 11;
}
```

**Component-specific variables:**
```css
.fi-btn {
    --btn-bg: var(--primary);
    --btn-text: white;
    --btn-padding-x: 1rem;
    --btn-padding-y: 0.5rem;
    --btn-radius: 0.375rem;
}
```

### Variable Naming Convention

**Pattern:** `--[component]-[property]-[modifier]`

```css
--btn-bg-primary
│   │  │  └─ modifier
│   │  └──── property
│   └─────── component
└────────── prefix

--section-border-color-active
│       │      │     └─ state
│       │      └─────── property detail
│       └────────────── property
└────────────────────── component
```

### Variable Scoping

**Global scope:**
```css
:root {
    --spacing-unit: 0.25rem;
}

.fi-component {
    padding: calc(var(--spacing-unit) * 4); /* 1rem */
}
```

**Component scope:**
```css
.fi-btn {
    --btn-padding: 1rem;
    padding: var(--btn-padding);
}

.fi-btn.fi-size-sm {
    --btn-padding: 0.5rem;  /* Overrides for this variant */
}
```

### CSS Variables for Theming

**Create themeable components:**

```css
.fi-card {
    background: var(--card-bg, white);
    border: 1px solid var(--card-border, #e5e7eb);
    border-radius: var(--card-radius, 0.5rem);
    padding: var(--card-padding, 1.5rem);
    box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,0.1));
}

/* Theme 1: Default (uses fallbacks above) */

/* Theme 2: Custom values */
:root {
    --card-bg: #f9fafb;
    --card-border: #d1d5db;
    --card-radius: 1rem;
    --card-padding: 2rem;
    --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
```

---

## Mental Models for Styling

### Model 1: The Box Model Thinking

Every Filament component is a box:

```
┌─────────────────────────────┐
│         Margin              │
│  ┌──────────────────────┐   │
│  │      Border          │   │
│  │  ┌───────────────┐   │   │
│  │  │   Padding     │   │   │
│  │  │  ┌────────┐   │   │   │
│  │  │  │Content │   │   │   │
│  │  │  └────────┘   │   │   │
│  │  └───────────────┘   │   │
│  └──────────────────────┘   │
└─────────────────────────────┘
```

**Apply this to every component:**

```css
.fi-section {
    /* Container box */
    margin: 1rem;          /* Space outside */
    border: 1px solid #e5e7eb;  /* Edge */
    padding: 1.5rem;       /* Space inside */
    border-radius: 0.5rem; /* Rounded corners */
}

.fi-section-content {
    /* Content box inside */
    padding: 1rem;         /* Space inside content */
}
```

### Model 2: The State Machine

Components have states:

```
       ┌─────────────┐
       │   Default   │
       └──────┬──────┘
              │
        ┌─────┴─────┐
        │           │
    ┌───▼───┐   ┌───▼───┐
    │ Hover │   │Active │
    └───────┘   └───┬───┘
                    │
                ┌───▼────┐
                │Disabled│
                └────────┘
```

**Style each state:**

```css
/* Default */
.fi-btn {
    background: #3b82f6;
    color: white;
}

/* Hover */
.fi-btn:hover {
    background: #2563eb;
}

/* Active */
.fi-btn.fi-active,
.fi-btn:active {
    background: #1d4ed8;
}

/* Disabled */
.fi-btn.fi-disabled,
.fi-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}
```

### Model 3: The Composition Pattern

Build complex styles from simple pieces:

```css
/* Base */
.fi-btn { }

/* + Color */
.fi-btn.fi-color-primary { }

/* + Size */
.fi-btn.fi-size-lg { }

/* + State */
.fi-btn:hover { }

/* Result: composed styles */
.fi-btn.fi-color-primary.fi-size-lg:hover {
    /* All combined! */
}
```

### Model 4: The Inheritance Chain

CSS properties inherit:

```
.fi-panel
└── color: #1f2937          ← Inherited by children
    background: white       ← Not inherited

    .fi-section
    └── (inherits color)
        border: 1px solid   ← Not inherited
        
        .fi-section-header
        └── (inherits color)
            
            .fi-section-header-heading
            └── (inherits color)
```

**Inherited properties:**
- color
- font-family
- font-size
- font-weight
- line-height
- text-align

**Not inherited:**
- background
- border
- margin
- padding
- width
- height

---

## Key Takeaways

### 1. Filament's CSS is Systematic

- Predictable class names (`fi-*`)
- Consistent hierarchy
- Clear component boundaries

### 2. Three Layers Matter

- Tailwind (utilities)
- Filament (components)
- Your theme (creativity)

### 3. OKLCH is Powerful

- Use it for color consistency
- Create better gradients
- Easier color manipulation

### 4. Variables Enable Theming

- Define once, use everywhere
- Easy to customize
- Scope appropriately

### 5. Think in Components

- Understand hierarchy
- Style parent-child relationships
- Use appropriate specificity

---

## Next Steps

Continue to:
- **Part 2:** Creative Styling Techniques
- **Part 3:** Component-Specific Patterns
- **Part 4:** Advanced CSS Features
- **Part 5:** Performance & Best Practices

---

**End of Part 1: Fundamentals**

*Master these concepts before moving to creative techniques.*
