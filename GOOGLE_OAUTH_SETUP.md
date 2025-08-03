# Setting Up Google OAuth for AgroKart-BD-Web

This document provides instructions for setting up Google OAuth authentication for the AgroKart-BD-Web application.

## Prerequisites

1. A Google account
2. Access to the [Google Developer Console](https://console.developers.google.com/)

## Step 1: Create a Google Cloud Project

1. Go to the [Google Developer Console](https://console.developers.google.com/)
2. Click on "Select a project" at the top of the page
3. Click on "NEW PROJECT"
4. Enter a name for your project (e.g., "AgroKart-BD-Web")
5. Click "CREATE"

## Step 2: Configure OAuth Consent Screen

1. Select your project
2. In the left sidebar, navigate to "APIs & Services" > "OAuth consent screen"
3. Select the user type (External or Internal)
4. Fill in the required information:
   - App name: "AgroKart-BD-Web"
   - User support email: Your email address
   - Developer contact information: Your email address
5. Click "SAVE AND CONTINUE"
6. Add the following scopes:
   - `./auth/userinfo.email`
   - `./auth/userinfo.profile`
7. Click "SAVE AND CONTINUE"
8. Add test users if needed
9. Click "SAVE AND CONTINUE"

## Step 3: Create OAuth Client ID

1. In the left sidebar, navigate to "APIs & Services" > "Credentials"
2. Click "CREATE CREDENTIALS" and select "OAuth client ID"
3. Select "Web application" as the application type
4. Enter a name for the client (e.g., "AgroKart-BD-Web Client")
5. Add authorized JavaScript origins:
   - `http://localhost` (for local development)
   - Your production domain (if applicable)
6. Add authorized redirect URIs:
   - `http://localhost/AgroKart-BD-Web/php/google_callback.php` (for local development)
   - Your production callback URL (if applicable)
7. Click "CREATE"
8. Note down the Client ID and Client Secret

## Step 4: Update the Application Code

1. Open the file `php/google_login.php`
2. Replace `YOUR_GOOGLE_CLIENT_ID` with your actual Client ID
3. If needed, update the redirect URI to match your environment

## Step 5: Install Google API PHP Client Library (Optional for full implementation)

For a complete implementation, you would need to install the Google API PHP Client library:

```bash
composer require google/apiclient:^2.0
```

Then update the Google login files to use the library for proper token exchange and profile retrieval.

## Testing

1. Navigate to the login page
2. Click on "Continue with Google"
3. You should be redirected to Google's authentication page
4. After authentication, you should be redirected back to the application

## Troubleshooting

- If you encounter errors, check the Client ID and redirect URI
- Ensure that the OAuth consent screen is properly configured
- Check that the required scopes are added
- Verify that the callback URL is correctly registered in the Google Developer Console