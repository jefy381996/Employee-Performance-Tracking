# 🧪 Localhost Testing Guide

## Overview
This guide explains how to test the Student Result Management plugin's license system on localhost.

## Prerequisites
- WordPress installed on localhost (e.g., XAMPP, WAMP, MAMP)
- Plugin installed and activated
- Access to WordPress admin panel

## Testing Steps

### 1. Access the Test Script
1. Go to your WordPress admin panel
2. Navigate to: **Student Results → Test License System**
3. Or access directly: `http://localhost/your-site/wp-admin/admin.php?page=srm-test-license`

### 2. Check Current System Status
The test script will show:
- **Domain Information**: Current domain (should be 'localhost')
- **License File Status**: Whether license.key exists
- **Current License Status**: Active license information
- **Premium Access**: Whether premium features are unlocked

### 3. Test License Key Validation
The script tests various license keys:
- ✅ **Valid Keys**: Keys that follow the 13-digit format
- ⚠️ **Wrong Domain**: Valid keys for different domains
- ❌ **Invalid Keys**: Keys that don't follow the format

### 4. Test License Activation
1. Enter a valid license key in the activation form
2. Click "Test Activation"
3. Check if the license status changes
4. Verify premium access is granted

### 5. Test License File Creation
1. Enter a license key in the file creation form
2. Click "Create Test File"
3. Check if the license.key file is created
4. Verify the license is automatically detected

### 6. Test Student Limits
- Check current student count
- Verify remaining slots for free users
- Test if you can add students

### 7. Test Cleanup
1. Click "Deactivate License"
2. Verify the license file is removed
3. Check if you return to free version
4. Ensure free features still work

## Valid License Keys for Localhost

### Format
```
[13 characters].localhost
```

### Character Requirements
- **1st letter**: B, J, N, A, F, or T
- **4th letter**: X, G, K, D, E, or P
- **8th, 9th, or 10th letter**: Special character (!@#$%^&* etc.)
- **13th letter**: B, G, N, K, F, or P

### Example Keys
```
BJKmNpQrStU*localhost
FGHjKlMnOpQ#localhost
TUVwXyZaBcD@localhost
NJKmNpQrStU!localhost
```

### Owner Key
```
Bismillah^512
```
(Works on any domain)

## Testing Scenarios

### Scenario 1: Fresh Installation
1. Install plugin without license file
2. Check that you're in free mode
3. Verify 20-student limit
4. Test license request form

### Scenario 2: Premium License Activation
1. Create license.key file with valid key
2. Check if license is automatically detected
3. Verify premium features are unlocked
4. Test unlimited student addition

### Scenario 3: Wrong Domain License
1. Use a key for a different domain
2. Verify the license is rejected
3. Check error message

### Scenario 4: Invalid License Format
1. Use a key that doesn't follow 13-digit format
2. Verify the license is rejected
3. Check error message

### Scenario 5: License Deactivation
1. Activate a valid license
2. Deactivate the license
3. Verify return to free mode
4. Check that free features still work

## Manual Testing

### Test License File Creation
1. Create a text file named `license.key`
2. Add a valid license key to the file
3. Upload to plugin directory
4. Check if license is detected

### Test License Key Format
```php
// Valid examples
BJKmNpQrStU*localhost
FGHjKlMnOpQ#localhost
TUVwXyZaBcD@localhost

// Invalid examples
ABC123!DEF456.localhost  // Wrong format
BJKmNpQrStU*example.com  // Wrong domain
BJKmNpQrStUlocalhost     // Missing dot
```

### Test Domain Detection
The plugin detects localhost using:
- `$_SERVER['HTTP_HOST']`
- `$_SERVER['SERVER_NAME']`
- Removes 'www.' prefix and port numbers

## Troubleshooting

### Common Issues

#### "License file not found"
- Check if license.key file exists in plugin directory
- Verify file permissions (should be 644)
- Check file name spelling

#### "Invalid license format"
- Verify the key follows 13-digit format
- Check character requirements for each position
- Ensure domain part matches 'localhost'

#### "License bound to different domain"
- Use a key specifically for 'localhost'
- Check the domain part of the license key
- Generate a new key for localhost

#### "Premium features not unlocked"
- Check if license is properly activated
- Verify license file exists and is readable
- Clear any caching plugins

### Debug Information
The test script provides:
- Current domain information
- License file status
- License validation results
- Activation test results
- Student limit information

## Expected Results

### Free Mode (No License)
- Status: "free"
- Student limit: 20
- Premium features: Disabled
- Contact banner: Visible

### Premium Mode (Valid License)
- Status: "premium"
- Student limit: Unlimited
- Premium features: Enabled
- Contact banner: Hidden

### Owner Mode (Owner Key)
- Status: "owner"
- All features: Enabled
- Admin access: Full
- Contact banner: Hidden

## Next Steps

After testing on localhost:
1. **Verify all features work correctly**
2. **Test license activation/deactivation**
3. **Check student limits**
4. **Test premium features**
5. **Remove test files before production**

## Contact Information
For support with testing:
- **WhatsApp**: +923083430923
- **Email**: jaffar381996152@gmail.com

---

*This guide is for testing purposes only. Remove test files before using in production.*