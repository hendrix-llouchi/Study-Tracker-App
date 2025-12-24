<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to FocusTrack</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4F46E5;">Welcome to FocusTrack, {{ $user->name }}!</h1>
        
        <p>We're excited to have you on board. FocusTrack will help you stay organized, track your academic performance, and achieve your study goals.</p>
        
        <h2>Get Started</h2>
        <ol>
            <li>Add your courses</li>
            <li>Set up your timetable</li>
            <li>Create your first study plan</li>
            <li>Start tracking your progress</li>
        </ol>
        
        <p style="margin-top: 30px;">
            <a href="{{ config('app.url') }}/dashboard" style="background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Dashboard</a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Happy studying!<br>
            The FocusTrack Team
        </p>
    </div>
</body>
</html>

