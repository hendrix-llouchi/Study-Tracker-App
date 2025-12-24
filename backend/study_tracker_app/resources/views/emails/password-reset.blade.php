<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4F46E5;">Reset Your Password</h1>
        
        <p>You requested to reset your password. Click the button below to proceed:</p>
        
        <p style="margin-top: 30px;">
            <a href="{{ $resetUrl }}" style="background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            If you didn't request this, please ignore this email.<br>
            This link will expire in 60 minutes.
        </p>
    </div>
</body>
</html>

