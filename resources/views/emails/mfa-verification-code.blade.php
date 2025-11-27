<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .message {
            font-size: 15px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
        }
        .code-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #ffffff;
            font-family: 'Courier New', monospace;
        }
        .code-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .expiry-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .expiry-info p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .security-notice {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-notice p {
            margin: 5px 0;
            color: #721c24;
            font-size: 14px;
        }
        .security-notice ul {
            margin: 10px 0 0 20px;
            padding: 0;
            color: #721c24;
        }
        .security-notice li {
            font-size: 13px;
            margin: 5px 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #6c757d;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 20px;
            }
            .code {
                font-size: 28px;
                letter-spacing: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔐 Multi-Factor Authentication</h1>
        </div>
        
        <div class="email-body">
            <p class="greeting">Hello {{ $userName }},</p>
            
            <p class="message">
                You recently attempted to sign in to your Sand Pit N Quarry Admin account. 
                To complete your login, please use the verification code below:
            </p>
            
            <div class="code-container">
                <div class="code-label">Your Verification Code</div>
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="expiry-info">
                <p>
                    <strong>⏱️ Important:</strong> This code will expire in <strong>{{ $expiryMinutes }} minutes</strong> 
                    at {{ $expiresAt->format('g:i A') }}.
                </p>
            </div>
            
            <div class="security-notice">
                <p><strong>🛡️ Security Notice:</strong></p>
                <ul>
                    <li>Never share this code with anyone</li>
                    <li>Sand Pit N Quarry staff will never ask for your verification code</li>
                    <li>If you didn't request this code, please ignore this email and secure your account</li>
                    <li>This code can only be used once</li>
                </ul>
            </div>
            
            <p class="message">
                If you didn't attempt to sign in, please contact your system administrator immediately.
            </p>
        </div>
        
        <div class="email-footer">
            <p><strong>Sand Pit N Quarry Admin</strong></p>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Sand Pit N Quarry. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
