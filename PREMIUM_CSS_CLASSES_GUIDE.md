# 🎨 Premium CSS Classes - Quick Reference Guide

## Overview
This guide provides a quick reference for all premium CSS classes and utilities added to the school management system. Use these classes to maintain consistency and leverage the modern design system.

---

## 🎨 COLOR SYSTEM

### CSS Variables
```css
/* Primary Colors (Indigo) */
--primary-50 to --primary-900

/* Secondary Colors (Teal) */
--secondary-50 to --secondary-900

/* Semantic Colors */
--success-500: #10B981
--warning-500: #F59E0B
--danger-500: #EF4444
--info-500: #3B82F6

/* Neutral Colors */
--neutral-50 to --neutral-900
```

### Usage Example
```html
<div style="background: var(--primary-600); color: white;">
  Premium Content
</div>
```

---

## 🔘 BUTTON CLASSES

### Primary Buttons
```html
<!-- Gradient Primary Button -->
<button class="btn btn-primary">Save Changes</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Cancel</button>

<!-- Success Button -->
<button class="btn btn-success">Approve</button>

<!-- Danger Button -->
<button class="btn btn-danger">Delete</button>
```

### Button Sizes
```html
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary">Medium (Default)</button>
<button class="btn btn-primary btn-lg">Large</button>
```

**Features:**
- Gradient backgrounds
- Premium shadows
- Hover lift effect (translateY(-2px))
- Smooth transitions
- Disabled states with opacity

---

## 📦 CARD COMPONENTS

### Standard Card
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Card Title</h3>
  </div>
  <div class="card-body">
    Card content goes here
  </div>
</div>
```

**Features:**
- 12px border radius
- Medium shadow
- Hover lift effect
- Gradient header background

### Glass Card
```html
<div class="card card-glass">
  <div class="card-body">
    Glassmorphism effect with backdrop blur
  </div>
</div>
```

**Features:**
- Translucent background
- Backdrop blur (10px)
- Semi-transparent border
- Premium shadow

### Premium Card
```html
<div class="card card-premium">
  <div class="card-body">
    Premium gradient card with colored shadow
  </div>
</div>
```

**Features:**
- Gradient background
- Colored border
- Primary shadow effect

---

## ✨ GLASSMORPHISM EFFECTS

### Glass Effect Classes
```html
<!-- Standard Glass Effect -->
<div class="glass-effect">
  Translucent with 10px blur
</div>

<!-- Strong Glass Effect -->
<div class="glass-effect-strong">
  More opaque with 16px blur
</div>

<!-- Dark Glass Effect -->
<div class="glass-effect-dark">
  Dark translucent background
</div>
```

**CSS Properties:**
- `background: rgba(255, 255, 255, 0.25)`
- `backdrop-filter: blur(10px)`
- `-webkit-backdrop-filter: blur(10px)`
- `border: 1px solid rgba(255, 255, 255, 0.3)`

---

## 🎨 GRADIENT BACKGROUNDS

### Gradient Classes
```html
<!-- Primary Gradient -->
<div class="bg-gradient-primary">
  Indigo to purple gradient
</div>

<!-- Secondary Gradient -->
<div class="bg-gradient-secondary">
  Teal gradient
</div>

<!-- Success Gradient -->
<div class="bg-gradient-success">
  Green gradient
</div>

<!-- Ocean Gradient -->
<div class="bg-gradient-ocean">
  Blue to teal gradient
</div>

<!-- Sunset Gradient -->
<div class="bg-gradient-sunset">
  Orange to red gradient
</div>
```

---

## 🌟 SHADOW UTILITIES

### Shadow Classes
```html
<!-- Premium Shadows -->
<div class="shadow-premium">Primary colored shadow</div>
<div class="shadow-premium-lg">Large primary shadow</div>

<!-- Glow Effects -->
<div class="shadow-glow-primary">Primary glow</div>
<div class="shadow-glow-success">Success glow</div>
```

**Shadow Levels:**
- `--shadow-xs`: Subtle elevation
- `--shadow-sm`: Small shadow
- `--shadow-md`: Medium shadow (default cards)
- `--shadow-lg`: Large shadow (hover states)
- `--shadow-xl`: Extra large shadow
- `--shadow-2xl`: Maximum elevation

---

## 🎭 HOVER EFFECTS

### Hover Lift
```html
<div class="hover-lift">
  Lifts up on hover with shadow
</div>
```

**Effect:** `translateY(-4px)` + enhanced shadow

### Hover Scale
```html
<div class="hover-scale">
  Scales up slightly on hover
</div>
```

**Effect:** `scale(1.05)`

### Hover Glow
```html
<div class="hover-glow">
  Glows with primary color on hover
</div>
```

**Effect:** Colored shadow appears on hover

---

## 🔄 ANIMATIONS

### Loading Animations
```html
<!-- Skeleton Loader -->
<div class="skeleton-loader" style="height: 20px; width: 200px;"></div>

<!-- Pulse Animation -->
<div class="pulse-animation">
  Pulsing element
</div>
```

### Entrance Animations
```html
<!-- Fade In -->
<div class="fade-in">
  Fades in from bottom
</div>

<!-- Slide In Right -->
<div class="slide-in-right">
  Slides in from right
