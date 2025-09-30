# TenderNotices WordPress Plugin

A comprehensive WordPress plugin for displaying tender notices and procurement opportunities on WordPress websites.

## Features

### Core Functionality
- **Custom Post Type**: Dedicated post type for tender notices
- **Chronological Ordering**: Tender notices displayed in reverse chronological order
- **Flexible Layouts**: Single and two-column display options
- **PDF Management**: Upload, store, and download PDF documents
- **Responsive Design**: Mobile-optimized interface
- **Admin Interface**: Comprehensive management dashboard

### Display Options
- **Single Column Layout**: Full-width cards with detailed information
- **Two Column Layout**: Compact cards for space-efficient display
- **Custom Templates**: Override default templates for custom styling
- **Widget Support**: Display tender notices in sidebars
- **Shortcode Support**: Embed tender notices anywhere

### Data Management
- **Rich Metadata**: Issue date, closing date, contact information, tender value
- **Categorization**: Organize tenders by category and status
- **Bulk Operations**: Manage multiple tenders efficiently
- **Search & Filter**: Advanced filtering capabilities

### Security & Performance
- **Secure File Access**: Protected PDF downloads
- **Database Optimization**: Efficient queries and caching
- **WordPress Standards**: Full compliance with WordPress coding standards
- **Accessibility**: WCAG 2.1 AA compliance

## Installation

1. Upload the plugin files to `/wp-content/plugins/tender-notices/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings in the Tender Notices settings page

## Usage

### Creating Tender Notices

1. Go to **Tender Notices** in your WordPress admin
2. Click **Add New Tender Notice**
3. Fill in the tender details:
   - Title and description
   - Issue and closing dates
   - Contact information
   - Tender value and reference
   - Upload PDF document
4. Assign categories and status
5. Publish the tender notice

### Displaying Tender Notices

#### Shortcode
Use the shortcode to display tender notices on any page or post:

```
[tender_notices]
```

**Shortcode Parameters:**
- `columns` - Number of columns (1 or 2)
- `posts_per_page` - Number of notices to display
- `show_excerpt` - Show/hide excerpt (true/false)
- `excerpt_length` - Number of words in excerpt
- `orderby` - Sort by field (date, title, etc.)
- `order` - Sort order (ASC/DESC)
- `show_pagination` - Show pagination (true/false)

**Examples:**
```
[tender_notices columns="2" posts_per_page="6"]
[tender_notices show_pagination="true"]
```

#### Widget
Add the Tender Notices widget to your sidebar:
1. Go to **Appearance > Widgets**
2. Drag the **Tender Notices Widget** to your desired sidebar
3. Configure the widget settings

#### Archive Page
The plugin automatically creates an archive page at `/tender-notices/` where all tender notices are displayed.

### Single Tender Notice
Individual tender notices are accessible at `/tender-notices/[tender-name]/`

## Configuration

### Settings
Go to **Tender Notices > Settings** to configure:
- Default layout (single or two columns)
- Posts per page
- Excerpt settings
- Display options

### Categories and Status
- **Categories**: Organize tenders by type (e.g., Construction, Services, Supplies)
- **Status**: Track tender status (e.g., Active, Closed, Awarded, Cancelled)

## Customization

### CSS Customization
The plugin uses CSS custom properties for easy theming:

```css
:root {
  --primary-navy: #003366;
  --accent-teal: #4DB6AC;
  --text-gray: #6B7280;
  --bg-gray: #F9FAFB;
  --border-radius: 0.5rem;
}
```

### Template Override
Create custom templates by copying files from the plugin's `templates/` directory to your theme:
- `single-tender-notice.php`
- `archive-tender-notice.php`

### Hooks and Filters
The plugin provides numerous hooks for customization:

```php
// Modify tender notice query
add_filter('tender_notices_query_args', function($args) {
    $args['meta_key'] = '_tender_value';
    $args['meta_value'] = '100000';
    return $args;
});

// Customize tender notice data
add_filter('tender_notices_data', function($data, $post_id) {
    $data['custom_field'] = get_post_meta($post_id, '_custom_field', true);
    return $data;
}, 10, 2);
```

## API Reference

### Functions
- `TenderNotices_Frontend::get_tender_notice_data($post_id)` - Get tender notice data
- `TenderNotices_Frontend::format_date($date, $format)` - Format date for display
- `TenderNotices_Frontend::is_tender_expired($closing_date)` - Check if tender is expired
- `TenderNotices_PDF_Manager::get_download_url($tender_id, $pdf_id)` - Get secure download URL

### Shortcodes
- `[tender_notices]` - Display tender notices
- `[tender_notice id="123"]` - Display single tender notice
- `[tender_notice slug="tender-slug"]` - Display single tender notice by slug

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Security

- All user inputs are sanitized and validated
- File uploads are restricted to PDF files only
- Download links are secured with nonces
- SQL queries use prepared statements

## Performance

- Optimized database queries
- Efficient file handling
- Minimal JavaScript footprint
- CSS and JS are minified for production

## Troubleshooting

### Common Issues

**PDF not uploading:**
- Check file size limits (default 10MB)
- Ensure file is a valid PDF
- Check server upload limits

**Tender notices not displaying:**
- Verify the shortcode syntax
- Check if tender notices are published
- Clear any caching plugins

**Styling issues:**
- Check for theme conflicts
- Verify CSS custom properties are supported
- Clear browser cache

### Debug Mode
Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and feature requests, please contact the plugin developer or submit an issue through the appropriate channels.

## Changelog

### Version 1.0.0
- Initial release
- Core functionality implementation
- Custom post type and admin interface
- Frontend display with multiple layouts
- PDF management system
- Widget and shortcode support
- Responsive design implementation

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Praeco Services based on the Ministry of Social Development and Family Services design system.
