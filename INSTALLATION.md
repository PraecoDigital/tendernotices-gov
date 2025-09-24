# TenderNotices Plugin Installation Guide

## Installation Methods

### Method 1: Upload via WordPress Admin (Recommended)

1. **Download the Plugin**
   - Download the `tender-notices.zip` file
   - Do not extract the zip file

2. **Access WordPress Admin**
   - Log in to your WordPress admin dashboard
   - Navigate to **Plugins > Add New**

3. **Upload the Plugin**
   - Click **Upload Plugin** button
   - Choose the `tender-notices.zip` file
   - Click **Install Now**

4. **Activate the Plugin**
   - After installation, click **Activate Plugin**
   - The plugin will be ready to use

### Method 2: Manual Installation via FTP

1. **Extract the Plugin**
   - Extract the `tender-notices.zip` file
   - You should see a folder structure with all plugin files

2. **Upload via FTP**
   - Connect to your website via FTP
   - Navigate to `/wp-content/plugins/`
   - Upload the entire `tender-notices` folder

3. **Activate the Plugin**
   - Go to **Plugins** in your WordPress admin
   - Find "TenderNotices" and click **Activate**

## Post-Installation Setup

### 1. Configure Settings
- Go to **Tender Notices > Settings**
- Set your preferred default layout (single or two columns)
- Configure posts per page and excerpt settings

### 2. Create Categories and Status
- Go to **Tender Notices > Categories** to create tender categories
- Go to **Tender Notices > Status** to create tender statuses

### 3. Add Your First Tender Notice
- Go to **Tender Notices > Add New**
- Fill in the tender details:
  - Title and description
  - Issue and closing dates
  - Tender number and reference
  - Pre-bid meeting information (if applicable)
  - Site visit information (if applicable)
  - Contact details
  - Upload PDF document
- Publish the tender notice

## Display Options

### Shortcode Usage
Add tender notices to any page or post using shortcodes:

```
[tender_notices]
[tender_notices columns="2" posts_per_page="6"]
[tender_notices category="construction" show_filters="true"]
```

### Widget Usage
- Go to **Appearance > Widgets**
- Add the "Tender Notices Widget" to your sidebar
- Configure the widget settings

### Archive Page
- The plugin automatically creates an archive page at `/tender-notices/`
- This displays all published tender notices

## System Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Browser**: Modern browsers (Chrome, Firefox, Safari, Edge)

## Troubleshooting

### Common Issues

**Plugin won't activate:**
- Check PHP version (requires 7.4+)
- Check for plugin conflicts
- Verify file permissions

**PDF uploads not working:**
- Check file size limits in WordPress settings
- Verify upload directory permissions
- Ensure only PDF files are being uploaded

**Styling issues:**
- Clear any caching plugins
- Check for theme conflicts
- Verify CSS files are loading

### Getting Help

If you encounter issues:
1. Check the WordPress error logs
2. Deactivate other plugins to test for conflicts
3. Switch to a default theme temporarily
4. Contact the plugin developer for support

## Features Overview

### Core Features
- ✅ Custom post type for tender notices
- ✅ Admin interface for management
- ✅ Frontend display with multiple layouts
- ✅ PDF upload and download functionality
- ✅ Responsive design
- ✅ Widget and shortcode support

### Display Information
- ✅ Title and tender number
- ✅ Issue and closing dates
- ✅ Pre-bid meeting information (with mandatory/online indicators)
- ✅ Site visit information (with mandatory indicator)
- ✅ Download buttons for PDF documents
- ✅ Contact information
- ✅ Categories and status

### Advanced Features
- ✅ Filtering and search
- ✅ Pagination
- ✅ Custom templates
- ✅ SEO optimization
- ✅ Accessibility compliance
- ✅ Mobile responsive design

## Support

For technical support or feature requests, please contact the plugin developer or refer to the plugin documentation.

---

**Plugin Version**: 1.0.0  
**Last Updated**: September 2024  
**Compatibility**: WordPress 5.0+
