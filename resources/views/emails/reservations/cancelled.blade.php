<!DOCTYPE html>
<html>
<head>
    <title>Reservation Cancelled</title>
</head>
<body>
    <h1>Reservation Cancelled</h1>
    <p>Hello {{ $reservation->name }},</p>
    <p>Your reservation at {{ $reservation->branch->name }} has been cancelled.</p>
    @if($reservation->cancel_reason)
        <p><strong>Reason:</strong> {{ $reservation->cancel_reason }}</p>
    @endif
    <p>We hope to serve you another time!</p>
</body>
</html>