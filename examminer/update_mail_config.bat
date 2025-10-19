@echo off
echo ========================================
echo Gmail SMTP Configuration for Exam Miner 2.0
echo ========================================
echo.
echo Please follow these steps:
echo.
echo 1. Go to your Google Account settings
echo 2. Enable 2-Factor Authentication
echo 3. Generate an App Password for Mail
echo 4. Copy the 16-character app password
echo.
echo Then update your .env file with these settings:
echo.
echo MAIL_MAILER=smtp
echo MAIL_HOST=smtp.gmail.com
echo MAIL_PORT=587
echo MAIL_USERNAME=your-gmail@gmail.com
echo MAIL_PASSWORD=your-16-char-app-password
echo MAIL_ENCRYPTION=tls
echo MAIL_FROM_ADDRESS=your-gmail@gmail.com
echo MAIL_FROM_NAME="Exam Miner 2.0"
echo.
echo After updating .env, run: php artisan config:clear
echo.
pause


