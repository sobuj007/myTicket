<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 40px 30px;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }
        .code-container {
            text-align: center;
            margin: 30px 0;
        }
        .verification-code {
            font-size: 48px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #4F46E5;
            background-color: #f3f4f6;
            padding: 20px 30px;
            border-radius: 12px;
            display: inline-block;
            font-family: 'Courier New', monospace;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
        }
        .expiry-note {
            font-size: 14px;
            color: #6b7280;
            margin-top: 20px;
            text-align: center;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        h1 {
            margin: 0;
            font-size: 28px;
        }
        h2 {
            color: #111827;
            margin-top: 0;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verification Code</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hello <strong>{{ $user->name }}</strong>,
            </div>
            
            <p>You're almost there! Please use the following verification code to complete your action:</p>
            
            <div class="code-container">
                <div class="verification-code">{{ $code }}</div>
            </div>
            
            <p>This code will expire in <strong>10 minutes</strong> for security reasons.</p>
            
            <div class="warning">
                <strong>⚠️ Important:</strong> Never share this code with anyone. Our team will never ask for this code.
            </div>
            
            <p>If you didn't request this code, you can safely ignore this email.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

