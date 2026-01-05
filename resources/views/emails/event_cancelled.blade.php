<!DOCTYPE html>
<html>

<head>
    <title>Event Cancelled</title>
</head>

<body>
    <h1>We Apologize</h1>
    <p>Dear {{ $user->name }},</p>
    <p>We regret to inform you that the event <strong>{{ $event->title }}</strong> scheduled for {{ $event->start_datetime->format('F j, Y, g:i a') }} has been cancelled by the organizer.</p>
    <p>We apologize for any inconvenience this may cause.</p>
    <p>Sincerely,<br>The LEMS Team</p>
</body>

</html>