</div>
```

---

## 📝 TEXT EFFECTS

### Gradient Text
```html
<!-- Primary Gradient Text -->
<h1 class="text-gradient-primary">
  Premium Heading
</h1>

<!-- Secondary Gradient Text -->
<h2 class="text-gradient-secondary">
  Teal Gradient Text
</h2>
```

**Features:**
- Background gradient clipped to text
- Transparent fill
- Bold font weight

---

## 🎯 INTERACTIVE CARDS

### Card Interactive
```html
<div class="card card-interactive">
  <div class="card-body">
    Clickable card with enhanced hover
  </div>
</div>
```

**Features:**
- Cursor pointer
- Lift + scale on hover
- Maximum shadow elevation
- Smooth transition

---

## 📜 CUSTOM SCROLLBAR

### Custom Scrollbar
```html
<div class="custom-scrollbar" style="height: 300px; overflow-y: auto;">
  Long content with custom scrollbar
</div>
```

**Features:**
- 8px width
- Rounded track and thumb
- Hover color change to primary
- Smooth transitions

---

## 🔀 TRANSITION UTILITIES

### Transition Classes
```html
<!-- Fast Transition (150ms) -->
<div class="transition-fast">Fast transition</div>

<!-- Base Transition (300ms) -->
<div class="transition-base">Standard transition</div>

<!-- Slow Transition (500ms) -->
<div class="transition-slow">Slow transition</div>
```

---

## 📱 RESPONSIVE UTILITIES

### Mobile Utilities
```html
<!-- Full width on mobile -->
<div class="mobile-full-width">Content</div>

<!-- Center text on mobile -->
<div class="mobile-text-center">Text</div>

<!-- Hide on mobile -->
<div class="mobile-hide">Desktop only</div>
```

**Breakpoint:** `max-width: 767px`

---

## 🎨 BORDER GRADIENTS

### Gradient Border
```html
<div class="border-gradient-primary">
  Element with gradient border
</div>
```

**Features:**
- 2px gradient border
- Transparent inner border
- Primary gradient colors

---

## 💡 USAGE EXAMPLES

### Premium Dashboard Card
```html
<div class="card card-premium hover-lift fade-in">
  <div class="card-header">
    <h3 class="card-title text-gradient-primary">
      Student Statistics
    </h3>
  </div>
  <div class="card-body">
    <div class="info-box">
      <!-- Content -->
    </div>
  </div>
</div>
```

### Glassmorphism Modal
```html
<div class="modal">
  <div class="modal-content glass-effect-strong">
    <div class="modal-header">
      <h4>Premium Modal</h4>
    </div>
    <div class="modal-body">
      <!-- Content -->
    </div>
  </div>
</div>
```

### Interactive Button Group
```html
<div class="btn-group">
  <button class="btn btn-primary hover-glow">
    Primary Action
  </button>
  <button class="btn btn-secondary hover-lift">
    Secondary Action
  </button>
</div>
```

### Loading State
```html
<div class="card">
  <div class="card-body">
    <div class="skeleton-loader" style="height: 20px; margin-bottom: 10px;"></div>
    <div class="skeleton-loader" style="height: 20px; width: 80%;"></div>
  </div>
</div>
```

---

## 🎯 BEST PRACTICES

### 1. Combine Classes Thoughtfully
```html
<!-- Good: Complementary effects -->
<div class="card card-premium hover-lift fade-in">

<!-- Avoid: Conflicting effects -->
<div class="hover-lift hover-scale"> <!-- Don't combine multiple hover transforms -->
```

### 2. Use Appropriate Shadows
- Cards: `--shadow-md`
- Hover states: `--shadow-lg`
- Modals: `--shadow-2xl`
- Buttons: `--shadow-primary`

### 3. Maintain Consistency
- Use gradient buttons for primary actions
- Use glass effects sparingly for premium sections
- Apply hover effects to interactive elements only

### 4. Performance Considerations
- Limit backdrop-filter usage (expensive)
- Use transform and opacity for animations (GPU-accelerated)
- Avoid excessive shadows on many elements

### 5. Accessibility
- Ensure sufficient color contrast
- Maintain focus states
- Don't rely solely on color for information
- Test with screen readers

---

## 🔧 CUSTOMIZATION

### Override CSS Variables
```css
:root {
  --primary-600: #your-color;
  --shadow-primary: your-shadow;
  --transition-base: your-timing;
}
```

### Create Custom Variants
```css
.card-custom {
  background: var(--gradient-ocean);
  box-shadow: var(--shadow-premium);
}

.card-custom:hover {
  transform: translateY(-6px);
}
```

---

## 📚 REFERENCE

### Key Files
- **Modern System:** `assets/css/style.css`
- **Legacy System:** `backend/dist/css/style-main.css`
- **Documentation:** `CSS_MODERNIZATION_SUMMARY.md`

### CSS Variable Naming Convention
- Color: `--{color}-{shade}`
- Shadow: `--shadow-{size}`
- Gradient: `--gradient-{name}`
- Transition: `--transition-{speed}`
- Radius: `--radius-{size}`

---

## 🎓 CONCLUSION

These premium CSS classes provide a comprehensive toolkit for building modern, professional interfaces. Use them consistently throughout the application to maintain the premium design language and deliver an exceptional user experience.

**Remember:** All classes are CSS-only and maintain 100% functionality compatibility!

