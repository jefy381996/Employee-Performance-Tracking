# License Key Creation and Management Guide

## Overview
This guide explains how to create domain-bound license keys for premium users of your Student Result Management plugin. The new system uses a file-based approach where each premium user gets a unique license file that's bound to their specific domain.

## How the System Works

1. **Free Users**: Download the plugin without any license file
2. **Premium Users**: Provide their domain name, and you create a custom license file for them
3. **Domain Binding**: Each license key is tied to a specific domain and won't work on other sites
4. **Owner Access**: Your special key "Bismillah^512" works on any domain

## Step-by-Step Process

### Step 1: User Requests a License
When a user wants to buy the premium version:

1. They go to **WordPress Admin → Student Result Management → Request License**
2. They fill out the form with:
   - Name
   - Email
   - Phone
   - Domain name (where they want to activate the plugin)
3. The form automatically sends an email to `jaffar381996152@gmail.com` with their details

### Step 2: You Create the License Key
When you receive the email request:

1. **Extract the domain name** from the email (e.g., `example.com`)
2. **Create a unique license key** using this format:
   ```
   [RANDOM_CHARS][SPECIAL_CHAR][RANDOM_CHARS].[DOMAIN_NAME]
   ```
   
   **Example**: `XYGh675*UGTFM.example.com`

3. **License Key Rules**:
   - Use any combination of letters and numbers for the random parts
   - Include at least one special character (*, #, @, $, %, etc.)
   - End with a dot (.) followed by the exact domain name
   - Total length should be around 15-20 characters

### Step 3: Create the License File
For each premium user:

1. **Create a new file** named `license.key`
2. **Add only the license key** to the file (no extra spaces or lines)
3. **Example content**:
   ```
   XYGh675*UGTFM.example.com
   ```

### Step 4: Send to the User
Send the `license.key` file to the user via email or file sharing.

### Step 5: User Installation
The user should:

1. **Upload the plugin** to their WordPress site
2. **Place the `license.key` file** in the plugin's root directory:
   ```
   wp-content/plugins/student-result-management/license.key
   ```
3. **Activate the plugin** in WordPress
4. **Go to Premium Features** and click "Activate License" (the key will be automatically detected)

## File Structure Example

```
student-result-management/
├── student-result-management.php
├── license.key                    ← User's license file
├── includes/
├── assets/
└── ... (other plugin files)
```

## License Key Examples

| Domain | License Key |
|--------|-------------|
| `mywebsite.com` | `ABc123#XYZ789.mywebsite.com` |
| `school.edu` | `DEF456@GHI012.school.edu` |
| `college.org` | `JKL789$MNO345.college.org` |

## Validation Process

The plugin automatically:

1. **Reads the license file** when activated
2. **Extracts the domain** from the key (part after the dot)
3. **Compares it** with the current site's domain
4. **Grants premium access** if they match
5. **Shows error** if domains don't match

## Security Features

- **Domain Binding**: Each key only works on one specific domain
- **File-Based**: No database storage of keys (more secure)
- **Automatic Validation**: No manual intervention needed
- **Owner Override**: Your key "Bismillah^512" works everywhere

## Troubleshooting

### User Can't Activate License
- Check if the domain in the license key matches their actual domain
- Ensure the `license.key` file is in the correct location
- Verify the file contains only the license key (no extra spaces)

### License Not Working
- Confirm the domain name is exactly correct (including www if needed)
- Check that the license file is readable by the web server
- Verify the license key format is correct

### Multiple Users on Same Domain
- Each user on the same domain can use the same license key
- The key is bound to the domain, not individual users

## Owner Access

Your special key "Bismillah^512":
- Works on any domain
- Grants full owner access
- Can be entered through the Premium Features page
- Doesn't require a license file

## Important Notes

1. **Keep license keys secure** - don't share them publicly
2. **Domain must match exactly** - including subdomains
3. **One key per domain** - users on the same domain can share
4. **File permissions** - ensure the license file is readable
5. **Backup license files** - keep copies of all issued keys

## Email Template for Users

When sending the license file to users, include:

```
Dear [User Name],

Thank you for purchasing the Student Result Management plugin premium version.

Your license key has been created for domain: [DOMAIN_NAME]

Please follow these steps to activate:

1. Download the attached license.key file
2. Upload it to your plugin directory: wp-content/plugins/student-result-management/
3. Activate the plugin in WordPress
4. Go to Student Result Management → Premium Features
5. Click "Activate License"

The license is bound to your domain and will only work on [DOMAIN_NAME].

If you have any issues, please contact me.

Best regards,
Jaffar Abbas
```

This system ensures that each premium user gets a unique, domain-bound license that cannot be shared or used on multiple sites.