# Advanced CSS Features & Techniques

**Part 4 of 5: Pushing the Boundaries**

**Audience:** AI Models mastering advanced CSS  
**Focus:** Modern CSS features, filters, transforms, and experimental techniques  
**Prerequisites:** Parts 1-3  
**Last Updated:** October 14, 2025

---

## Table of Contents

1. [CSS Custom Properties (Advanced)](#css-custom-properties-advanced)
2. [CSS Filters & Blend Modes](#css-filters--blend-modes)
3. [Transforms & 3D](#transforms--3d)
4. [Advanced Gradients](#advanced-gradients)
5. [Clip Path & Masks](#clip-path--masks)
6. [CSS Animations (Deep Dive)](#css-animations-deep-dive)
7. [Scroll Animations](#scroll-animations)
8. [Container Queries](#container-queries)
9. [View Transitions API](#view-transitions-api)
10. [CSS Houdini](#css-houdini)

---

## CSS Custom Properties (Advanced)

### Dynamic Property Calculations

```css
:root {
    --base-size: 16px;
    --scale: 1.25;
    
    /* Calculated values */
    --size-sm: calc(var(--base-size) / var(--scale));
    --size-md: var(--base-size);
    --size-lg: calc(var(--base-size) * var(--scale));
    --size-xl: calc(var(--base-size) * var(--scale) * var(--scale));
}

.fi-text-sm { font-size: var(--size-sm); }  /* 12.8px */
.fi-text-md { font-size: var(--size-md); }  /* 16px */
.fi-text-lg { font-size: var(--size-lg); }  /* 20px */
.fi-text-xl { font-size: var(--size-xl); }  /* 25px */
```

### Context-Aware Properties

```css
.fi-card {
    --card-padding: 1rem;
    --card-radius: 0.5rem;
    --card-shadow: 0 1px 3px rgba(0,0,0,0.1);
    
    padding: var(--card-padding);
    border-radius: var(--card-radius);
    box-shadow: var(--card-shadow);
}

/* Compact variant overrides */
.fi-card.fi-compact {
    --card-padding: 0.5rem;
}

/* Featured variant overrides */
.fi-card.fi-featured {
    --card-padding: 2rem;
    --card-shadow: 0 10px 15px rgba(0,0,0,0.1);
}
```

### Property Inheritance Chain

```css
.fi-theme-ocean {
    --primary-h: 200;
    --primary-s: 80%;
    --primary-l: 50%;
    --primary: hsl(var(--primary-h), var(--primary-s), var(--primary-l));
    
    /* Derived colors */
    --primary-light: hsl(var(--primary-h), var(--primary-s), 70%);
    --primary-dark: hsl(var(--primary-h), var(--primary-s), 30%);
}

.fi-btn.fi-color-primary {
    background: var(--primary);
}

.fi-btn.fi-color-primary:hover {
    background: var(--primary-dark);
}
```

### Fallback Chains

```css
.fi-component {
    /* Multiple fallbacks */
    background: var(--custom-bg, var(--theme-bg, white));
    color: var(--custom-text, var(--theme-text, black));
}
```

### Dynamic Theme Switching

```css
:root {
    --theme: 'light';
}

:root[data-theme='light'] {
    --bg: white;
    --text: black;
    --accent: #3b82f6;
}

:root[data-theme='dark'] {
    --bg: #1f2937;
    --text: white;
    --accent: #60a5fa;
}

:root[data-theme='high-contrast'] {
    --bg: black;
    --text: yellow;
    --accent: cyan;
}

.fi-panel {
    background: var(--bg);
    color: var(--text);
}
```

### Responsive Properties

```css
:root {
    --spacing: 1rem;
}

@media (min-width: 768px) {
    :root {
        --spacing: 1.5rem;
    }
}

@media (min-width: 1024px) {
    :root {
        --spacing: 2rem;
    }
}

.fi-section {
    padding: var(--spacing);
}
/* Automatically responsive! */
```

---

## CSS Filters & Blend Modes

### Filter Functions

```css
/* Blur */
.fi-backdrop {
    backdrop-filter: blur(10px);
}

/* Brightness */
.fi-image-dim {
    filter: brightness(0.7);
}

/* Contrast */
.fi-image-vivid {
    filter: contrast(1.3);
}

/* Grayscale */
.fi-disabled {
    filter: grayscale(1);
}

/* Hue Rotate */
.fi-image-tinted {
    filter: hue-rotate(45deg);
}

/* Saturate */
.fi-image-vibrant {
    filter: saturate(1.5);
}

/* Sepia */
.fi-image-vintage {
    filter: sepia(0.8);
}

/* Drop Shadow */
.fi-elevated {
    filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
}
```

### Combining Filters

```css
.fi-image-effect {
    filter: 
        brightness(1.1)
        contrast(1.2)
        saturate(1.3)
        blur(0.5px);
}
```

### Backdrop Effects

```css
.fi-glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: 
        blur(20px) 
        saturate(180%) 
        brightness(1.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
```

### Blend Modes

```css
/* Multiply (darken) */
.fi-overlay-darken {
    mix-blend-mode: multiply;
}

/* Screen (lighten) */
.fi-overlay-lighten {
    mix-blend-mode: screen;
}

/* Overlay (contrast) */
.fi-overlay {
    mix-blend-mode: overlay;
}

/* Color */
.fi-colorize {
    mix-blend-mode: color;
}

/* Difference (invert) */
.fi-invert {
    mix-blend-mode: difference;
}
```

### Creative Blend Mode Effects

**Duotone Image:**
```css
.fi-image-duotone {
    position: relative;
}

.fi-image-duotone::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    mix-blend-mode: color;
    opacity: 0.8;
}
```

**Text Cutout:**
```css
.fi-text-cutout {
    background: url('image.jpg');
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    mix-blend-mode: screen;
}
```

---

## Transforms & 3D

### 2D Transforms

```css
/* Translate */
.fi-hover-up:hover {
    transform: translateY(-4px);
}

/* Scale */
.fi-hover-grow:hover {
    transform: scale(1.05);
}

/* Rotate */
.fi-spinner {
    transform: rotate(45deg);
}

/* Skew */
.fi-slanted {
    transform: skewX(-5deg);
}

/* Multiple transforms */
.fi-complex:hover {
    transform: translateY(-4px) scale(1.05) rotate(2deg);
}
```

### 3D Transforms

```css
.fi-3d-card {
    transform-style: preserve-3d;
    perspective: 1000px;
}

.fi-3d-card-face {
    backface-visibility: hidden;
    transition: transform 0.6s;
}

.fi-3d-card:hover .fi-3d-card-face {
    transform: rotateY(180deg);
}
```

### Perspective

```css
.fi-perspective-container {
    perspective: 1000px;
    perspective-origin: center;
}

.fi-3d-child {
    transform: rotateX(10deg) rotateY(15deg);
}
```

### Flip Card

```css
.fi-flip-card {
    perspective: 1000px;
    width: 300px;
    height: 400px;
}

.fi-flip-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.6s;
    transform-style: preserve-3d;
}

.fi-flip-card:hover .fi-flip-card-inner {
    transform: rotateY(180deg);
}

.fi-flip-card-front,
.fi-flip-card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
}

.fi-flip-card-back {
    transform: rotateY(180deg);
}
```

### Tilt Effect (Interactive)

```css
.fi-tilt-card {
    transition: transform 0.1s;
}

/* Applied via JavaScript */
.fi-tilt-card[style*="--mouse-x"][style*="--mouse-y"] {
    transform: 
        perspective(1000px)
        rotateX(calc(var(--mouse-y) * 10deg))
        rotateY(calc(var(--mouse-x) * 10deg));
}
```

---

## Advanced Gradients

### Multi-Stop Gradients

```css
.fi-rainbow {
    background: linear-gradient(
        to right,
        #ff0000 0%,
        #ff7f00 16.67%,
        #ffff00 33.33%,
        #00ff00 50%,
        #0000ff 66.67%,
        #4b0082 83.33%,
        #9400d3 100%
    );
}
```

### Radial Gradients

```css
.fi-spotlight {
    background: radial-gradient(
        circle at 30% 30%,
        rgba(255, 255, 255, 0.8),
        rgba(255, 255, 255, 0) 70%
    );
}

.fi-vignette {
    background: radial-gradient(
        ellipse at center,
        transparent 50%,
        rgba(0, 0, 0, 0.7)
    );
}
```

### Conic Gradients

```css
.fi-pie-chart {
    background: conic-gradient(
        #3b82f6 0deg 120deg,
        #10b981 120deg 240deg,
        #f59e0b 240deg 360deg
    );
    border-radius: 50%;
}

.fi-loading-spinner {
    background: conic-gradient(
        from 0deg,
        transparent 0deg 270deg,
        #3b82f6 270deg 360deg
    );
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
```

### Gradient Patterns

**Stripes:**
```css
.fi-stripes {
    background: repeating-linear-gradient(
        45deg,
        #3b82f6,
        #3b82f6 10px,
        #60a5fa 10px,
        #60a5fa 20px
    );
}
```

**Checkerboard:**
```css
.fi-checkerboard {
    background-image:
        linear-gradient(45deg, #ddd 25%, transparent 25%),
        linear-gradient(-45deg, #ddd 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #ddd 75%),
        linear-gradient(-45deg, transparent 75%, #ddd 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}
```

**Dots:**
```css
.fi-dots {
    background-image: radial-gradient(circle, #3b82f6 1px, transparent 1px);
    background-size: 20px 20px;
}
```

### Animated Gradients

**Gradient Shift:**
```css
.fi-animated-gradient {
    background: linear-gradient(
        -45deg,
        #ee7752, #e73c7e, #23a6d5, #23d5ab
    );
    background-size: 400% 400%;
    animation: gradient-shift 15s ease infinite;
}

@keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
```

**Gradient Rotation:**
```css
.fi-rotating-gradient {
    background: conic-gradient(
        from 0deg,
        #3b82f6, #8b5cf6, #ec4899, #3b82f6
    );
    animation: rotate 3s linear infinite;
}

@keyframes rotate {
    to { transform: rotate(360deg); }
}
```

---

## Clip Path & Masks

### Basic Clip Paths

```css
/* Circle */
.fi-clip-circle {
    clip-path: circle(50%);
}

/* Ellipse */
.fi-clip-ellipse {
    clip-path: ellipse(60% 40%);
}

/* Polygon (Triangle) */
.fi-clip-triangle {
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
}

/* Pentagon */
.fi-clip-pentagon {
    clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
}

/* Hexagon */
.fi-clip-hexagon {
    clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%);
}

/* Star */
.fi-clip-star {
    clip-path: polygon(
        50% 0%, 61% 35%, 98% 35%, 68% 57%, 
        79% 91%, 50% 70%, 21% 91%, 32% 57%, 
        2% 35%, 39% 35%
    );
}
```

### Custom Shapes

```css
/* Notched corner */
.fi-notch {
    clip-path: polygon(
        0 0, calc(100% - 20px) 0, 100% 20px, 
        100% 100%, 0 100%
    );
}

/* Speech bubble */
.fi-bubble {
    clip-path: polygon(
        0% 0%, 100% 0%, 100% 75%, 
        75% 75%, 75% 100%, 50% 75%, 
        0% 75%
    );
}

/* Arrow */
.fi-arrow {
    clip-path: polygon(
        0% 20%, 60% 20%, 60% 0%, 
        100% 50%, 60% 100%, 60% 80%, 
        0% 80%
    );
}
```

### Animated Clip Paths

```css
.fi-reveal {
    clip-path: inset(0 100% 0 0);
    animation: reveal 1s forwards;
}

@keyframes reveal {
    to {
        clip-path: inset(0 0 0 0);
    }
}
```

### CSS Masks

**Gradient Mask:**
```css
.fi-fade-out {
    mask-image: linear-gradient(
        to bottom,
        black 0%,
        black 70%,
        transparent 100%
    );
}
```

**Image Mask:**
```css
.fi-masked {
    mask-image: url('mask.svg');
    mask-size: cover;
    mask-position: center;
}
```

**Repeating Mask:**
```css
.fi-dotted-mask {
    mask-image: radial-gradient(circle, black 40%, transparent 40%);
    mask-size: 20px 20px;
}
```

---

## CSS Animations (Deep Dive)

### Keyframe Timing

```css
@keyframes complex-timing {
    0% {
        transform: translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    50% {
        transform: translateX(100px);
    }
    80% {
        transform: translateX(120px); /* Overshoot */
    }
    100% {
        transform: translateX(100px);
        opacity: 1;
    }
}
```

### Animation Properties (Complete)

```css
.fi-animated {
    animation-name: slide-in;
    animation-duration: 1s;
    animation-timing-function: ease-in-out;
    animation-delay: 0.5s;
    animation-iteration-count: 3;
    animation-direction: alternate;
    animation-fill-mode: forwards;
    animation-play-state: running;
}

/* Shorthand */
.fi-animated {
    animation: slide-in 1s ease-in-out 0.5s 3 alternate forwards;
}
```

### Multiple Animations

```css
.fi-complex {
    animation:
        slide-in 1s ease-out,
        fade-in 0.5s ease-in,
        rotate 2s linear infinite;
}
```

### Animation States

```css
.fi-paused {
    animation-play-state: paused;
}

.fi-hover-pause:hover {
    animation-play-state: paused;
}
```

### Physics-Based Animations

**Bounce:**
```css
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(-25%);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
```

**Elastic:**
```css
@keyframes elastic {
    0% {
        transform: scale(1);
    }
    30% {
        transform: scale(1.25);
    }
    40% {
        transform: scale(0.75);
    }
    50% {
        transform: scale(1.15);
    }
    65% {
        transform: scale(0.95);
    }
    75% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}
```

### Staggered Animations

```css
.fi-list-item {
    opacity: 0;
    animation: fade-in 0.5s forwards;
}

.fi-list-item:nth-child(1) { animation-delay: 0.1s; }
.fi-list-item:nth-child(2) { animation-delay: 0.2s; }
.fi-list-item:nth-child(3) { animation-delay: 0.3s; }
.fi-list-item:nth-child(4) { animation-delay: 0.4s; }
.fi-list-item:nth-child(5) { animation-delay: 0.5s; }

/* Or use CSS variables */
.fi-list-item {
    animation-delay: calc(var(--index) * 0.1s);
}
```

---

## Scroll Animations

### Scroll-Driven Animations (Native)

```css
.fi-fade-on-scroll {
    animation: fade-in linear;
    animation-timeline: scroll();
    animation-range: 0 500px;
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

### Scroll-Linked Progress Bar

```css
.fi-progress-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 4px;
    background: #3b82f6;
    transform-origin: left;
    animation: progress linear;
    animation-timeline: scroll(root);
}

@keyframes progress {
    from {
        transform: scaleX(0);
    }
    to {
        transform: scaleX(1);
    }
}
```

### Parallax Scrolling

```css
.fi-parallax {
    transform: translateZ(-1px) scale(2);
}

.fi-parallax-container {
    perspective: 1px;
    height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
}
```

### Sticky Elements with Effects

```css
.fi-sticky-header {
    position: sticky;
    top: 0;
    z-index: 10;
    transition: all 0.3s;
}

/* Via JavaScript: add .scrolled when scrolling */
.fi-sticky-header.scrolled {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 0.5rem 1rem;
}
```

---

## Container Queries

### Basic Container Query

```css
.fi-card-container {
    container-type: inline-size;
    container-name: card;
}

.fi-card-content {
    padding: 1rem;
}

@container card (min-width: 400px) {
    .fi-card-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        padding: 2rem;
    }
}

@container card (min-width: 600px) {
    .fi-card-content {
        grid-template-columns: 1fr 1fr 1fr;
    }
}
```

### Responsive Components (No Media Queries!)

```css
.fi-widget {
    container-type: inline-size;
}

.fi-widget-header {
    font-size: 1rem;
}

@container (min-width: 300px) {
    .fi-widget-header {
        font-size: 1.125rem;
    }
}

@container (min-width: 500px) {
    .fi-widget-header {
        font-size: 1.25rem;
    }
}
```

---

## View Transitions API

### Basic Page Transition

```css
::view-transition-old(root),
::view-transition-new(root) {
    animation-duration: 0.3s;
}

::view-transition-old(root) {
    animation-name: fade-out;
}

::view-transition-new(root) {
    animation-name: fade-in;
}

@keyframes fade-out {
    to { opacity: 0; }
}

@keyframes fade-in {
    from { opacity: 0; }
}
```

### Custom Transitions

```css
/* Assign view-transition-name */
.fi-card {
    view-transition-name: card-transition;
}

/* Style the transition */
::view-transition-old(card-transition) {
    animation: slide-out 0.5s;
}

::view-transition-new(card-transition) {
    animation: slide-in 0.5s;
}
```

---

## CSS Houdini

### Custom Paint API

```css
/* Register paint worklet (in JS) */
CSS.paintWorklet.addModule('my-painter.js');

/* Use custom paint */
.fi-custom-background {
    background-image: paint(myPainter);
}
```

### Custom Properties API

```js
// Register custom property
CSS.registerProperty({
    name: '--my-color',
    syntax: '<color>',
    inherits: false,
    initialValue: '#3b82f6'
});
```

```css
.fi-animatable-color {
    --my-color: #3b82f6;
    background: var(--my-color);
    transition: --my-color 1s;
}

.fi-animatable-color:hover {
    --my-color: #8b5cf6;
}
```

---

**Continue to Part 5: Performance & Best Practices**

This covers advanced CSS techniques. The final part will focus on optimization, debugging, and production considerations.
