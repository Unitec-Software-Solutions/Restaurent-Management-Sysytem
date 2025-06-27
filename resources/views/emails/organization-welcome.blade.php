{{-- filepath: resources/views/emails/organization-welcome.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Restaurant Management System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border: 1px solid #e1e5e9; }
        .footer { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; text-align: center; color: #6c757d; font-size: 14px; }
        .btn { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .credentials { background: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #4f46e5; margin: 20px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Welcome to Restaurant Management System</h1>
            <p>Your restaurant management platform is ready!</p>
        </div>

        <div class="content">
            <h2>Hello {{ $adminName }}!</h2>
            
            <p>Congratulations! Your organization <strong>{{ $organizationName }}</strong> has been successfully set up in our Restaurant Management System.</p>

            <h3>What's been created for you:</h3>
            <ul>
                <li>✅ Your organization profile</li>
                <li>✅ Head office branch with kitchen stations</li>
                <li>✅ Administrator account with full access</li>
                <li>✅ Default roles and permissions</li>
                <li>✅ Basic inventory setup</li>
            </ul>

            <div class="credentials">
                <h3>🔐 Your Login Credentials</h3>
                <p><strong>Email:</strong> {{ $adminEmail }}</p>
                <p><strong>Temporary Password:</strong> <code>{{ $temporaryPassword }}</code></p>
                <p><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
            </div>

            <div class="warning">
                <strong>⚠️ Important Security Notice:</strong><br>
                Please log in immediately and change your password. This temporary password will expire in 24 hours.
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="btn">Access Your Dashboard</a>
            </div>

            <h3>Next Steps:</h3>
            <ol>
                <li>Log in and change your password</li>
                <li>Complete your organization profile</li>
                <li>Set up your menu items</li>
                <li>Configure your subscription plan</li>
                <li>Add additional branches if needed</li>
                <li>Invite your team members</li>
            </ol>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Restaurant Management System. All rights reserved.</p>
            <p>This email was sent to {{ $adminEmail }} regarding {{ $organizationName }}</p>
        </div>
    </div>
</body>
</html>