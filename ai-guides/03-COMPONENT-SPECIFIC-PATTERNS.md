# Component-Specific Styling Patterns

**Part 3 of 5: Mastering Individual Components**

**Audience:** AI Models styling Filament components  
**Focus:** Practical patterns for each component type  
**Prerequisites:** Parts 1-2  
**Last Updated:** October 14, 2025

---

## Table of Contents

1. [Buttons & Actions](#buttons--actions)
2. [Forms & Fields](#forms--fields)
3. [Tables & Data](#tables--data)
4. [Modals & Overlays](#modals--overlays)
5. [Navigation](#navigation)
6. [Badges & Labels](#badges--labels)
7. [Cards & Sections](#cards--sections)
8. [Widgets & Stats](#widgets--stats)
9. [Tabs & Wizards](#tabs--wizards)
10. [Notifications & Alerts](#notifications--alerts)

---

## Buttons & Actions

### Anatomy of a Button

```html
<button class="fi-btn fi-color-primary fi-size-md">
    <svg class="fi-btn-icon">...</svg>
    <span class="fi-btn-label">Save</span>
</button>
```

### Base Button Styling

```css
.fi-btn {
    /* Foundation */
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    
    /* Typography */
    font-weight: 600;
    font-size: 0.875rem;
    line-height: 1.25;
    white-space: nowrap;
    
    /* Box model */
    padding: 0.625rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid transparent;
    
    /* Interaction */
    cursor: pointer;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    
    /* States */
    outline-offset: 2px;
}

.fi-btn:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 2px;
}

.fi-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
```

### Button Variants

#### Solid (Primary)
```css
.fi-btn.fi-color-primary {
    background: linear-gradient(to bottom, #3b82f6, #2563eb);
    color: white;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.fi-btn.fi-color-primary:hover {
    background: linear-gradient(to bottom, #2563eb, #1d4ed8);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.fi-btn.fi-color-primary:active {
    transform: translateY(1px);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
```

#### Outlined
```css
.fi-btn.fi-outlined {
    background: transparent;
    border: 2px solid currentColor;
    color: #3b82f6;
}

.fi-btn.fi-outlined:hover {
    background: rgba(59, 130, 246, 0.05);
}

.fi-btn.fi-outlined:active {
    background: rgba(59, 130, 246, 0.1);
}
```

#### Ghost (Subtle)
```css
.fi-btn.fi-ghost {
    background: transparent;
    color: #6b7280;
}

.fi-btn.fi-ghost:hover {
    background: rgba(0, 0, 0, 0.05);
    color: #111827;
}
```

#### Link Style
```css
.fi-btn.fi-link {
    background: transparent;
    color: #3b82f6;
    padding: 0;
    border-radius: 0;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.fi-btn.fi-link:hover {
    text-decoration-thickness: 2px;
}
```

### Size Variants

```css
.fi-btn.fi-size-xs {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
}

.fi-btn.fi-size-sm {
    padding: 0.5rem 0.875rem;
    font-size: 0.8125rem;
}

.fi-btn.fi-size-md {
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
}

.fi-btn.fi-size-lg {
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
}

.fi-btn.fi-size-xl {
    padding: 1rem 1.5rem;
    font-size: 1.125rem;
}
```

### Loading State

```css
.fi-btn.fi-loading {
    position: relative;
    color: transparent;
    pointer-events: none;
}

.fi-btn.fi-loading::after {
    content: '';
    position: absolute;
    width: 1rem;
    height: 1rem;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
```

### Button Group

```css
.fi-btn-group {
    display: inline-flex;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.fi-btn-group .fi-btn {
    border-radius: 0;
    border-right: 1px solid rgba(0, 0, 0, 0.1);
}

.fi-btn-group .fi-btn:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.fi-btn-group .fi-btn:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    border-right: none;
}

.fi-btn-group .fi-btn.fi-active {
    background: #3b82f6;
    color: white;
    z-index: 1;
}
```

### Icon Buttons

```css
.fi-btn.fi-icon-only {
    padding: 0.625rem;
    width: 2.5rem;
    height: 2.5rem;
}

.fi-btn-icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* Icon button variations */
.fi-btn.fi-icon-circular {
    border-radius: 50%;
}

.fi-btn.fi-icon-square {
    border-radius: 0.25rem;
}
```

### Creative Button Effects

**Shimmer Effect:**
```css
.fi-btn.fi-shimmer {
    position: relative;
    overflow: hidden;
}

.fi-btn.fi-shimmer::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
    );
    transition: left 0.5s;
}

.fi-btn.fi-shimmer:hover::before {
    left: 100%;
}
```

**Ripple Effect:**
```css
.fi-btn.fi-ripple {
    position: relative;
    overflow: hidden;
}

.fi-btn.fi-ripple::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.fi-btn.fi-ripple:active::after {
    width: 300px;
    height: 300px;
}
```

**3D Button:**
```css
.fi-btn.fi-3d {
    background: linear-gradient(to bottom, #3b82f6, #2563eb);
    box-shadow: 
        0 4px 0 #1d4ed8,
        0 8px 12px rgba(0, 0, 0, 0.2);
    transform: translateY(0);
    transition: all 0.1s;
}

.fi-btn.fi-3d:active {
    transform: translateY(4px);
    box-shadow: 
        0 0 0 #1d4ed8,
        0 2px 4px rgba(0, 0, 0, 0.2);
}
```

---

## Forms & Fields

### Field Anatomy

```html
<div class="fi-fo-field">
    <label class="fi-fo-field-label">
        Name <span class="fi-required">*</span>
    </label>
    <div class="fi-fo-field-wrapper">
        <input class="fi-input" type="text">
        <p class="fi-fo-field-hint">Enter your full name</p>
    </div>
    <p class="fi-fo-field-error">This field is required</p>
</div>
```

### Input Styling

```css
.fi-input {
    /* Foundation */
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    line-height: 1.5;
    
    /* Appearance */
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    color: #111827;
    
    /* Interaction */
    transition: all 0.15s;
    outline: none;
}

.fi-input:hover {
    border-color: #9ca3af;
}

.fi-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.fi-input::placeholder {
    color: #9ca3af;
}

.fi-input:disabled {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}
```

### Input States

```css
/* Error state */
.fi-fo-field.fi-has-error .fi-input {
    border-color: #ef4444;
}

.fi-fo-field.fi-has-error .fi-input:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.fi-fo-field-error {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #ef4444;
}

/* Success state */
.fi-fo-field.fi-has-success .fi-input {
    border-color: #10b981;
}

.fi-fo-field-success {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #10b981;
}
```

### Input Affixes (Icons, Buttons)

```css
.fi-fo-field-wrapper {
    position: relative;
}

.fi-input-prefix {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
}

.fi-input-suffix {
    position: absolute;
    right: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
}

.fi-input.has-prefix {
    padding-left: 2.5rem;
}

.fi-input.has-suffix {
    padding-right: 2.5rem;
}
```

### Select Styling

```css
.fi-select {
    appearance: none;
    background-image: url("data:image/svg+xml,..."); /* Chevron icon */
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1rem;
    padding-right: 2.5rem;
}
```

### Checkbox & Radio

```css
.fi-checkbox,
.fi-radio {
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #d1d5db;
    transition: all 0.15s;
}

.fi-checkbox {
    border-radius: 0.25rem;
}

.fi-radio {
    border-radius: 50%;
}

.fi-checkbox:checked,
.fi-radio:checked {
    background: #3b82f6;
    border-color: #3b82f6;
}

.fi-checkbox:focus,
.fi-radio:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
```

### Toggle Switch

```css
.fi-toggle {
    position: relative;
    display: inline-block;
    width: 3rem;
    height: 1.5rem;
}

.fi-toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.fi-toggle-slider {
    position: absolute;
    inset: 0;
    background: #d1d5db;
    border-radius: 1.5rem;
    transition: background 0.2s;
    cursor: pointer;
}

.fi-toggle-slider::before {
    content: '';
    position: absolute;
    width: 1.25rem;
    height: 1.25rem;
    left: 0.125rem;
    bottom: 0.125rem;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s;
}

.fi-toggle-input:checked + .fi-toggle-slider {
    background: #3b82f6;
}

.fi-toggle-input:checked + .fi-toggle-slider::before {
    transform: translateX(1.5rem);
}
```

### File Upload

```css
.fi-fo-file-upload {
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    background: #f9fafb;
    transition: all 0.2s;
}

.fi-fo-file-upload:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.fi-fo-file-upload.fi-dragging {
    border-color: #3b82f6;
    background: #dbeafe;
    transform: scale(1.02);
}

.fi-fo-file-upload-icon {
    width: 3rem;
    height: 3rem;
    margin: 0 auto 1rem;
    color: #6b7280;
}
```

### Form Layout

```css
/* Stacked (default) */
.fi-form-stacked .fi-fo-field {
    margin-bottom: 1.5rem;
}

/* Inline */
.fi-form-inline {
    display: flex;
    gap: 1rem;
    align-items: end;
}

/* Grid */
.fi-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

/* Two-column */
.fi-form-two-column {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1rem;
    align-items: start;
}
```

---

## Tables & Data

### Table Structure

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
                    <td class="fi-ta-cell">John Doe</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### Table Styling

```css
.fi-ta-ctn {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.fi-ta-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.fi-ta-header-cell {
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    white-space: nowrap;
}

.fi-ta-row {
    transition: background 0.15s;
}

.fi-ta-row:hover {
    background: #f9fafb;
}

.fi-ta-row:not(:last-child) {
    border-bottom: 1px solid #f3f4f6;
}

.fi-ta-cell {
    padding: 1rem;
    color: #111827;
}
```

### Sortable Headers

```css
.fi-ta-header-cell-sortable {
    cursor: pointer;
    user-select: none;
}

.fi-ta-header-cell-sortable:hover {
    background: #f3f4f6;
}

.fi-ta-header-cell-sort-icon {
    margin-left: 0.5rem;
    opacity: 0.3;
    transition: opacity 0.15s;
}

.fi-ta-header-cell-sorted .fi-ta-header-cell-sort-icon {
    opacity: 1;
}
```

### Row Selection

```css
.fi-ta-row-selected {
    background: #eff6ff !important;
}

.fi-ta-row-checkbox {
    width: 2.5rem;
    text-align: center;
}
```

### Striped Rows

```css
.fi-ta-table.fi-striped .fi-ta-row:nth-child(even) {
    background: #f9fafb;
}

.fi-ta-table.fi-striped .fi-ta-row:nth-child(even):hover {
    background: #f3f4f6;
}
```

### Compact Table

```css
.fi-ta-table.fi-compact .fi-ta-header-cell,
.fi-ta-table.fi-compact .fi-ta-cell {
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}
```

### Sticky Header

```css
.fi-ta-wrapper {
    max-height: 600px;
    overflow-y: auto;
}

.fi-ta-header-cell {
    position: sticky;
    top: 0;
    z-index: 10;
}
```

### Empty State

```css
.fi-ta-empty-state {
    padding: 3rem;
    text-align: center;
}

.fi-ta-empty-state-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    color: #d1d5db;
}

.fi-ta-empty-state-heading {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.fi-ta-empty-state-description {
    color: #6b7280;
}
```

### Loading State

```css
.fi-ta-row.fi-loading {
    position: relative;
}

.fi-ta-row.fi-loading::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.5),
        transparent
    );
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    to { transform: translateX(100%); }
}
```

---

## Modals & Overlays

### Modal Structure

```html
<div class="fi-modal-overlay">
    <div class="fi-modal-window">
        <header class="fi-modal-header">
            <h2 class="fi-modal-heading">Title</h2>
            <button class="fi-modal-close">×</button>
        </header>
        <div class="fi-modal-content">Content</div>
        <footer class="fi-modal-footer">
            <button class="fi-btn">Cancel</button>
            <button class="fi-btn fi-color-primary">Save</button>
        </footer>
    </div>
</div>
```

### Modal Overlay

```css
.fi-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}
```

### Modal Window

```css
.fi-modal-window {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 32rem;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
```

### Modal Sections

```css
.fi-modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.fi-modal-heading {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
}

.fi-modal-close {
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 0.15s;
}

.fi-modal-close:hover {
    background: #f3f4f6;
    color: #111827;
}

.fi-modal-content {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
}

.fi-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    background: #f9fafb;
}
```

### Modal Animations

```css
/* Entrance */
.fi-modal-overlay {
    animation: modal-fade-in 0.2s;
}

.fi-modal-window {
    animation: modal-scale-in 0.3s cubic-bezier(0, 0, 0.2, 1);
}

@keyframes modal-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modal-scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
```

### Slide-Over (Side Panel)

```css
.fi-modal-slide-over .fi-modal-overlay {
    justify-content: flex-end;
}

.fi-modal-slide-over .fi-modal-window {
    max-width: 28rem;
    height: 100vh;
    border-radius: 0;
    animation: slide-in 0.3s;
}

@keyframes slide-in {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}
```

### Modal Sizes

```css
.fi-modal-window.fi-width-sm { max-width: 24rem; }
.fi-modal-window.fi-width-md { max-width: 32rem; }
.fi-modal-window.fi-width-lg { max-width: 42rem; }
.fi-modal-window.fi-width-xl { max-width: 56rem; }
.fi-modal-window.fi-width-full { max-width: 90vw; }
```

---

## Navigation

### Sidebar

```css
.fi-sidebar {
    width: 16rem;
    height: 100vh;
    background: white;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
}

.fi-sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.fi-sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.fi-sidebar-item-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    margin: 0.125rem 0.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.15s;
}

.fi-sidebar-item-btn:hover {
    background: #f3f4f6;
    color: #111827;
}

.fi-sidebar-item.fi-active .fi-sidebar-item-btn {
    background: #eff6ff;
    color: #3b82f6;
}

.fi-sidebar-item-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.fi-sidebar-item-label {
    flex: 1;
}

.fi-sidebar-item-badge {
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    background: #f3f4f6;
    border-radius: 0.25rem;
}
```

### Collapsible Sidebar

```css
.fi-sidebar.fi-collapsed {
    width: 4rem;
}

.fi-sidebar.fi-collapsed .fi-sidebar-item-label,
.fi-sidebar.fi-collapsed .fi-sidebar-item-badge {
    display: none;
}

.fi-sidebar.fi-collapsed .fi-sidebar-item-btn {
    justify-content: center;
}
```

### Top Navigation

```css
.fi-topbar {
    height: 4rem;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    gap: 1rem;
}

.fi-topbar-brand {
    font-weight: 700;
    font-size: 1.25rem;
}

.fi-topbar-nav {
    display: flex;
    gap: 0.5rem;
    flex: 1;
}

.fi-topbar-nav-item {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.15s;
}

.fi-topbar-nav-item:hover {
    background: #f3f4f6;
    color: #111827;
}

.fi-topbar-nav-item.fi-active {
    background: #eff6ff;
    color: #3b82f6;
}
```

### Breadcrumbs

```css
.fi-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.fi-breadcrumbs-item {
    color: #6b7280;
    transition: color 0.15s;
}

.fi-breadcrumbs-item:hover {
    color: #111827;
}

.fi-breadcrumbs-item.fi-active {
    color: #111827;
    font-weight: 500;
}

.fi-breadcrumbs-separator {
    color: #d1d5db;
}
```

---

## Badges & Labels

### Badge Base

```css
.fi-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.375rem;
    white-space: nowrap;
}
```

### Badge Variants

```css
/* Primary */
.fi-badge.fi-color-primary {
    background: #dbeafe;
    color: #1e40af;
}

/* Success */
.fi-badge.fi-color-success {
    background: #d1fae5;
    color: #065f46;
}

/* Danger */
.fi-badge.fi-color-danger {
    background: #fee2e2;
    color: #991b1b;
}

/* Warning */
.fi-badge.fi-color-warning {
    background: #fef3c7;
    color: #92400e;
}

/* Gray */
.fi-badge.fi-color-gray {
    background: #f3f4f6;
    color: #374151;
}
```

### Badge Sizes

```css
.fi-badge.fi-size-xs {
    padding: 0.125rem 0.5rem;
    font-size: 0.625rem;
}

.fi-badge.fi-size-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
}

.fi-badge.fi-size-md {
    padding: 0.375rem 0.875rem;
    font-size: 0.875rem;
}
```

### Dot Badge

```css
.fi-badge.fi-dot::before {
    content: '';
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: currentColor;
}
```

### Removable Badge

```css
.fi-badge-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1rem;
    height: 1rem;
    margin-left: 0.25rem;
    border-radius: 50%;
    transition: background 0.15s;
}

.fi-badge-remove:hover {
    background: rgba(0, 0, 0, 0.1);
}
```

---

**Continue to Part 4: Advanced CSS Features**

This covers core component patterns. Next, we'll explore advanced techniques like custom properties, filters, and modern CSS features.
