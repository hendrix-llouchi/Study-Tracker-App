<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Daily Study Plan</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4F46E5;">Good Morning, {{ $user->name }}!</h1>
        
        <p>Here's your study plan for today:</p>
        
        @if($plans->count() > 0)
            <ul style="list-style: none; padding: 0;">
                @foreach($plans as $plan)
                    <li style="background: #f3f4f6; padding: 15px; margin: 10px 0; border-radius: 5px;">
                        <strong>{{ $plan->course->name }}</strong> - {{ $plan->topic }}<br>
                        <small>Time: {{ $plan->start_time }} | Duration: {{ $plan->planned_duration }} minutes</small>
                    </li>
                @endforeach
            </ul>
        @else
            <p>No study plans scheduled for today. Take a break or add some plans!</p>
        @endif
        
        <p style="margin-top: 30px;">
            <a href="{{ config('app.url') }}/planning" style="background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Full Plan</a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Have a productive day!<br>
            The FocusTrack Team
        </p>
    </div>
</body>
</html>

