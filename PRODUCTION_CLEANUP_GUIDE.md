# Files to Remove from Production

The following files should be removed from your production environment as they serve no purpose in production and could potentially expose sensitive information or debugging tools:

## Already Moved to Backup

These files have already been moved to the `unused_files_backup` folder:

### Test Files

- test_verify_otp.php
- test_oauth_url.php
- simple_checkout_test.php
- simple_chatbot_test.php
- isolated_test.php
- full_page_test.php
- cleanup_test_data.php
- checkout_debug_test.php
- php/google_callback_test.php
- php/test_google_oauth.php

### Debug Files

- token_debug.php
- forgot_password_debug.php
- debug_tokens.php
- debug_database.php
- php/user_seller_debug.php
- php/order_process_full_debug.php
- php/order_process_debug.php
- php/debug_chart_data.php

### Mock Files

- php/google_mock_login.php

## Files to Consider Removing from Production

These files should be removed or secured before deploying to production:

1. `php/debug_google_oauth.php` - Replace with production_safe_debug.php or remove entirely
2. `php/check_db_structure.php` - Should be removed in production
3. `cleanup_debug_code.php` - This script itself should be removed after use
4. `cleanup_unused_files.bat` - This batch file should be removed after use
5. `CREDENTIAL_CLEANUP_GUIDE.md` - Contains information about credentials, better not to include in production
6. `GOOGLE_OAUTH_SETUP.md` - Contains setup instructions, may not be needed in production

## Additional Security Recommendations

1. **Remove Debug Statements**: Run the `cleanup_debug_code.php` script to remove error_log and other debug statements

2. **Secure Configuration Files**: Ensure all configuration files with sensitive data are properly secured

3. **Disable Error Display**: In production, make sure PHP errors are not displayed to users by setting:

   ```php
   ini_set('display_errors', 0);
   error_reporting(E_ALL);
   ```

4. **Review .gitignore**: Make sure all sensitive files are properly excluded from Git

5. **Environment Configuration**: Set up proper environment detection to disable debug tools in production
