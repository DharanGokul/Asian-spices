# How to Run Asian Spices Website Locally

## Step 1: Start the PHP Development Server

Open PowerShell and run:

```powershell
cd e:\code\asian-spices
php -S localhost:8000
```

You should see:
```
[Thu Jan 29 08:XX:XX 2026] PHP 7.4.3 Development Server (http://localhost:8000) started
```

**Leave this terminal open** - this server must keep running!

## Step 2: Open the Website in Browser

Open your web browser and go to:
```
http://localhost:8000
```

You should see the Asian Spices homepage with navigation menu.

## Step 3: Navigate to Contact Form

Click on the **"Contact"** link in the navigation menu or go to:
```
http://localhost:8000/contact.html
```

## Step 4: Fill Out the Contact Form

Complete the form with the following fields:

- **Full Name** * (required) → Enter your name
- **Email** * (required) → Enter your email address
- **Company Name** * (required) → Enter your company name
- **Phone Number** → (optional) Enter phone number
- **Country** * (required) → Enter your country
- **Inquiry Type** * (required) → Select from dropdown:
  - Product Inquiry
  - Sample Request
  - Partnership Opportunity
  - Quality/Certification Information
  - General Inquiry
  - Press/Media
- **Message** → (optional) Enter your message

## Step 5: Submit the Form

Click the **"Send Message"** button

## Step 6: Check the Response

You should see one of these responses:

### Success Response (Green Alert)
```
Thank you for contacting us! We have sent you a confirmation email.
```

### Error Response (Red Alert)
If something went wrong, you'll see an error message with details.

## Step 7: Check Your Emails

**Check both mailboxes:**

### Admin Email
- **To:** dharangokur.integra@gmail.com
- **From:** dharangokulcse143@gmail.com
- **Subject:** New Contact Form Submission - [Your Inquiry Type]
- **Contains:** All form details you submitted

### User Confirmation Email
- **To:** Your email address (the one you entered in the form)
- **From:** Asian Spices
- **Subject:** Thank you for contacting Asian Spices
- **Contains:** Thank you message and our contact information

## Troubleshooting

### Server Won't Start
- Make sure PHP is installed: `php --version`
- Make sure port 8000 is not in use
- Try a different port: `php -S localhost:8001`

### Form Submission Shows Error
- Check all required fields are filled
- Check your email address is valid
- Check if debug logs show authentication errors

### Emails Not Received
1. Check the admin email inbox at: dharangokur.integra@gmail.com
2. Check your confirmation email inbox
3. Check SPAM/JUNK folders
4. Check the server terminal for error messages

### Server Terminal Shows SMTP Errors

**If you see:** `535-5.7.8 Username and Password not accepted`
- The app password needs to be regenerated from: https://myaccount.google.com/apppasswords
- Update the password in: `smtp-config.php`

**If you see:** `SSL operation failed with code 1`
- This should be fixed already, but restart the server if needed

## Stopping the Server

In the terminal where the server is running, press:
```
Ctrl + C
```

You'll see:
```
^C
```

The server will stop.

## View Server Logs

While the form is being submitted, watch the terminal running the PHP server. You'll see:

```
[date time] [IP]:PORT Accepted
[date time] [IP]:PORT [200]: (null) /contact-form.php
[date time] [IP]:PORT Closing
```

This shows successful form processing.

## Testing with Different Emails

Try submitting the form multiple times with different email addresses to test:
- Your personal email
- Test email addresses
- Make sure you check all inboxes

---

**Quick Reference:**
- Website: http://localhost:8000
- Contact Form: http://localhost:8000/contact.html
- Server Terminal: Keep this open
- Admin receives emails: YES
- User receives confirmation: YES
- Expected response time: 2-5 seconds
