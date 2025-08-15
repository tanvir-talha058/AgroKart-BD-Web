# Email Setup Guide for AgroKart BD

## Overview

Your AgroKart BD website now uses PHPMailer for sending password reset emails. The debug messages have been removed and replaced with proper email functionality.

## Required Email Configuration

### Step 1: Gmail SMTP Setup

1. **Create or Use Existing Gmail Account**

   - You need a Gmail account to send emails from your website
   - It's recommended to create a dedicated business email account

2. **Enable 2-Factor Authentication**

   - Go to your Google Account settings
   - Enable 2-Factor Authentication (required for app passwords)

3. **Generate App Password**
   - Go to Google Account → Security → App passwords
   - Select "Mail" as the app
   - Generate a 16-character app password
   - **Save this password** - you'll need it in Step 2

### Step 2: Update Email Credentials

Edit the file: `php/forgot_password_process.php`

Find these lines in the `sendResetEmail()` function:

```php
$mail->Username   = 'your-email@gmail.com';          // Change this
$mail->Password   = 'your-app-password';             // Change this
```

Replace with:

```php
$mail->Username   = 'yourbusiness@gmail.com';        // Your Gmail address
$mail->Password   = 'abcd efgh ijkl mnop';           // Your 16-character app password
```

### Step 3: Customize Sender Details (Optional)

You can also update the sender name and email:

```php
$mail->setFrom('noreply@agrokartbd.com', 'AgroKart BD');
```

Change to your preferred sender details:

```php
$mail->setFrom('your-email@gmail.com', 'Your Business Name');
```

## Alternative Email Providers

### If you want to use other email providers instead of Gmail:

**Yahoo Mail:**

```php
$mail->Host       = 'smtp.mail.yahoo.com';
$mail->Port       = 587;
$mail->Username   = 'your-email@yahoo.com';
$mail->Password   = 'your-app-password';
```

**Outlook/Hotmail:**

```php
$mail->Host       = 'smtp-mail.outlook.com';
$mail->Port       = 587;
$mail->Username   = 'your-email@outlook.com';
$mail->Password   = 'your-password';
```

## Testing the Email Functionality

1. Update the email credentials in `forgot_password_process.php`
2. Go to your website's forgot password page
3. Enter a valid email address
4. Check if the OTP email is received
5. Check server error logs if emails are not being sent

## Troubleshooting

### Common Issues:

1. **"SMTP Authentication failed"**

   - Double-check your email address and app password
   - Make sure 2-Factor Authentication is enabled on Gmail
   - Verify the app password was generated correctly

2. **"SMTP connection failed"**

   - Check if your hosting provider blocks outgoing SMTP connections
   - Contact your hosting provider to enable SMTP on port 587

3. **Emails going to spam**
   - Consider using a proper domain email (instead of Gmail)
   - Add SPF and DKIM records to your domain

### Error Logs

Check your server's PHP error log for detailed error messages:

- Look for "PHPMailer Error:" entries
- Contact your hosting provider if you need help accessing error logs

## Security Notes

- Never commit email passwords to version control
- Consider using environment variables for sensitive credentials
- Use strong, unique passwords for your email account
- Monitor your email account for any suspicious activity

## What Changed

✅ **Fixed Issues:**

- Removed debug messages showing OTP codes on the website
- Replaced PHP mail() function with PHPMailer SMTP
- Added proper error handling for email failures
- Improved user feedback messages

✅ **Improvements:**

- Professional HTML email templates
- Better error logging
- Secure SMTP authentication
- Ready for production use

## Next Steps

1. Follow Step 1-3 above to configure your Gmail SMTP
2. Test the forgot password functionality
3. Monitor error logs for any issues
4. Consider setting up a business domain email for more professional appearance

---

**Need Help?**

- Check the error logs first
- Ensure all credentials are correct
- Contact your hosting provider about SMTP settings
- Test with a simple email first before going live
