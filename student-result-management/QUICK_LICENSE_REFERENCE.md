# Quick License Key Reference

## License Key Format
```
[12 random characters][special character][domain name]
```

**Example:** `XYGh675*UGTFM.example.com`

## Steps When User Requests License

### 1. Receive Request
- User fills form in plugin
- Request sent to: `jaffar381996152@gmail.com`
- Contains: Name, Email, Phone, Domain

### 2. Create License Key
- Generate 12 random characters (letters/numbers)
- Add 1 special character (`*`, `#`, `@`, `$`, `%`, `&`)
- Add user's domain name
- **Example:** `ABCdEfGhIjK*mybusiness.com`

### 3. Send to User
```
Your license key: [LICENSE_KEY]

Instructions:
1. Create "license.key" file in plugin folder
2. Add your license key to the file
3. Upload plugin to your site
4. Go to Premium Features page
5. Enter license key and activate
```

## File Locations

### License File
- **Location:** `student-result-management/license.key`
- **Content:** Just the license key (no extra text)
- **Example:** `XYGh675*UGTFM.example.com`

### Plugin Structure
```
student-result-management/
├── student-result-management.php
├── license.key                    ← Add this file for premium users
├── includes/
└── [other files...]
```

## Validation Rules

### Valid Keys
- ✅ `BJKmNpQrStU*example.com`
- ✅ `FGHjKlMnOpQ#mysite.com`
- ✅ `TUVwXyZaBcD@testdomain.com`

### Invalid Keys
- ❌ `ABC123example.com` (no special character)
- ❌ `ABC*123example.com` (special character in wrong position)
- ❌ `ABC*123wrongdomain.com` (domain mismatch)

## Special Cases

### Owner Key
- **Key:** `Bismillah^512`
- **Works on:** Any domain
- **Access:** Full owner privileges

### Free Version
- **No license file** = Free version
- **Features:** Limited to 20 students
- **No premium features**

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Invalid key" | Check format and domain match |
| "Wrong domain" | Create new key for correct domain |
| "No premium features" | Verify license file exists |
| "File not found" | Create license.key file in plugin root |

## Contact Info
- **WhatsApp:** +923083430923
- **Email:** jaffar381996152@gmail.com