# Speeches Manager Plugin - Design Style Guide

## Overview

This document outlines the design system and styling guidelines for the Government Speeches Manager WordPress Plugin. The design is based on the Ministry of Social Development and Family Services design system, ensuring consistency and professional appearance across all speech displays.

## Color Palette

### Primary Colors
- **Primary Navy**: `#003366` - Main brand color for headers, titles, and primary elements
- **Accent Teal**: `#4DB6AC` - Secondary brand color for buttons, highlights, and interactive elements
- **White**: `#FFFFFF` - Background for cards and content areas

### Supporting Colors
- **Text Gray**: `#6B7280` - Primary text color for body content
- **Background Gray**: `#F9FAFB` - Light background for meta sections and filters
- **Light Gray**: `#8C8C8C` - Secondary text and subtle elements
- **Border Gray**: `#E5E7EB` - Borders and dividers

### CSS Custom Properties
```css
:root {
  --primary-navy: #003366;
  --accent-teal: #4DB6AC;
  --text-gray: #6B7280;
  --bg-gray: #F9FAFB;
  --light-gray: #8C8C8C;
  --white: #FFFFFF;
  --border-radius: 0.5rem;
  --transition: 0.2s ease;
  --font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
```

## Typography

### Font Family
- **Primary**: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
- **Fallback**: System fonts for optimal performance

### Font Sizes and Weights
- **Page Title**: 2rem, font-weight: 700
- **Card Title**: 1.125rem, font-weight: 600
- **Section Headers**: 1.25rem, font-weight: 600
- **Body Text**: 1rem, line-height: 1.7
- **Meta Text**: 0.875rem, color: var(--text-gray)
- **Small Text**: 0.75rem, color: var(--text-gray)

## Layout System

### Container Structure
- **Max Width**: 1200px
- **Centered**: Auto margins
- **Padding**: 1rem (mobile), 2rem (desktop)
- **Grid Gap**: 1.5rem between elements

### Grid Layouts
- **1 Column**: Single column layout
- **2 Columns**: Auto-fill with 320px minimum width
- **3 Columns**: Responsive (1 col mobile, 2 col tablet, 3 col desktop)

## Component Design

### Speech Cards

#### Structure
```html
<div class="speech-card">
  <div class="speech-header">
    <div class="speech-meta">
      <span class="badge badge-primary">Category</span>
      <span class="speech-date">Date</span>
    </div>
    <h3 class="speech-title">Title</h3>
    <div class="speech-speaker">Speaker Info</div>
  </div>
  <div class="speech-content">
    <div class="speech-event">Event</div>
    <div class="speech-summary">Summary</div>
  </div>
  <div class="speech-actions">
    <a class="btn-primary">Read Full Speech</a>
    <a class="btn-secondary">Download PDF</a>
  </div>
</div>
```

#### Styling
- **Background**: White with subtle shadow
- **Border**: 1px solid #E5E7EB
- **Border Radius**: 0.5rem
- **Hover Effect**: Enhanced shadow (0 10px 25px rgba(0, 0, 0, 0.15))
- **Padding**: 1.5rem for header and content sections

### Badges

#### Primary Badge
- **Background**: var(--primary-navy)
- **Color**: White
- **Shape**: Rounded pill (border-radius: 9999px)
- **Padding**: 0.125rem 0.625rem
- **Font**: 0.75rem, font-weight: 600

#### Secondary Badge
- **Background**: #F3F4F6
- **Color**: #374151
- **Hover**: Transforms to accent teal

### Buttons

#### Primary Button (.btn-primary)
- **Background**: var(--accent-teal)
- **Color**: White
- **Padding**: 0.75rem 2rem
- **Border Radius**: 0.5rem
- **Hover**: Background changes to var(--primary-navy)
- **Transition**: 0.2s ease

#### Secondary Button (.btn-secondary)
- **Background**: White
- **Color**: var(--primary-navy)
- **Border**: 1px solid #D1D5DB
- **Hover**: Background changes to var(--bg-gray), border to accent teal

### Icons
- **Size**: 16px × 16px
- **Color**: Inherits from parent (currentColor)
- **Style**: SVG icons with consistent stroke width
- **Usage**: Inline with text, aligned with flexbox

