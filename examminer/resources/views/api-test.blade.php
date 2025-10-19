<!DOCTYPE html>
<html>
<head>
    <title>OAuth Test</title>
    <meta charset="utf-8">
</head>
<body>
    <h1>OAuth Test - Laravel Route Working</h1>
    
    <h2>Configuration Check</h2>
    @php
        $configFile = public_path('api/google_config.php');
        $configExists = file_exists($configFile);
    @endphp
    
    @if($configExists)
        <div style="background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;">
            ✅ google_config.php exists
        </div>
        
        @php
            try {
                require $configFile;
                $clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'Not defined';
                $redirectUri = defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : 'Not defined';
            } catch (Exception $e) {
                $clientId = 'Error: ' . $e->getMessage();
                $redirectUri = 'Error loading config';
            }
        @endphp
        
        <div style="background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;">
            ✅ Client ID: {{ $clientId }}<br>
            ✅ Redirect URI: {{ $redirectUri }}
        </div>
        
        @if(defined('GOOGLE_CLIENT_ID') && defined('GOOGLE_REDIRECT_URI'))
            <h2>Direct OAuth URL Test</h2>
            @php
                $directUrl = "https://accounts.google.com/oauth/authorize?" . http_build_query([
                    'client_id' => GOOGLE_CLIENT_ID,
                    'redirect_uri' => GOOGLE_REDIRECT_URI,
                    'scope' => 'openid email profile',
                    'response_type' => 'code',
                    'access_type' => 'online',
                    'prompt' => 'consent'
                ]);
            @endphp
            
            <div style="background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid #17a2b8;">
                <a href="{{ $directUrl }}" target="_blank" style="background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Test Direct OAuth URL</a>
            </div>
        @endif
    @else
        <div style="background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;">
            ❌ google_config.php not found at: {{ $configFile }}
        </div>
    @endif
    
    <h2>Google Cloud Console Links</h2>
    <div style="background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107;">
        <h3>⚠️ CRITICAL: Check These Settings</h3>
        <ol>
            <li><strong>OAuth Consent Screen:</strong> <a href="https://console.cloud.google.com/apis/credentials/consent" target="_blank">Open OAuth Consent Screen</a></li>
            <li><strong>Credentials:</strong> <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Open Credentials</a></li>
            <li><strong>Domain Verification:</strong> <a href="https://search.google.com/search-console" target="_blank">Open Search Console</a></li>
        </ol>
    </div>
    
    <h2>Most Common Fix for "Access Denied"</h2>
    <div style="background: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;">
        <h3>❌ 'Access Denied' Fix (90% of cases):</h3>
        <ol>
            <li>Go to <a href="https://console.cloud.google.com/apis/credentials/consent" target="_blank">OAuth Consent Screen</a></li>
            <li>Make sure Publishing status is <strong>'Testing'</strong> (not 'In production')</li>
            <li>Add your email address to <strong>'Test users'</strong> list</li>
            <li>Try signing in with that exact email address</li>
        </ol>
    </div>
    
    <h2>Other Test URLs</h2>
    <ul>
        <li><a href="/api/simple_test.php">Simple Test (File Include)</a></li>
        <li><a href="/api/test_google_config.php">Full Config Test</a></li>
        <li><a href="/api/oauth_test.php">OAuth Test Tool</a></li>
        <li><a href="/api/google_start.php">Start OAuth Flow</a></li>
    </ul>
</body>
</html>
