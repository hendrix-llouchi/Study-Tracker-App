<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Plan Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4F46E5;">Hi {{ $user->name }},</h1>
        
        <p>Don't forget to set your study plan for tomorrow! Planning ahead helps you stay organized and productive.</p>
        
        <p style="margin-top: 30px;">
            <a href="{{ config('app.url') }}/planning" style="background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Create Study Plan</a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Best regards,<br>
            The FocusTrack Team
        </p>
    </div>
</body>
</html>

