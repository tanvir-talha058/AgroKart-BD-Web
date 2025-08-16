@echo off
echo Moving unused files to backup folder...

if not exist "unused_files_backup" mkdir "unused_files_backup"
if not exist "unused_files_backup\php" mkdir "unused_files_backup\php"

rem Test files
move "test_verify_otp.php" "unused_files_backup\"
move "test_oauth_url.php" "unused_files_backup\"
move "simple_checkout_test.php" "unused_files_backup\"
move "simple_chatbot_test.php" "unused_files_backup\"
move "isolated_test.php" "unused_files_backup\"
move "full_page_test.php" "unused_files_backup\"
move "cleanup_test_data.php" "unused_files_backup\"
move "checkout_debug_test.php" "unused_files_backup\"

rem Debug files
move "token_debug.php" "unused_files_backup\"
move "forgot_password_debug.php" "unused_files_backup\"
move "debug_tokens.php" "unused_files_backup\"
move "debug_database.php" "unused_files_backup\"

rem PHP folder test and debug files
move "php\google_callback_test.php" "unused_files_backup\php\"
move "php\test_google_oauth.php" "unused_files_backup\php\"
move "php\user_seller_debug.php" "unused_files_backup\php\"
move "php\order_process_full_debug.php" "unused_files_backup\php\"
move "php\order_process_debug.php" "unused_files_backup\php\"
move "php\debug_chart_data.php" "unused_files_backup\php\"

rem Mock files (replaced by real OAuth)
move "php\google_mock_login.php" "unused_files_backup\php\"

echo Files have been moved to the unused_files_backup folder.
echo If you need any of these files, you can find them there.
