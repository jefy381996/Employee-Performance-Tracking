# Quick License Key Reference Guide

## 🚀 Quick Steps for Creating License Keys

### When You Receive a License Request Email:

1. **Extract the domain** from the email (e.g., `mywebsite.com`)

2. **Create a license key** in this format:
   ```
   [RANDOM_CHARS][SPECIAL_CHAR][RANDOM_CHARS].[DOMAIN_NAME]
   ```
   
   **Example**: `XYGh675*UGTFM.mywebsite.com`

3. **Create a file** named `license.key` with only the license key inside:
   ```
   XYGh675*UGTFM.mywebsite.com
   ```

4. **Send the file** to the user via email

### User Installation Steps:
1. Upload plugin to WordPress
2. Place `license.key` file in: `wp-content/plugins/student-result-management/`
3. Activate plugin
4. Go to Premium Features → Activate License

## 📋 License Key Examples

| Domain | License Key |
|--------|-------------|
| `school.edu` | `ABC123#XYZ789.school.edu` |
| `college.org` | `DEF456@GHI012.college.org` |
| `university.com` | `GHI789$JKL345.university.com` |

## 🔑 Your Owner Key
- **Key**: `Bismillah^512`
- **Works on**: Any domain
- **Access**: Full owner features

## ⚠️ Important Rules

- **Domain must match exactly** (including www if needed)
- **One key per domain** (multiple users on same domain can share)
- **Include special character** (*, #, @, $, %, etc.)
- **End with dot + domain name**
- **Keep keys secure** - don't share publicly

## 🛠️ Troubleshooting

**User can't activate?**
- Check domain matches exactly
- Verify file is in correct location
- Ensure no extra spaces in file

**License not working?**
- Confirm domain name is correct
- Check file permissions
- Verify key format

## 📧 Email Template

```
Dear [Name],

Your license key for [DOMAIN_NAME] is attached.

Installation:
1. Upload license.key to: wp-content/plugins/student-result-management/
2. Activate plugin
3. Go to Premium Features → Activate License

Contact: +923083430923 or jaffar381996152@gmail.com

Best regards,
Jaffar Abbas
```

## 📁 File Structure
```
student-result-management/
├── license.key          ← User's license file
├── student-result-management.php
└── includes/
```

This system ensures each premium user gets a unique, domain-bound license that cannot be shared across multiple sites.