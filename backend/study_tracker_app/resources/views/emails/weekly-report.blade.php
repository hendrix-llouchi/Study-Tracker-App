<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Weekly Study Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4F46E5;">Weekly Report, {{ $user->name }}!</h1>
        
        @if($report)
            <h2>Summary</h2>
            <ul>
                <li>Total Study Hours: {{ $report['total_study_hours'] ?? 0 }} hours</li>
                <li>Planned Hours: {{ $report['planned_hours'] ?? 0 }} hours</li>
                <li>Completion Rate: {{ $report['completion_rate'] ?? 0 }}%</li>
            </ul>
            
            @if(isset($report['ai_insights']))
                <h3>Insights</h3>
                <p>{{ $report['ai_insights'] }}</p>
            @endif
        @endif
        
        <p style="margin-top: 30px;">
            <a href="{{ config('app.url') }}/progress" style="background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Full Report</a>
        </p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Keep up the great work!<br>
            The FocusTrack Team
        </p>
    </div>
</body>
</html>

