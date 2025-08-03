# Quick License Key Reference

## License Key Format
```
[RANDOM_CHARS][SPECIAL_CHAR][RANDOM_CHARS].[DOMAIN_NAME]
```

## Examples
- `XYGh675*UGTFM.example.com`
- `ABc123#XYZ789.mywebsite.com`
- `DEF456@GHI012.school.edu`

## Steps When User Requests License

1. **Receive email** from `jaffar381996152@gmail.com`
2. **Extract domain** from the request
3. **Create license key** using format above
4. **Create `license.key` file** with just the key inside
5. **Send file** to user via email

## File Location for Users
```
wp-content/plugins/student-result-management/license.key
```

## Owner Key
- **Key**: `Bismillah^512`
- **Works on**: Any domain
- **Access**: Full owner privileges

## Validation Rules
- Domain in key must match user's actual domain
- Key must contain at least one special character
- File must be readable by web server
- No extra spaces or lines in license file

## Common Issues
- **Domain mismatch**: Check exact domain name (including www)
- **File not found**: Ensure `license.key` is in plugin root
- **Permission error**: Check file is readable by web server
- **Format error**: Verify key follows the pattern above

## Security Notes
- Each key is bound to one domain only
- Keys cannot be shared between different domains
- Owner key works everywhere
- Keep all license keys secure and backed up