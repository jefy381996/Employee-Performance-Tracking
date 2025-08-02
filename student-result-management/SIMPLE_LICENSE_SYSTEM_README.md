# Student Result Management - Simple License System

## Overview

This plugin now uses a simple license key system for premium feature access. No payment processing, no complex licensing - just enter a license key to unlock premium features.

## 🔑 License System

### Owner Key
- **Owner Key**: `Bismillah^512`
- **Access**: Full admin access to all features
- **Usage**: Enter this key to become the plugin owner

### Premium License Keys
- **Format**: Alphanumeric with special characters (8-32 characters)
- **Validation**: Must contain at least one letter and one number
- **Access**: All premium features unlocked

### Free Users
- **Access**: Basic features only
- **Features**: Student management, result management, basic dashboard

## 🚀 How It Works

### 1. Plugin Owner Setup
1. Install the plugin
2. Go to **Student Results > Premium Features**
3. Enter the owner key: `Bismillah^512`
4. Click "Activate License"
5. You now have full owner access

### 2. Premium User Setup
1. Contact the plugin owner to request a license key
2. Go to **Student Results > Premium Features**
3. Enter your license key
4. Click "Activate License"
5. Premium features are now unlocked

### 3. License Management
- **Activate**: Enter license key to unlock features
- **Deactivate**: Remove license to lock features
- **Check Status**: Verify current license status

## 📋 Feature Access

### Free Features (Always Available)
- ✅ Student Management (Add, Edit, Delete) - **Limited to 20 students**
- ✅ Result Management (Add, Edit, Delete)
- ✅ Result Lookup (Frontend)
- ✅ Basic Dashboard
- ✅ Basic Settings

### Premium Features (Require License)
- ✅ Student Management - **Unlimited students**
- ✅ CSV Import/Export
- ✅ Advanced Analytics with Charts
- ✅ Email Notifications System
- ✅ Data Backup & Restore
- ✅ Custom Templates
- ✅ Student Profile Images
- ✅ PDF Certificate Upload
- ✅ Certificate Download

## 🧪 Testing System

### Testing Mode
1. Go to **Student Results > Testing Mode**
2. Choose test role: Free User, Premium User, or Expired License
3. Test feature access and restrictions
4. Deactivate testing to return to normal mode

### Testing Steps
1. Activate testing as "Free User"
2. Try premium features - should see upgrade prompts
3. Switch to "Premium User" - all features should work
4. Test "Expired License" - features should be locked

## 🔧 Technical Details

### License Validation
```php
// Check if user has premium access
$license_manager = new SRM_License_Manager();
$has_premium = $license_manager->has_premium_access();

// Check if user is plugin owner
$is_owner = $license_manager->is_plugin_owner();
```

### License Key Format
- **Length**: 8-32 characters
- **Content**: Alphanumeric + special characters
- **Requirements**: At least one letter and one number
- **Owner Key**: `Bismillah^512` (special case)

### Database Storage
- **License Key**: Stored in `wp_options` as `srm_license_key`
- **License Status**: Stored in `wp_options` as `srm_license_status`
- **Plugin Owner**: Stored in `wp_options` as `srm_plugin_owner`

## 📁 File Structure

```
student-result-management/
├── student-result-management.php (Main plugin file)
├── includes/admin/
│   ├── license-manager.php (License system)
│   ├── enhanced-premium.php (Premium features page)
│   ├── testing-mode.php (Testing system)
│   ├── csv-import-export.php (CSV functionality)
│   ├── advanced-analytics.php (Analytics)
│   ├── email-notifications.php (Email system)
│   ├── data-backup-restore.php (Backup system)
│   └── custom-templates.php (Template system)
└── README files
```

## 🎯 Usage Instructions

### For Plugin Owner
1. Install plugin
2. Enter owner key: `Bismillah^512`
3. Full access to all features
4. Can provide license keys to others

### For Premium Users
1. Contact plugin owner for license key
2. Enter license key in Premium Features page
3. Access all premium features
4. Can deactivate license if needed

### For Free Users
1. Use basic features only
2. See upgrade prompts for premium features
3. Contact owner to request license key

## 🔒 Security Features

- **Nonce Verification**: All forms and AJAX requests
- **Capability Checks**: Proper WordPress capability checks
- **Data Sanitization**: All user inputs sanitized
- **License Validation**: Server-side license verification
- **Owner Protection**: Owner status cannot be removed by deactivation

## 🐛 Troubleshooting

### Common Issues

1. **License Not Working**
   - Check license key format
   - Verify key contains letters and numbers
   - Ensure key is 8-32 characters long

2. **Owner Key Not Working**
   - Verify exact spelling: `Bismillah^512`
   - Check for extra spaces
   - Ensure you're an admin user

3. **Premium Features Locked**
   - Check license status in Premium Features page
   - Verify license key is active
   - Try deactivating and reactivating license

4. **Testing Mode Issues**
   - Go to Testing Mode page
   - Deactivate testing mode
   - Check current license status

## 📞 Support

For support and license requests:
1. Contact the plugin owner
2. Provide your website URL
3. Explain your intended use
4. Receive your unique license key

---

**Note**: This is a simple, effective license system designed for easy management and clear feature access control.