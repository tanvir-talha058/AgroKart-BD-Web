# Credential Cleanup Guide

To fix the issue with GitHub blocking pushes due to exposed credentials, we've implemented a secure environment variable system that keeps sensitive data out of the repository. Here's what we've done:

## 1. Environment Variable System

We've set up an environment variable system:

1. Created an `.env` file to store your actual credentials (which is in `.gitignore`)
2. Added an environment loader (`includes/env_loader.php`) to read these variables
3. Updated the OAuth configuration to use these environment variables

### Files Changed:

- `.env` - Contains your actual credentials (excluded from Git)
- `.env.example` - Template for others to follow (contains no real credentials)
- `includes/env_loader.php` - New file to load environment variables
- `config/oauth_config_local.php` - Updated to use environment variables

## 2. Future GitHub Pushes

If you're still having issues pushing to GitHub, you can:

1. Visit these URLs to resolve the security alerts:
   - https://github.com/tanvir-talha058/AgroKart-BD-Web/security/secret-scanning/unblock-secret/31LZAxuwpyVcKllgmy9KMBWMcuP
   - https://github.com/tanvir-talha058/AgroKart-BD-Web/security/secret-scanning/unblock-secret/31LZAz2Q5rZO9BCyfjCVXRIY9Y9

2. Revoke and regenerate your Google OAuth credentials for better security:
   - Go to the [Google Cloud Console](https://console.cloud.google.com/)
   - Find your project and go to "APIs & Services" > "Credentials"
   - Delete the old credentials and create new ones
   - Update your `.env` file with the new credentials

## 3. For New Team Members

Anyone working on this project should:

1. Copy `.env.example` to a new file called `.env`
2. Fill in their own Google OAuth credentials
3. Never commit the `.env` file to Git

## 4. Security Best Practices

- Never commit real credentials to Git
- Always use environment variables for sensitive data
- Regularly rotate your OAuth credentials
- Check your Git history for any accidentally committed secrets
