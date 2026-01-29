# Gmail SMTP Authentication - Setup Instructions

## The Problem
Your current Gmail password `Asianpaints@123` is being rejected by Gmail's SMTP server with error:
```
535-5.7.8 Username and Password not accepted
```

This is because Google requires an **App Password** for third-party applications to access Gmail.

## Solution: Generate an App Password

### Prerequisites
- Your Gmail account must have **2-Step Verification enabled**
- You must be using the account: `dharangokulcse143@gmail.com`

### Steps to Generate App Password

1. **Go to Google Account Settings**
   - Visit: https://myaccount.google.com/

2. **Enable 2-Step Verification** (if not already enabled)
   - Click "Security" in the left menu
   - Scroll to "2-Step Verification"
   - Click "Get Started" and follow the prompts

3. **Generate App Password**
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" from the dropdown
   - Select "Windows Computer" (or your device)
   - Click "Generate"
   - Google will show you a 16-character password like: `zyxw cba qwer tyui`

4. **Copy the App Password**
   - Remove the spaces from the password
   - Example: `zyxwcbaqwertyui`

5. **Update smtp-config.php**
   - Open: `e:\code\asian-spices\smtp-config.php`
   - Replace the `password` value with your 16-character app password
   - Keep the username: `dharangokulcse143@gmail.com`

### Example Configuration
```php
'username' => 'dharangokulcse143@gmail.com',
'password' => 'zyxwcbaqwertyui', // Your 16-character app password
'from_email' => 'dharangokulcse143@gmail.com',
```

## Testing

After updating the password, run the test:
```bash
php run-test.php
```

You should see successful SMTP responses instead of authentication errors.

## Alternative: Use Google Less Secure Apps

If you don't want to use 2FA and App Passwords:

1. Go to: https://myaccount.google.com/u/0/lesssecureapps
2. Turn ON "Allow less secure apps"
3. Your regular Gmail password should then work

**Note:** This is less secure and not recommended for production use.

## Troubleshooting

- **Still getting "Bad Credentials"?** Check that you copied the entire 16-character password
- **"Authentication Required"?** Make sure 2-Step Verification is enabled
- **Connection refused?** Make sure your firewall allows outbound SMTP (port 587)

## Success Indicators

When working correctly, you should see in the logs:
```
SMTP Debug: Password auth - 235 2.7.0 Accepted
SMTP Debug: Email sent - 250 2.0.0 OK
SMTP Debug: Email sending successful
{"success":true,"message":"Your message has been received successfully!"}
```
