<?php
// api/oauth/google_config.php
// Copy your real Client ID/Secret from Google Cloud Console
define('GOOGLE_CLIENT_ID',     'YOUR_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_SECRET');
define('GOOGLE_REDIRECT_URI',  'https://exam-miner.com/api/callback.php'); // Your callback.php

// Your app’s JWT secret (same as login.php/_auth.php)
define('APP_JWT_SECRET', 'exam-miner');
