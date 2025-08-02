# Student Result Management System - WordPress Plugin

A comprehensive WordPress plugin for managing student results with free and premium features. Perfect for schools, colleges, and educational institutions.

## Features

### Free Features ✅
- **Student Management**: Add, edit, and manage student details
- **Result Management**: Add and manage exam results with subject-wise marks
- **Result Lookup**: Public result lookup by roll number
- **Basic Export**: Export student data as CSV
- **Dashboard Analytics**: View statistics and recent activity
- **Responsive Design**: Beautiful UI that works on all devices

### Premium Features 💎
- **PDF Result Cards**: Generate beautiful PDF result cards
- **CSV Import/Export**: Bulk import students and results via CSV
- **Student Profile Images**: Upload and manage student photos
- **Advanced Analytics**: Detailed performance reports and charts
- **Email Notifications**: Automated result notifications
- **Data Backup & Restore**: Complete data management tools
- **Custom Templates**: Multiple result card templates
- **Role-based Access**: Advanced user permissions

## Installation

1. **Upload the Plugin**
   - Download the `student-result-management` folder
   - Upload it to your WordPress `/wp-content/plugins/` directory
   - Or install via WordPress admin by uploading the ZIP file

2. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Student Result Management System"
   - Click "Activate"

3. **Initial Setup**
   - The plugin will automatically create necessary database tables
   - The user who activates the plugin becomes the owner with full premium access
   - Configure settings in Student Results → Settings

## Quick Start Guide

### 1. Add Students
- Navigate to Student Results → Students
- Click "Add New" to add student details
- Fill in roll number, name, class, and other information
- Save the student record

### 2. Add Results
- Go to Student Results → Results
- Click "Add New" to add exam results
- Select student, enter exam details and marks
- Add subject-wise marks if needed
- The system automatically calculates grades and percentage

### 3. Frontend Result Lookup
- Add the shortcode `[student_result_lookup]` to any page or post
- Students can search their results using roll number
- Results display beautifully with all exam details

### 4. Configure Settings
- Visit Student Results → Settings
- Configure school information, grading system
- Set passing marks and other preferences

## Shortcodes

### Result Lookup Form
```php
[student_result_lookup]
```

**Attributes:**
- `style`: `default`, `modern`, or `classic`

**Example:**
```php
[student_result_lookup style="modern"]
```

## Database Tables

The plugin creates these tables:
- `wp_srm_students`: Student information
- `wp_srm_results`: Exam results and marks
- `wp_srm_settings`: Plugin configuration

## Premium License System

The plugin includes a built-in licensing system:
- **Plugin Owner**: Full access to all features (set during activation)
- **Free Users**: Access to basic features only
- **Premium Users**: Full access after license activation

### Becoming Premium Owner
The WordPress user who first activates the plugin automatically becomes the owner with full premium access.

## Grade System

Default grade calculation:
- **A+**: 90-100%
- **A**: 80-89%
- **B+**: 70-79%
- **B**: 60-69%
- **C+**: 50-59%
- **C**: 40-49%
- **F**: 0-39%

## File Structure

```
student-result-management/
├── student-result-management.php     # Main plugin file
├── README.md                         # This file
├── assets/
│   ├── css/
│   │   ├── admin.css                 # Admin styling
│   │   └── frontend.css              # Frontend styling
│   ├── js/
│   │   ├── admin.js                  # Admin functionality
│   │   └── frontend.js               # Frontend functionality
│   ├── images/                       # Plugin images
│   └── sample.csv                    # Sample CSV for import
├── includes/
│   ├── admin/
│   │   ├── dashboard.php             # Admin dashboard
│   │   ├── students.php              # Student management
│   │   ├── results.php               # Result management
│   │   ├── import-export.php         # Import/Export tools
│   │   ├── settings.php              # Plugin settings
│   │   └── premium.php               # Premium features page
│   └── frontend/
│       └── result-lookup.php         # Frontend result lookup
└── languages/                        # Translation files
```

## Customization

### CSS Customization
Add custom styles to your theme's `style.css`:

```css
/* Customize result lookup form */
.srm-result-lookup {
    /* Your custom styles */
}

/* Customize result cards */
.srm-result-card {
    /* Your custom styles */
}
```

### PHP Hooks
The plugin provides several action and filter hooks:

```php
// Before displaying results
do_action('srm_before_display_results', $student, $results);

// After student is saved
do_action('srm_student_saved', $student_id, $student_data);

// Filter result data before display
$results = apply_filters('srm_result_data', $results, $student_id);
```

## CSV Import Format

For bulk student import, use this CSV format:
```csv
roll_number,first_name,last_name,email,phone,class,section,date_of_birth
2023001,John,Doe,john.doe@email.com,1234567890,10,A,2005-01-15
```

**Required fields:**
- `roll_number`: Unique identifier
- `first_name`: Student's first name
- `last_name`: Student's last name
- `class`: Student's class/grade

## Security Features

- **Nonce Verification**: All forms use WordPress nonces
- **Data Sanitization**: All input is sanitized and validated
- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Prevention**: All output is escaped
- **Access Control**: Role-based permissions

## Performance

- **Optimized Queries**: Efficient database queries with proper indexing
- **AJAX Loading**: Smooth user experience with AJAX functionality
- **Lazy Loading**: Images loaded only when needed
- **Caching Ready**: Compatible with WordPress caching plugins

## Browser Support

- **Chrome**: Latest 2 versions
- **Firefox**: Latest 2 versions
- **Safari**: Latest 2 versions
- **Edge**: Latest 2 versions
- **Mobile**: iOS Safari, Android Chrome

## Troubleshooting

### Common Issues

**Plugin won't activate:**
- Check PHP version (7.4+ required)
- Verify WordPress version (5.0+ required)
- Check file permissions

**Database tables not created:**
- Check database permissions
- Verify `wp_` prefix in wp-config.php
- Try deactivating and reactivating

**Results not displaying:**
- Check if students and results exist
- Verify shortcode syntax
- Check for JavaScript errors in browser console

**CSV import not working:**
- Verify file format (CSV with proper headers)
- Check file size limits
- Ensure premium access

### Debug Mode

Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and feature requests:
- Check the troubleshooting section above
- Review plugin documentation
- Contact your system administrator

## Changelog

### Version 1.0.0
- Initial release
- Complete student and result management system
- Frontend result lookup with shortcode
- Beautiful responsive design
- Premium feature framework
- CSV import/export functionality
- Comprehensive admin dashboard

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed with ❤️ for educational institutions worldwide.

**Technologies Used:**
- WordPress Plugin API
- jQuery for enhanced interactions
- CSS Grid and Flexbox for modern layouts
- SVG icons for crisp graphics
- WordPress Coding Standards