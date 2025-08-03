# Quick Reference - License Key Management

## License Key Format
```
[12 characters][special character][domain name]
```

**Example:** `XYGh675*UGTFM.example.com`

## Steps When You Receive a License Request

### 1. Create the License Key
- **First 12 characters**: Any letters/numbers (e.g., `XYGh675UGTFM`)
- **13th character**: Special character (`*`, `#`, `@`, `$`, `%`, `&`)
- **Domain**: User's domain name (e.g., `example.com`)

### 2. Send to User
```
Your license key: [LICENSE_KEY]

Instructions:
1. Create "license.key" file in plugin folder
2. Add your license key to the file
3. Upload plugin to your site
4. Go to Premium Features page
5. Enter license key and activate
```

### 3. User Instructions
- Create `license.key` file in plugin root folder
- Add only the license key (no extra text)
- Upload plugin to WordPress site
- Activate through Premium Features page

## Valid License Key Examples
- `BJKmNpQrStU*example.com`
- `FGHjKlMnOpQ#mysite.com`
- `TUVwXyZaBcD@testdomain.com`

## File Structure
```
student-result-management/
├── license.key                    ← User adds this file
├── student-result-management.php
└── [other files...]
```

## License File Content
```
XYGh675*UGTFM.example.com
```

## Contact Info
- **WhatsApp**: +923083430923
- **Email**: jaffar381996152@gmail.com

---

*For detailed guide, see LICENSE_KEY_GUIDE.md*