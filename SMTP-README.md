# Asian Spices Contact Form - SMTP Setup Guide

This contact form uses SMTP for reliable email delivery. Follow these steps to configure it properly.

## Files Overview

- `contact-form.php` - Main form handler with SMTP functionality
- `smtp-config.php` - SMTP configuration file (needs to be customized)
- `contact.html` - Contact form HTML
- `assets/js/main.js` - Form validation and AJAX submission

## SMTP Configuration

### Step 1: Configure SMTP Settings

1. Copy `smtp-config.php` and customize the settings for your email provider:

```php
return [
    'smtp' => [
        'host' => 'your-smtp-server.com',
        'port' => 587, // 587 for TLS, 465 for SSL
        'encryption' => 'tls', // 'tls', 'ssl', or ''
        'username' => 'your-email@domain.com',
        'password' => 'your-password-or-app-password',
        'from_email' => 'noreply@yourdomain.com',
        'from_name' => 'Your Company Name',
        'timeout' => 30,
        'debug' => false // Set to true for troubleshooting
    ],
    'email' => [
        'admin_email' => 'admin@yourdomain.com',
        'site_name' => 'Your Site Name',
        'site_url' => 'https://yourdomain.com'
    ]
];
```

### Step 2: Popular SMTP Providers

#### Gmail SMTP
```php
'host' => 'smtp.gmail.com',
'port' => 587,
'encryption' => 'tls',
'username' => 'your-gmail@gmail.com',
'password' => 'your-app-password', // Generate from Google Account Settings
```

**Important:** Use an App Password, not your regular Gmail password:
1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Security → 2-Step Verification → App passwords
3. Generate a password for "Mail"

#### Outlook/Hotmail SMTP
```php
'host' => 'smtp-mail.outlook.com',
'port' => 587,
'encryption' => 'tls',
'username' => 'your-email@outlook.com',
'password' => 'your-account-password',
```

#### Yahoo SMTP
```php
'host' => 'smtp.mail.yahoo.com',
'port' => 587,
'encryption' => 'tls',
'username' => 'your-email@yahoo.com',
'password' => 'your-account-password',
```

#### SendGrid SMTP
```php
'host' => 'smtp.sendgrid.net',
'port' => 587,
'encryption' => 'tls',
'username' => 'apikey', // Literally 'apikey'
'password' => 'your-sendgrid-api-key',
```

#### Mailgun SMTP
```php
'host' => 'smtp.mailgun.org',
'port' => 587,
'encryption' => 'tls',
'username' => 'your-mailgun-smtp-username',
'password' => 'your-mailgun-smtp-password',
```

### Step 3: Testing SMTP Configuration

1. Set `'debug' => true` in your config to enable detailed logging
2. Check your PHP error logs for SMTP connection details
3. Test the form submission
4. Monitor email delivery

### Step 4: Security Considerations

1. **Never commit real credentials** to version control
2. Use environment variables for production:
   ```php
   'username' => getenv('SMTP_USERNAME'),
   'password' => getenv('SMTP_PASSWORD'),
   ```
3. Restrict file permissions on `smtp-config.php`:
   ```bash
   chmod 600 smtp-config.php
   ```
4. Consider using OAuth2 for Gmail instead of app passwords

### Step 5: Troubleshooting

#### Common Issues:

1. **Connection Failed**
   - Check SMTP host and port
   - Verify firewall settings
   - Try different encryption settings

2. **Authentication Failed**
   - Verify username/password
   - Check if 2FA is enabled (use app passwords)
   - Ensure account allows SMTP access

3. **TLS/SSL Errors**
   - Try different ports (587 vs 465)
   - Check encryption settings
   - Verify SSL certificates

4. **Emails Not Received**
   - Check spam/junk folders
   - Verify sender reputation
   - Check email provider limits

#### Debug Mode:
Enable debug mode in config and check PHP error logs:
```php
'debug' => true,
```

### Step 6: Production Deployment

1. Set `'debug' => false` in production
2. Use proper error logging instead of `error_log()`
3. Implement rate limiting to prevent abuse
4. Add CAPTCHA if needed
5. Monitor email delivery and bounce rates

## Alternative: Using PHPMailer Library

For more advanced features, consider using PHPMailer:

1. Install via Composer: `composer require phpmailer/phpmailer`
2. Or download from: https://github.com/PHPMailer/PHPMailer
3. Uncomment the PHPMailer includes in `contact-form.php`
4. Replace the custom SMTP function with PHPMailer

## Support

If you encounter issues:
1. Check PHP error logs
2. Enable debug mode
3. Test with different SMTP providers (like Mailtrap for testing)
4. Verify network connectivity to SMTP ports

## Security Note

This implementation includes basic security measures, but for production use, consider additional protections like:
- Input sanitization
- Rate limiting
- CAPTCHA
- IP blocking
- Email content filtering