## Single Speech Page Design

### Layout Structure
```html
<div class="gsm-single-speech-container">
  <div class="gsm-single-speech-wrapper">
    <main class="gsm-single-speech-main">
      <article class="gsm-single-speech">
        <header class="entry-header">
          <h1 class="entry-title">Title</h1>
        </header>
        <div class="gsm-single-speech-meta">Meta Information</div>
        <div class="entry-content">Content</div>
        <footer class="entry-footer">Download Button</footer>
      </article>
    </main>
  </div>
</div>
```

### Key Features
- **Full Width**: Overrides theme sidebar for optimal reading
- **Meta Section**: Highlighted background with structured information
- **Typography**: Larger text (1.1rem) with increased line-height (1.7)
- **Download Button**: Prominent call-to-action with hover effects

## Filter Components

### Category Filter
- **Background**: var(--bg-gray)
- **Border**: 1px solid #E5E7EB
- **Layout**: Flexbox with responsive wrapping
- **Form Elements**: Consistent styling with focus states

### Filter Selects
- **Padding**: 0.5rem 0.75rem
- **Border**: 1px solid #D1D5DB
- **Focus**: 2px outline in accent teal
- **Min Width**: 150px for consistency

## Archive Section

### Design
- **Background**: var(--primary-navy)
- **Text Color**: White
- **Layout**: Centered content with action buttons
- **Purpose**: Navigation between current and archived speeches

## Responsive Design

### Mobile Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Mobile Adaptations
- **Single Column**: All layouts become single column
- **Full Width Buttons**: Buttons expand to full width
- **Reduced Padding**: Container padding reduced to 1rem
- **Stacked Actions**: Button groups stack vertically

## Accessibility Features

### Focus States
- **Outline**: 2px solid var(--accent-teal)
- **Offset**: 2px from element
- **Target**: All interactive elements

### Reduced Motion
- **Media Query**: `@media (prefers-reduced-motion: reduce)`
- **Effect**: Disables transitions for users who prefer reduced motion

### Print Styles
- **Hidden Elements**: Actions, filters, and pagination hidden
- **Card Styling**: Simplified borders for print
- **Content Focus**: Emphasizes content over interactive elements

## Animation and Transitions

### Standard Transitions
- **Duration**: 0.2s
- **Easing**: ease
- **Properties**: box-shadow, background-color, border-color, transform

### Hover Effects
- **Cards**: Shadow enhancement
- **Buttons**: Background color changes
- **Links**: Color transitions
- **Badges**: Color and background changes

## Implementation Guidelines

### CSS Specificity
- **High Specificity**: Uses `!important` declarations to override theme styles
- **Targeting**: Specific selectors to avoid conflicts
- **Override Strategy**: Designed to work with any WordPress theme

### Performance Considerations
- **CSS Variables**: Efficient color management
- **Minimal Animations**: Lightweight transitions
- **Optimized Selectors**: Efficient CSS targeting

### Browser Support
- **Modern Browsers**: Full feature support
- **Fallbacks**: Graceful degradation for older browsers
- **Progressive Enhancement**: Core functionality works without advanced features

## Usage Examples

### Basic Speech Display
```php
// Shortcode usage
[speeches_manager columns="2" excerpt_length="25"]
```

### Custom Styling Override
```css
/* Override primary color */
.speeches-container {
  --primary-navy: #your-color;
}
```

### Custom Card Styling
```css
/* Custom card appearance */
.speech-card {
  border-left: 4px solid var(--accent-teal);
}
```

## Maintenance Notes

### Color Updates
- Update CSS custom properties in `:root` for global changes
- Maintain contrast ratios for accessibility
- Test across different backgrounds

### Layout Modifications
- Grid system is flexible and responsive
- Breakpoints can be adjusted in media queries
- Component spacing uses consistent rem units

### Adding New Components
- Follow existing naming conventions
- Use CSS custom properties for colors
- Include hover and focus states
- Test responsive behavior

---

*This design system ensures consistency, accessibility, and professional appearance across all speech displays while maintaining flexibility for customization.*
