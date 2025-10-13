# Creative Styling Techniques for Filament

**Part 2 of 5: Beyond the Basics**

**Audience:** AI Models creating unique Filament designs  
**Focus:** Creative techniques, visual effects, and advanced CSS  
**Prerequisites:** Part 1 - Fundamentals  
**Last Updated:** October 14, 2025

---

## Table of Contents

1. [Visual Design Principles](#visual-design-principles)
2. [Modern CSS Features](#modern-css-features)
3. [Color Psychology & Application](#color-psychology--application)
4. [Typography Techniques](#typography-techniques)
5. [Spacing & Rhythm](#spacing--rhythm)
6. [Visual Hierarchy](#visual-hierarchy)
7. [Creative Effects Library](#creative-effects-library)
8. [Animation Principles](#animation-principles)
9. [Composition Techniques](#composition-techniques)
10. [Design Systems Thinking](#design-systems-thinking)

---

## Visual Design Principles

### Gestalt Principles Applied to Filament

#### 1. Proximity - Group Related Elements

```css
/* Related form fields grouped visually */
.fi-fo-field-group {
    display: flex;
    gap: 1rem;  /* Close proximity = related */
    margin-bottom: 2rem;  /* Larger gap = new group */
}
```

**Visual example:**
```
[First Name] [Last Name]        ← Close (same group)

                                ← Space

[Email]                         ← New group
```

#### 2. Similarity - Use Consistent Patterns

```css
/* All primary actions look similar */
.fi-btn.fi-color-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 0.5rem;
    font-weight: 600;
}

/* All secondary actions look similar */
.fi-btn.fi-color-secondary {
    background: transparent;
    border: 2px solid #d1d5db;
    border-radius: 0.5rem;
    font-weight: 600;
}
```

#### 3. Continuity - Create Visual Flow

```css
/* Breadcrumb creates visual path */
.fi-breadcrumbs {
    display: flex;
    align-items: center;
}

.fi-breadcrumbs-item::after {
    content: '→';
    margin: 0 0.5rem;
    color: #9ca3af;
}

.fi-breadcrumbs-item:last-child::after {
    display: none;
}
```

#### 4. Closure - Suggest Boundaries

```css
/* Subtle border suggests containment */
.fi-section {
    border: 1px solid rgba(0, 0, 0, 0.05);
    /* User's brain "closes" the shape */
}
```

#### 5. Figure-Ground - Create Depth

```css
/* Modal creates clear figure (content) vs ground (overlay) */
.fi-modal-overlay {
    background: rgba(0, 0, 0, 0.5);  /* Ground */
    backdrop-filter: blur(4px);
}

.fi-modal-window {
    background: white;  /* Figure */
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    z-index: 50;
}
```

---

## Modern CSS Features

### 1. CSS Grid for Layouts

**Dashboard Grid:**
```css
.fi-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

/* Span multiple columns for featured widget */
.fi-wi-featured {
    grid-column: span 2;
}

/* Span multiple rows for tall chart */
.fi-wi-chart-large {
    grid-row: span 2;
}
```

**Responsive Grid Without Media Queries:**
```css
.fi-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}
/* Automatically adjusts columns based on available space! */
```

### 2. CSS Subgrid (Modern Browsers)

```css
.fi-form-section {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1rem;
}

.fi-fo-field {
    display: grid;
    grid-template-columns: subgrid;
    grid-column: span 2;
}
/* Labels and inputs align across multiple fields! */
```

### 3. Container Queries (Revolutionary)

```css
.fi-card {
    container-type: inline-size;
    container-name: card;
}

/* Style based on container width, not viewport! */
@container card (min-width: 400px) {
    .fi-card-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
}
```

### 4. Cascade Layers (Organization)

```css
@layer base, components, utilities;

@layer base {
    .fi-btn {
        padding: 0.5rem 1rem;
    }
}

@layer components {
    .fi-btn-primary {
        background: blue;
    }
}

@layer utilities {
    .fi-btn-large {
        padding: 1rem 2rem;
    }
}
/* Layers enforce predictable cascade regardless of order! */
```

### 5. Color-Mix Function

```css
.fi-badge {
    /* Mix primary color with white for soft background */
    background: color-mix(in oklch, var(--primary) 20%, white);
    
    /* Border is more saturated */
    border: 1px solid color-mix(in oklch, var(--primary) 60%, white);
}
```

### 6. CSS Nesting (Native)

```css
.fi-card {
    background: white;
    border-radius: 0.5rem;
    
    .fi-card-header {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        
        .fi-card-title {
            font-weight: 700;
        }
    }
    
    &:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
}
```

### 7. Scroll-Driven Animations

```css
.fi-page-header {
    animation: fade-in linear forwards;
    animation-timeline: scroll();
    animation-range: 0 200px;
}

@keyframes fade-in {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0.5;
        transform: scale(0.9);
    }
}
/* Header fades/scales as you scroll! */
```

### 8. Logical Properties (i18n-friendly)

```css
.fi-card {
    /* Instead of margin-left */
    margin-inline-start: 1rem;
    
    /* Instead of padding: 1rem 2rem */
    padding-block: 1rem;
    padding-inline: 2rem;
    
    /* Instead of border-left */
    border-inline-start: 4px solid blue;
}
/* Automatically flips for RTL languages! */
```

---

## Color Psychology & Application

### Color Meanings & Usage

#### Blue (Trust, Stability)
```css
.fi-btn-primary {
    background: oklch(0.55 0.2 250);  /* Blue */
    /* Use for: Primary actions, links, default state */
}
```

#### Green (Success, Growth)
```css
.fi-badge-success {
    background: oklch(0.7 0.15 150 / 0.15);
    color: oklch(0.4 0.18 150);
    /* Use for: Success messages, positive indicators */
}
```

#### Red (Danger, Urgency)
```css
.fi-btn-danger {
    background: oklch(0.55 0.22 25);
    /* Use for: Delete actions, errors, critical warnings */
}
```

#### Yellow/Orange (Warning, Attention)
```css
.fi-badge-warning {
    background: oklch(0.75 0.15 75 / 0.15);
    color: oklch(0.45 0.18 75);
    /* Use for: Warnings, pending states, caution */
}
```

#### Purple (Premium, Creative)
```css
.fi-badge-featured {
    background: linear-gradient(135deg, 
        oklch(0.6 0.2 290), 
        oklch(0.6 0.2 320)
    );
    /* Use for: Premium features, special status */
}
```

#### Gray (Neutral, Disabled)
```css
.fi-btn-disabled {
    background: oklch(0.7 0.01 0);
    color: oklch(0.5 0.01 0);
    /* Use for: Disabled states, secondary info */
}
```

### Color Harmony Techniques

#### Complementary (High Contrast)
```css
:root {
    --color-primary: oklch(0.55 0.2 250);    /* Blue */
    --color-accent: oklch(0.65 0.18 30);     /* Orange (opposite) */
}

.fi-highlight {
    background: var(--color-primary);
    border-left: 4px solid var(--color-accent);
}
```

#### Analogous (Harmonious)
```css
:root {
    --color-base: oklch(0.6 0.2 250);       /* Blue */
    --color-lighter: oklch(0.6 0.2 270);    /* Blue-Purple */
    --color-darker: oklch(0.6 0.2 230);     /* Blue-Cyan */
}

.fi-gradient {
    background: linear-gradient(135deg,
        var(--color-darker),
        var(--color-base),
        var(--color-lighter)
    );
}
```

#### Triadic (Balanced Variety)
```css
:root {
    --color-1: oklch(0.6 0.2 0);      /* Red */
    --color-2: oklch(0.6 0.2 120);    /* Green */
    --color-3: oklch(0.6 0.2 240);    /* Blue */
}

.fi-chart-1 { fill: var(--color-1); }
.fi-chart-2 { fill: var(--color-2); }
.fi-chart-3 { fill: var(--color-3); }
```

#### Monochromatic (Subtle Elegance)
```css
:root {
    --color-base: oklch(0.5 0.2 250);
    --color-light-1: oklch(0.6 0.2 250);
    --color-light-2: oklch(0.7 0.2 250);
    --color-dark-1: oklch(0.4 0.2 250);
    --color-dark-2: oklch(0.3 0.2 250);
}

.fi-section-1 { background: var(--color-light-2); }
.fi-section-2 { background: var(--color-light-1); }
.fi-section-3 { background: var(--color-base); }
```

### Color Accessibility (WCAG)

**Contrast ratios:**
- Normal text: 4.5:1 minimum
- Large text (18pt+): 3:1 minimum
- UI components: 3:1 minimum

```css
/* ❌ BAD - Low contrast */
.fi-text-bad {
    color: oklch(0.7 0.15 250);      /* Light blue */
    background: oklch(0.85 0.05 0);  /* Light gray */
    /* Contrast: ~2.5:1 - FAILS */
}

/* ✅ GOOD - High contrast */
.fi-text-good {
    color: oklch(0.3 0.15 250);      /* Dark blue */
    background: oklch(0.95 0.01 0);  /* Nearly white */
    /* Contrast: ~8:1 - PASSES AAA */
}
```

**Programmatic check:**
```css
.fi-btn {
    --bg: oklch(0.55 0.2 250);
    --text: oklch(0.98 0.01 0);  /* Always light on dark */
    
    background: var(--bg);
    color: var(--text);
}
```

---

## Typography Techniques

### Type Scale (Modular Scale)

**Ratio: 1.250 (Major Third)**
```css
:root {
    --text-xs: 0.64rem;      /* 10.24px */
    --text-sm: 0.8rem;       /* 12.8px */
    --text-base: 1rem;       /* 16px */
    --text-lg: 1.25rem;      /* 20px */
    --text-xl: 1.563rem;     /* 25px */
    --text-2xl: 1.953rem;    /* 31.25px */
    --text-3xl: 2.441rem;    /* 39px */
    --text-4xl: 3.052rem;    /* 48.8px */
}
```

**Application:**
```css
.fi-heading-1 { font-size: var(--text-4xl); }
.fi-heading-2 { font-size: var(--text-3xl); }
.fi-heading-3 { font-size: var(--text-2xl); }
.fi-heading-4 { font-size: var(--text-xl); }
.fi-body { font-size: var(--text-base); }
.fi-caption { font-size: var(--text-sm); }
```

### Font Pairing

**System Font Stack (Fast, Native):**
```css
:root {
    --font-sans: system-ui, -apple-system, 'Segoe UI', Roboto, 
                 'Helvetica Neue', Arial, sans-serif;
    --font-mono: ui-monospace, 'SF Mono', 'Cascadia Code', 
                 'Source Code Pro', monospace;
}

.fi-body { font-family: var(--font-sans); }
.fi-code { font-family: var(--font-mono); }
```

**Custom Font Pairing:**
```css
/* Heading: Display font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap');

/* Body: Readable font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

:root {
    --font-heading: 'Poppins', sans-serif;
    --font-body: 'Inter', sans-serif;
}

.fi-heading { font-family: var(--font-heading); }
.fi-body { font-family: var(--font-body); }
```

### Line Height (Leading)

**Golden ratio: 1.5x font size**
```css
:root {
    --leading-tight: 1.25;
    --leading-normal: 1.5;
    --leading-relaxed: 1.75;
}

/* Headings: tight */
.fi-heading {
    line-height: var(--leading-tight);
}

/* Body: normal */
.fi-body {
    line-height: var(--leading-normal);
}

/* Long-form: relaxed */
.fi-article {
    line-height: var(--leading-relaxed);
}
```

### Letter Spacing (Tracking)

```css
.fi-uppercase {
    text-transform: uppercase;
    letter-spacing: 0.05em;  /* Wider for uppercase */
}

.fi-heading-large {
    letter-spacing: -0.02em;  /* Tighter for large text */
}

.fi-caption {
    letter-spacing: 0.025em;  /* Slightly wider for small text */
}
```

### Text Effects

**Gradient Text:**
```css
.fi-gradient-text {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
}

@keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}
```

**Text Shadow (Depth):**
```css
.fi-text-elevated {
    text-shadow: 
        0 1px 2px rgba(0, 0, 0, 0.1),
        0 2px 4px rgba(0, 0, 0, 0.06);
}

.fi-text-embossed {
    color: rgba(255, 255, 255, 0.8);
    text-shadow: 0 1px 0 rgba(0, 0, 0, 0.5);
}
```

**Text Truncation:**
```css
/* Single line */
.fi-text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Multiple lines */
.fi-text-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
```

---

## Spacing & Rhythm

### The 8pt Grid System

**Base unit: 8px (0.5rem)**
```css
:root {
    --space-1: 0.5rem;   /* 8px */
    --space-2: 1rem;     /* 16px */
    --space-3: 1.5rem;   /* 24px */
    --space-4: 2rem;     /* 32px */
    --space-5: 2.5rem;   /* 40px */
    --space-6: 3rem;     /* 48px */
    --space-8: 4rem;     /* 64px */
    --space-10: 5rem;    /* 80px */
}
```

**Application:**
```css
.fi-section {
    padding: var(--space-4);  /* 32px all sides */
}

.fi-section-header {
    margin-bottom: var(--space-3);  /* 24px below */
}

.fi-card-grid {
    gap: var(--space-2);  /* 16px between cards */
}
```

### Vertical Rhythm

**Consistent spacing creates flow:**
```css
.fi-content > * + * {
    margin-top: var(--space-3);  /* 24px between all siblings */
}

/* Exceptions for tighter grouping */
.fi-form-group > * + * {
    margin-top: var(--space-2);  /* 16px between fields */
}
```

### Breathing Room (Padding)

```css
/* Compact: tight spaces */
.fi-compact {
    padding: var(--space-1) var(--space-2);  /* 8px 16px */
}

/* Regular: comfortable */
.fi-regular {
    padding: var(--space-2) var(--space-3);  /* 16px 24px */
}

/* Spacious: generous */
.fi-spacious {
    padding: var(--space-4) var(--space-6);  /* 32px 48px */
}
```

---

## Visual Hierarchy

### Size Hierarchy

```css
/* Establish importance through size */
.fi-heading-primary {
    font-size: 2.5rem;      /* Most important */
    font-weight: 700;
}

.fi-heading-secondary {
    font-size: 1.75rem;     /* Secondary importance */
    font-weight: 600;
}

.fi-heading-tertiary {
    font-size: 1.25rem;     /* Tertiary */
    font-weight: 600;
}

.fi-body {
    font-size: 1rem;        /* Base importance */
    font-weight: 400;
}

.fi-caption {
    font-size: 0.875rem;    /* Least important */
    font-weight: 400;
    opacity: 0.7;
}
```

### Color Hierarchy

```css
/* High emphasis: full opacity, strong color */
.fi-text-primary {
    color: oklch(0.2 0.05 0);
    opacity: 1;
}

/* Medium emphasis: reduced opacity */
.fi-text-secondary {
    color: oklch(0.2 0.05 0);
    opacity: 0.7;
}

/* Low emphasis: further reduced */
.fi-text-tertiary {
    color: oklch(0.2 0.05 0);
    opacity: 0.5;
}

/* Disabled: minimal */
.fi-text-disabled {
    color: oklch(0.2 0.05 0);
    opacity: 0.38;
}
```

### Weight Hierarchy

```css
.fi-weight-light { font-weight: 300; }
.fi-weight-normal { font-weight: 400; }
.fi-weight-medium { font-weight: 500; }
.fi-weight-semibold { font-weight: 600; }
.fi-weight-bold { font-weight: 700; }
```

### Z-Index Scale

```css
:root {
    --z-base: 0;
    --z-dropdown: 1000;
    --z-sticky: 1100;
    --z-fixed: 1200;
    --z-modal-backdrop: 1300;
    --z-modal: 1400;
    --z-popover: 1500;
    --z-tooltip: 1600;
}

.fi-dropdown { z-index: var(--z-dropdown); }
.fi-modal-backdrop { z-index: var(--z-modal-backdrop); }
.fi-modal { z-index: var(--z-modal); }
.fi-tooltip { z-index: var(--z-tooltip); }
```

---

## Creative Effects Library

### 1. Glassmorphism

```css
.glass-effect {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}
```

### 2. Neumorphism (Soft UI)

```css
.neumorphic {
    background: #e0e5ec;
    border-radius: 1rem;
    box-shadow: 
        9px 9px 16px rgba(163, 177, 198, 0.6),
        -9px -9px 16px rgba(255, 255, 255, 0.5);
}

.neumorphic-pressed {
    box-shadow: 
        inset 6px 6px 10px rgba(163, 177, 198, 0.5),
        inset -6px -6px 10px rgba(255, 255, 255, 0.5);
}
```

### 3. Gradient Borders

```css
.gradient-border {
    border: 2px solid transparent;
    background: 
        linear-gradient(white, white) padding-box,
        linear-gradient(135deg, #3b82f6, #8b5cf6) border-box;
    border-radius: 0.5rem;
}
```

### 4. Animated Gradients

```css
.animated-gradient {
    background: linear-gradient(
        -45deg,
        #ee7752, #e73c7e, #23a6d5, #23d5ab
    );
    background-size: 400% 400%;
    animation: gradient-animation 15s ease infinite;
}

@keyframes gradient-animation {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
```

### 5. Glow Effects

```css
.glow {
    box-shadow: 
        0 0 10px rgba(59, 130, 246, 0.5),
        0 0 20px rgba(59, 130, 246, 0.3),
        0 0 30px rgba(59, 130, 246, 0.2);
}

.glow-pulse {
    animation: glow-pulse 2s infinite;
}

@keyframes glow-pulse {
    0%, 100% {
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }
    50% {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.8);
    }
}
```

### 6. Particle Background

```css
.particle-bg {
    position: relative;
    overflow: hidden;
}

.particle-bg::before,
.particle-bg::after {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.3;
    animation: float 10s infinite;
}

.particle-bg::before {
    background: #3b82f6;
    top: -150px;
    left: -150px;
}

.particle-bg::after {
    background: #8b5cf6;
    bottom: -150px;
    right: -150px;
    animation-delay: -5s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0); }
    33% { transform: translate(30px, -50px); }
    66% { transform: translate(-20px, 20px); }
}
```

### 7. Parallax Depth

```css
.parallax-container {
    perspective: 1000px;
}

.parallax-layer-1 {
    transform: translateZ(-100px) scale(1.1);
}

.parallax-layer-2 {
    transform: translateZ(-50px) scale(1.05);
}

.parallax-layer-3 {
    transform: translateZ(0);
}
```

### 8. Shimmer Loading

```css
.shimmer {
    background: linear-gradient(
        90deg,
        #f0f0f0 25%,
        #e0e0e0 50%,
        #f0f0f0 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
```

---

## Animation Principles

### Easing Functions (Personality)

```css
:root {
    /* Fast start, slow end (natural) */
    --ease-out: cubic-bezier(0, 0, 0.2, 1);
    
    /* Slow start, fast end (anticipation) */
    --ease-in: cubic-bezier(0.4, 0, 1, 1);
    
    /* Slow both ends (smooth) */
    --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
    
    /* Bounce effect */
    --ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
    
    /* Spring effect */
    --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fi-btn {
    transition: transform 0.3s var(--ease-out);
}

.fi-modal {
    transition: transform 0.5s var(--ease-spring);
}
```

### Duration (Timing)

```css
:root {
    --duration-fast: 150ms;
    --duration-base: 300ms;
    --duration-slow: 500ms;
    --duration-slower: 700ms;
}

/* Micro-interactions: fast */
.fi-btn:hover {
    transition: background-color var(--duration-fast);
}

/* Regular transitions: base */
.fi-card {
    transition: transform var(--duration-base);
}

/* Entrance animations: slow */
.fi-modal-enter {
    animation: modal-enter var(--duration-slow);
}
```

### Animation Principles (Disney's 12)

**1. Squash & Stretch:**
```css
.fi-btn:active {
    transform: scale(0.95);
    transition: transform 0.1s;
}
```

**2. Anticipation:**
```css
.fi-dropdown-trigger:active {
    transform: translateY(2px);
}

.fi-dropdown-menu {
    transform: translateY(-10px);
    animation: dropdown-enter 0.3s forwards;
}

@keyframes dropdown-enter {
    to { transform: translateY(0); }
}
```

**3. Staging (Focus Attention):**
```css
.fi-highlight-enter {
    animation: spotlight 0.5s;
}

@keyframes spotlight {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
    }
    50% {
        box-shadow: 0 0 0 20px rgba(59, 130, 246, 0);
    }
}
```

**4. Follow Through:**
```css
.fi-tag-remove {
    animation: tag-exit 0.4s;
}

@keyframes tag-exit {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);  /* Overshoot */
    }
    100% {
        transform: scale(0);
        opacity: 0;
    }
}
```

---

## Composition Techniques

### Rule of Thirds

```css
.fi-hero-section {
    display: grid;
    grid-template-columns: 2fr 1fr;  /* 66% / 33% split */
}
```

### Golden Ratio (1.618)

```css
.fi-sidebar {
    flex-basis: 38.2%;  /* 100 / 1.618 ≈ 61.8% for content */
}

.fi-content {
    flex-basis: 61.8%;
}
```

### F-Pattern Layout

```css
.fi-page {
    display: grid;
    grid-template-areas:
        "header header header"
        "nav content sidebar"
        "footer footer footer";
    grid-template-columns: 200px 1fr 300px;
}
/* Eye naturally follows F-shape */
```

---

## Design Systems Thinking

### Tokens (Design Decisions)

```css
:root {
    /* Color tokens */
    --color-primary: oklch(0.55 0.2 250);
    --color-surface: oklch(0.98 0.01 0);
    --color-text: oklch(0.3 0.05 0);
    
    /* Space tokens */
    --space-xs: 0.25rem;
    --space-sm: 0.5rem;
    --space-md: 1rem;
    --space-lg: 1.5rem;
    --space-xl: 2rem;
    
    /* Typography tokens */
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    
    /* Shadow tokens */
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    
    /* Border tokens */
    --border-width: 1px;
    --border-radius-sm: 0.25rem;
    --border-radius-md: 0.5rem;
    --border-radius-lg: 1rem;
}
```

### Component Variants

```css
/* Base component */
.fi-button {
    /* Shared styles */
}

/* Size variants */
.fi-button--sm { }
.fi-button--md { }
.fi-button--lg { }

/* Color variants */
.fi-button--primary { }
.fi-button--secondary { }
.fi-button--danger { }

/* State variants */
.fi-button--disabled { }
.fi-button--loading { }
```

---

**Continue to Part 3: Component-Specific Patterns**

This covers creative foundations. Next, we'll apply these techniques to specific Filament components.
