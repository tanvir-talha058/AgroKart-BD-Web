# Google OAuth Setup Guide for AgroKart BD

## Overview

This guide will help you set up real Google OAuth login for your AgroKart BD website so users can sign in with their actual Google accounts.

## Step 1: Create Google OAuth Credentials

### 1.1 Go to Google Cloud Console

1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with your Google account
3. Create a new project or select an existing one

### 1.2 Enable Google+ API

1. In the left sidebar, go to "APIs & Services" → "Enabled APIs & services"
2. Click "+ ENABLE APIS AND SERVICES"
3. Search for "Google+ API" and enable it
4. Also enable "Google OAuth2 API"

### 1.3 Create OAuth 2.0 Credentials

1. Go to "APIs & Services" → "Credentials"
2. Click "+ CREATE CREDENTIALS" → "OAuth client ID"
3. Choose "Web application"
4. Give it a name like "AgroKart BD OAuth"

### 1.4 Configure Authorized Redirect URIs

Add these URIs (replace with your actual domain):

**For localhost development:**

```
http://localhost/AgroKart-BD-Web/php/google_callback.php
```

**For production (replace yourdomain.com):**

```
https://yourdomain.com/php/google_callback.php
```

### 1.5 Save Your Credentials

After creating, you'll get:

- **Client ID**: Something like `123456789-abcdefgh.apps.googleusercontent.com`
- **Client Secret**: Something like `GOCSPX-abcdefghijklmnopqrstuvwxyz`

**Important:** Save these securely!

## Step 2: Update Your Website Configuration

### 2.1 Update php/google_login.php

Replace the client_id with your actual Client ID from Step 1.5

### 2.2 Update php/google_callback.php

Replace both client_id and client_secret with your actual credentials

### 2.3 Update Redirect URI

Make sure the redirect_uri in both files matches exactly what you set in Google Cloud Console

## Step 3: Test the Implementation

### 3.1 Development Testing

1. Make sure your local server is running
2. Go to your login page
3. Click "Continue with Google"
4. You should see the real Google consent screen
5. After approval, you should be redirected back and logged in

### 3.2 Production Testing

1. Upload files to your live server
2. Update redirect URIs to use your actual domain
3. Test the Google login flow

## Security Notes

⚠️ **Important Security Considerations:**

- Never commit client secrets to version control
- Use HTTPS in production
- Validate all user data from Google
- Store credentials securely (consider environment variables)

## Troubleshooting

### Common Issues:

**"redirect_uri_mismatch" error:**

- Make sure the redirect_uri in your code exactly matches what's in Google Cloud Console
- Check for trailing slashes, http vs https, etc.

**"invalid_client" error:**

- Double-check your Client ID and Client Secret
- Make sure you're using the correct project in Google Cloud Console

**"access_denied" error:**

- User cancelled the authorization
- This is normal user behavior, handle gracefully

## What Happens After Setup

✅ **User Experience:**

1. User clicks "Continue with Google" on login page
2. Redirected to real Google login page
3. User enters their actual Google credentials
4. Google asks for permission to share basic info
5. User is redirected back to your site as logged in buyer

✅ **Account Creation:**

- New accounts automatically created as "Buyer" role
- Uses Google profile name and email
- No password required (OAuth handles authentication)
- Account linked to their Google ID for future logins

## Files Modified/Created

- `php/google_login.php` - Initiates OAuth flow
- `php/google_callback.php` - Handles Google response
- `login.php` - Updated with real OAuth button
- This setup guide

Ready to implement? Follow the steps above and update your credentials!
