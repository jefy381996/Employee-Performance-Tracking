# License Key Management Guide

## Overview
This guide explains how to create and manage license keys for the Student Result Management plugin's domain-bound licensing system.

## How the System Works

### For Free Users
- Users download the plugin without any license file
- They get access to basic features (limited to 20 students)
- They can request a premium license through the plugin interface

### For Premium Users
- Users provide their information (name, email, phone, domain) through the plugin
- You manually create a domain-bound license key
- You add the license key to a `license.key` file in their plugin folder
- The plugin validates the key against the current domain

## Step-by-Step Process

### 1. User Requests License
When a user wants to buy the premium version:
1. They go to **Premium Features** page in the plugin
2. They click **"Request Premium License"** button
3. They fill out the form with:
   - Name
   - Email
   - Phone
   - Domain name where they want to activate the plugin
4. The request is automatically sent to your email: `jaffar381996152@gmail.com`

### 2. You Create the License Key
When you receive a license request email, follow these steps:

#### License Key Format
Create a license key in this format:
```
[12 random characters][special character][domain name]
```

**Example:** `XYGh675*UGTFM.example.com`

#### Character Requirements:
- **First 12 characters**: Any combination of letters and numbers
- **13th character**: Must be a special character (`*`, `#`, `@`, `$`, `%`, `&`, etc.)
- **Domain part**: The user's domain name (e.g., `example.com`, `mysite.com`)

#### Examples of Valid License Keys:
- `BJKmNpQrStU*example.com`
- `FGHjKlMnOpQ#mysite.com`
- `TUVwXyZaBcD@testdomain.com`

### 3. You Send the License Key to User
Send the license key to the user via email or WhatsApp with these instructions:

```
Your license key: [LICENSE_KEY]

To activate your premium license:

1. Download the plugin files
2. Create a file named "license.key" in the plugin's root folder
3. Add your license key to the license.key file (just the key, no extra text)
4. Upload the plugin to your WordPress site
5. Go to Premium Features page and enter your license key
6. Click "Activate License"

The plugin will automatically validate your key against your domain.
```

### 4. User Activates the License
The user follows these steps:
1. Creates a `license.key` file in the plugin folder
2. Adds the license key to the file
3. Uploads the plugin to their WordPress site
4. Goes to Premium Features page
5. Enters the license key
6. Clicks "Activate License"

## File Structure

### Plugin Folder Structure
```
student-result-management/
├── student-result-management.php
├── license.key                    ← License file (for premium users only)
├── includes/
├── assets/
└── [other files...]
```

### License File Content
The `license.key` file should contain ONLY the license key, nothing else:

```
XYGh675*UGTFM.example.com
```

## Validation Process

### How the Plugin Validates Keys
1. **Reads the license file**: Plugin checks if `license.key` exists
2. **Extracts the key**: Reads the license key from the file
3. **Gets current domain**: Detects the current WordPress site's domain
4. **Validates format**: Checks if the key follows the correct format
5. **Compares domains**: Ensures the key's domain matches the current site's domain
6. **Grants access**: If validation passes, grants premium access

### Domain Detection
The plugin detects the current domain using:
- `$_SERVER['HTTP_HOST']`
- `$_SERVER['SERVER_NAME']`
- Removes `www.` prefix and port numbers

## Special Cases

### Owner Key
The owner key `Bismillah^512` works on any domain and grants full owner access.

### Invalid Scenarios
- **Wrong domain**: Key `XYGh675*UGTFM.example.com` won't work on `mysite.com`
- **Missing file**: No `license.key` file = free version
- **Wrong format**: Keys not following the format will be rejected
- **Empty file**: Empty `license.key` file = free version

## Troubleshooting

### Common Issues

#### "License key is invalid"
- Check the key format
- Ensure the domain matches exactly
- Verify the special character is in the 13th position

#### "License key is bound to a different domain"
- The key was created for a different domain
- User needs a new key for their specific domain

#### "No license file found"
- User needs to create the `license.key` file
- File should be in the plugin's root directory

#### "Plugin shows free version despite valid key"
- Check if the license file exists
- Verify the key format
- Ensure the domain matches

## Security Notes

### Key Uniqueness
- Each license key is bound to a specific domain
- Same key cannot be used on multiple domains
- Keys are stored in files, not in the database

### File Permissions
- Ensure the `license.key` file is readable by the web server
- File should have appropriate permissions (644 or 755)

## Example Workflow

### Complete Example
1. **User requests license** for `mybusiness.com`
2. **You create key**: `ABCdEfGhIjK*mybusiness.com`
3. **You send instructions** to user with the key
4. **User creates** `license.key` file with the key
5. **User uploads** plugin to their site
6. **User activates** the license through the plugin
7. **Plugin validates** the key against `mybusiness.com`
8. **Premium access granted**

## Contact Information
For support or questions about license management:
- **WhatsApp**: +923083430923
- **Email**: jaffar381996152@gmail.com

---

*This guide is for the plugin owner only. Users should contact you for license requests.*