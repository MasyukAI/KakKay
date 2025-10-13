# Performance, Best Practices & Production

**Part 5 of 5: Professional CSS Development**

**Audience:** AI Models creating production-ready CSS  
**Focus:** Optimization, debugging, accessibility, and maintenance  
**Prerequisites:** Parts 1-4  
**Last Updated:** October 14, 2025

---

## Table of Contents

1. [Performance Optimization](#performance-optimization)
2. [CSS Architecture & Organization](#css-architecture--organization)
3. [Accessibility (A11y)](#accessibility-a11y)
4. [Browser Compatibility](#browser-compatibility)
5. [Debugging Techniques](#debugging-techniques)
6. [Code Quality Standards](#code-quality-standards)
7. [Testing CSS](#testing-css)
8. [Documentation Practices](#documentation-practices)
9. [Build & Deployment](#build--deployment)
10. [Maintenance & Refactoring](#maintenance--refactoring)

---

## Performance Optimization

### Critical Rendering Path

**Understanding the Cost:**

| Operation | Cost | Example |
|-----------|------|---------|
| Layout (Reflow) | High | `width`, `margin`, `padding`, `display` |
| Paint | Medium | `color`, `background`, `border`, `box-shadow` |
| Composite | Low | `transform`, `opacity`, `filter` |

**Optimize for compositing:**

```css
/* ❌ BAD - Triggers layout */
.fi-hover-bad:hover {
    margin-top: -10px;
    width: 110%;
}

/* ✅ GOOD - Only composites */
.fi-hover-good:hover {
    transform: translateY(-10px) scale(1.1);
}
```

### GPU Acceleration

**Force GPU rendering:**

```css
.fi-accelerated {
    transform: translateZ(0);
    /* Or */
    will-change: transform;
}
```

**Use `will-change` carefully:**

```css
/* ✅ GOOD - Applied before animation */
.fi-card:hover {
    will-change: transform;
}

.fi-card.fi-animating {
    transform: scale(1.1);
}

/* ❌ BAD - Always on (wastes memory) */
.fi-card {
    will-change: transform, opacity, background;
}
```

### Selector Performance

**Avoid expensive selectors:**

```css
/* ❌ SLOW - Universal selector */
* {
    box-sizing: border-box;
}

/* ✅ BETTER - Scoped */
.fi-panel * {
    box-sizing: border-box;
}

/* ❌ SLOW - Deep descendant */
.fi-panel .fi-section .fi-content div p span {
    color: red;
}

/* ✅ FAST - Direct class */
.fi-text-highlighted {
    color: red;
}
```

**Selector specificity costs:**

```css
/* Fast → Slow */
.fi-btn {}                    /* Class: Fast */
.fi-btn.fi-primary {}         /* 2 classes: Fast */
.fi-section .fi-btn {}        /* Descendant: Medium */
div.fi-btn {}                 /* Tag + class: Medium */
.fi-section > .fi-btn {}      /* Child: Medium */
[data-active="true"] {}       /* Attribute: Slower */
.fi-btn:not(.fi-disabled) {}  /* Pseudo: Slower */
```

### CSS File Size

**Minimize bytes:**

```css
/* Use shorthand */
/* ❌ Verbose */
.fi-card {
    margin-top: 1rem;
    margin-right: 1rem;
    margin-bottom: 1rem;
    margin-left: 1rem;
}

/* ✅ Concise */
.fi-card {
    margin: 1rem;
}
```

**Remove redundant code:**

```css
/* ❌ Redundant */
.fi-btn {
    display: block;
    display: flex;  /* This wins, remove first */
}

/* ✅ Clean */
.fi-btn {
    display: flex;
}
```

**Combine selectors:**

```css
/* ❌ Repetitive */
.fi-btn-primary {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

.fi-btn-secondary {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

/* ✅ Combined */
.fi-btn-primary,
.fi-btn-secondary {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}
```

### Optimize Animations

```css
/* ❌ BAD - Many properties */
.fi-card {
    transition: all 0.3s;
}

/* ✅ GOOD - Specific properties */
.fi-card {
    transition: 
        transform 0.3s,
        opacity 0.3s;
}

/* ✅ BEST - Composite-only */
.fi-card {
    transition: transform 0.3s;
}
```

### Loading Strategies

**Critical CSS:**

```html
<!-- Inline critical styles -->
<style>
    .fi-panel { background: white; }
    .fi-btn { padding: 0.5rem 1rem; }
</style>

<!-- Defer non-critical -->
<link rel="preload" href="styles.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
```

**CSS Containment:**

```css
.fi-card {
    contain: layout style paint;
}

.fi-widget {
    contain: strict;  /* Layout + style + paint + size */
}
```

### Font Loading Optimization

```css
@font-face {
    font-family: 'CustomFont';
    src: url('font.woff2') format('woff2');
    font-display: swap;  /* Show fallback immediately */
}
```

---

## CSS Architecture & Organization

### File Structure

```
resources/css/
├── filament/
│   └── admin/
│       └── theme.css
├── base/
│   ├── reset.css
│   ├── typography.css
│   └── variables.css
├── components/
│   ├── buttons.css
│   ├── forms.css
│   ├── tables.css
│   └── modals.css
├── layouts/
│   ├── grid.css
│   └── navigation.css
├── utilities/
│   ├── spacing.css
│   └── colors.css
└── themes/
    ├── light.css
    └── dark.css
```

### Naming Methodologies

**BEM (Block Element Modifier):**

```css
/* Block */
.fi-card {}

/* Element */
.fi-card__header {}
.fi-card__body {}
.fi-card__footer {}

/* Modifier */
.fi-card--featured {}
.fi-card--compact {}

/* Combined */
.fi-card--featured .fi-card__header {}
```

**ITCSS (Inverted Triangle CSS):**

```css
/* 1. Settings (variables) */
:root {
    --primary: #3b82f6;
}

/* 2. Tools (mixins, functions) */
/* Handled by preprocessor */

/* 3. Generic (reset, normalize) */
* {
    box-sizing: border-box;
}

/* 4. Elements (bare HTML) */
body {
    font-family: sans-serif;
}

/* 5. Objects (layout patterns) */
.o-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* 6. Components (UI pieces) */
.c-button {
    padding: 0.5rem 1rem;
}

/* 7. Utilities (helpers) */
.u-hidden {
    display: none;
}
```

### Code Organization

**Group related styles:**

```css
/* ================================
   BUTTONS
   ================================ */

/* Base button */
.fi-btn {
    /* Foundation */
    display: inline-flex;
    align-items: center;
    
    /* Typography */
    font-weight: 600;
    
    /* Spacing */
    padding: 0.625rem 1rem;
    
    /* Appearance */
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    
    /* Interaction */
    cursor: pointer;
    transition: all 0.15s;
}

/* States */
.fi-btn:hover {
    background: #f9fafb;
}

.fi-btn:active {
    transform: translateY(1px);
}

.fi-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Variants */
.fi-btn.fi-color-primary {
    background: #3b82f6;
    color: white;
}

.fi-btn.fi-size-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
```

### CSS Variables Organization

```css
:root {
    /* ================================
       COLORS
       ================================ */
    
    /* Primary palette */
    --color-primary-50: oklch(0.95 0.05 250);
    --color-primary-500: oklch(0.55 0.2 250);
    --color-primary-900: oklch(0.3 0.15 250);
    
    /* Semantic colors */
    --color-success: var(--color-green-500);
    --color-danger: var(--color-red-500);
    --color-warning: var(--color-yellow-500);
    
    /* ================================
       SPACING
       ================================ */
    
    --space-1: 0.25rem;
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    
    /* ================================
       TYPOGRAPHY
       ================================ */
    
    --font-sans: system-ui, sans-serif;
    --font-mono: 'SF Mono', monospace;
    
    --text-xs: 0.75rem;
    --text-sm: 0.875rem;
    --text-base: 1rem;
    --text-lg: 1.125rem;
    --text-xl: 1.25rem;
    
    /* ================================
       LAYOUT
       ================================ */
    
    --radius-sm: 0.25rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-full: 9999px;
    
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
    
    /* ================================
       ANIMATION
       ================================ */
    
    --duration-fast: 150ms;
    --duration-base: 300ms;
    --duration-slow: 500ms;
    
    --ease-in: cubic-bezier(0.4, 0, 1, 1);
    --ease-out: cubic-bezier(0, 0, 0.2, 1);
    --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
}
```

---

## Accessibility (A11y)

### Color Contrast (WCAG AA/AAA)

```css
/* ❌ FAILS - 2.5:1 ratio */
.fi-text-low-contrast {
    color: #999999;
    background: #ffffff;
}

/* ✅ PASSES AA - 4.5:1 ratio */
.fi-text-good-contrast {
    color: #666666;
    background: #ffffff;
}

/* ✅ PASSES AAA - 7:1 ratio */
.fi-text-excellent-contrast {
    color: #333333;
    background: #ffffff;
}
```

### Focus Indicators

```css
/* ❌ BAD - Removes focus */
.fi-btn:focus {
    outline: none;
}

/* ✅ GOOD - Custom focus */
.fi-btn:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* ✅ BETTER - Visible ring */
.fi-btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}
```

### Skip Links

```css
.fi-skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #3b82f6;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    z-index: 9999;
}

.fi-skip-link:focus {
    top: 0;
}
```

### Screen Reader Only

```css
.fi-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
```

### Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

### High Contrast Mode

```css
@media (prefers-contrast: high) {
    .fi-btn {
        border: 2px solid currentColor;
    }
    
    .fi-card {
        border: 2px solid #000;
    }
}
```

### Color Blindness

```css
/* Don't rely only on color */

/* ❌ BAD */
.fi-status-success {
    color: green;
}

.fi-status-error {
    color: red;
}

/* ✅ GOOD - Add icons/text */
.fi-status-success::before {
    content: '✓ ';
}

.fi-status-error::before {
    content: '✗ ';
}
```

---

## Browser Compatibility

### Feature Detection

```css
/* Check support with @supports */
@supports (backdrop-filter: blur(10px)) {
    .fi-glass {
        backdrop-filter: blur(10px);
    }
}

@supports not (backdrop-filter: blur(10px)) {
    .fi-glass {
        background: rgba(255, 255, 255, 0.9);
    }
}
```

### Vendor Prefixes

```css
.fi-transform {
    -webkit-transform: translateX(10px);
    -moz-transform: translateX(10px);
    -ms-transform: translateX(10px);
    transform: translateX(10px);
}

.fi-appearance-none {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
```

### Fallbacks

```css
.fi-gradient {
    background: #3b82f6;  /* Fallback */
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
}

.fi-custom-font {
    font-family: 'CustomFont', system-ui, sans-serif;
}
```

### Progressive Enhancement

```css
/* Base (works everywhere) */
.fi-card {
    background: white;
    border: 1px solid #ddd;
    padding: 1rem;
}

/* Enhanced (modern browsers) */
@supports (display: grid) {
    .fi-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
}
```

---

## Debugging Techniques

### Visual Debugging

```css
/* Show all elements */
* {
    outline: 1px solid red !important;
}

/* Show specific element */
.fi-problematic {
    outline: 3px solid lime !important;
}
```

### Debug Classes

```css
.debug-grid {
    background-image:
        linear-gradient(rgba(255,0,0,0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,0,0,0.1) 1px, transparent 1px);
    background-size: 10px 10px;
}

.debug-center {
    position: relative;
}

.debug-center::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    background: red;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
```

### Common Issues

**Issue: Collapsed margin**
```css
/* Problem */
.fi-parent {
    margin-top: 20px;
}
.fi-child {
    margin-top: 30px;  /* Only 30px total, not 50px! */
}

/* Solution 1: Border */
.fi-parent {
    border-top: 1px solid transparent;
    margin-top: 20px;
}

/* Solution 2: Padding */
.fi-parent {
    padding-top: 1px;
    margin-top: 20px;
}

/* Solution 3: Flexbox/Grid */
.fi-parent {
    display: flex;
    flex-direction: column;
}
```

**Issue: Z-index not working**
```css
/* Problem */
.fi-element {
    z-index: 999;  /* Doesn't work! */
}

/* Solution: Add position */
.fi-element {
    position: relative;  /* or absolute, fixed, sticky */
    z-index: 999;
}
```

**Issue: 100vh on mobile**
```css
/* Problem */
.fi-full-height {
    height: 100vh;  /* Address bar causes issues */
}

/* Solution */
.fi-full-height {
    height: 100dvh;  /* Dynamic viewport height */
}
```

---

## Code Quality Standards

### Formatting Rules

```css
/* ✅ GOOD */
.fi-button {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: #3b82f6;
    color: white;
}

/* Properties alphabetized (optional but consistent) */
.fi-card {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 1.5rem;
}
```

### Avoid Magic Numbers

```css
/* ❌ BAD */
.fi-card {
    margin-top: 23px;
    padding-left: 17px;
}

/* ✅ GOOD */
:root {
    --card-spacing: 1.5rem;
}

.fi-card {
    margin-top: var(--card-spacing);
    padding-left: var(--card-spacing);
}
```

### Avoid Overly Specific Selectors

```css
/* ❌ BAD */
div.fi-panel > section.fi-section div.fi-content > p.fi-text {
    color: red;
}

/* ✅ GOOD */
.fi-text-danger {
    color: red;
}
```

### Use Comments Effectively

```css
/* ================================
   SECTION TITLE
   ================================ */

/* Sub-section description */
.fi-component {
    /* Property explanation if needed */
    transform: translateZ(0); /* Force GPU acceleration */
}

/* Multi-line explanation
   for complex logic
   that requires context */
```

---

## Testing CSS

### Visual Regression Testing

**Tools:** Percy, Chromatic, BackstopJS

**Approach:**
1. Capture baseline screenshots
2. Make CSS changes
3. Compare new screenshots to baseline
4. Review differences

### Browser Testing Checklist

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

### Responsive Testing

**Breakpoints to test:**
- Mobile: 375px, 414px
- Tablet: 768px, 1024px
- Desktop: 1280px, 1440px, 1920px

### Accessibility Testing

**Tools:**
- axe DevTools
- WAVE
- Lighthouse

**Manual checks:**
- [ ] Keyboard navigation works
- [ ] Focus indicators visible
- [ ] Color contrast passes WCAG AA
- [ ] Screen reader announces correctly

---

## Documentation Practices

### Component Documentation

```css
/**
 * Button Component
 * 
 * A flexible button component with multiple variants.
 * 
 * Variants:
 * - .fi-btn (base)
 * - .fi-btn.fi-color-primary
 * - .fi-btn.fi-color-secondary
 * - .fi-btn.fi-size-sm
 * - .fi-btn.fi-size-lg
 * - .fi-btn.fi-outlined
 * 
 * States:
 * - :hover
 * - :active
 * - :disabled
 * 
 * Usage:
 * <button class="fi-btn fi-color-primary">Click me</button>
 */

.fi-btn {
    /* ... */
}
```

### CSS Style Guide

Create a `STYLING-GUIDE.md`:

```markdown
# CSS Style Guide

## Naming Conventions

- Use kebab-case for classes: `.my-component`
- Prefix Filament classes: `.fi-*`
- Use BEM for complex components

## File Organization

- Group related components
- One component per section
- Add section dividers

## Best Practices

- Use CSS variables for theming
- Minimize specificity
- Optimize for performance
- Test in multiple browsers
```

---

## Build & Deployment

### Build Process

```bash
# Development
npm run dev

# Production
npm run build

# Optimize
npm run build
php artisan filament:optimize
```

### CSS Minification

```javascript
// vite.config.js
export default {
    build: {
        cssMinify: true,
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name].[hash][extname]'
            }
        }
    }
}
```

### PurgeCSS Configuration

```javascript
// Remove unused CSS
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Filament/**/*.php',
    ],
}
```

### Performance Budget

**Targets:**
- CSS file size: < 100KB (gzipped)
- Load time: < 500ms
- First Paint: < 1s

---

## Maintenance & Refactoring

### Identifying Technical Debt

**Code smells:**
- `!important` everywhere
- Deep selector nesting
- Duplicate code
- Unused styles

### Refactoring Strategy

1. **Audit:** Find duplicate/unused code
2. **Extract:** Create reusable variables/classes
3. **Simplify:** Reduce specificity
4. **Test:** Ensure nothing breaks
5. **Document:** Update style guide

### Deprecation Process

```css
/* ❌ DEPRECATED: Use .fi-btn-primary instead */
.old-button-style {
    /* ... */
}

/* ✅ NEW */
.fi-btn-primary {
    /* ... */
}
```

### Version Control

**Commit messages:**
```
feat(buttons): Add outlined variant
fix(tables): Correct header alignment
refactor(colors): Use CSS custom properties
perf(animations): Optimize for GPU
docs(buttons): Update usage examples
```

---

## Summary Checklist

### Before Committing

- [ ] Code is formatted consistently
- [ ] No console errors/warnings
- [ ] Tested in multiple browsers
- [ ] Accessibility checks pass
- [ ] Performance is acceptable
- [ ] Documentation is updated
- [ ] No merge conflicts

### Code Review Checklist

- [ ] Follows naming conventions
- [ ] Uses CSS variables appropriately
- [ ] Selectors are performant
- [ ] No unnecessary `!important`
- [ ] Responsive design works
- [ ] Accessibility considered
- [ ] Comments explain why, not what

---

## Quick Reference

### Performance Hierarchy

1. **Fastest:** `transform`, `opacity`
2. **Fast:** `color`, `background-color`
3. **Medium:** `border`, `box-shadow`
4. **Slow:** `width`, `height`, `margin`, `padding`
5. **Slowest:** `display`, `position`

### Specificity Values

- Inline style: 1000
- ID: 100
- Class/attribute/pseudo-class: 10
- Element/pseudo-element: 1

### Accessibility Quick Wins

1. Use semantic HTML
2. Add focus indicators
3. Ensure color contrast
4. Support keyboard navigation
5. Test with screen readers
6. Respect reduced motion
7. Provide text alternatives

---

**End of Series**

You now have a complete understanding of:
1. Filament CSS fundamentals
2. Creative styling techniques
3. Component-specific patterns
4. Advanced CSS features
5. Performance & best practices

**Continue learning:**
- Experiment with examples
- Read Filament documentation
- Study modern CSS features
- Test in real projects
- Stay updated on CSS specifications

---

*Master the fundamentals, embrace creativity, optimize relentlessly.*
