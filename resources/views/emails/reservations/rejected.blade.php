<!DOCTYPE html>
<html>
<head>
    <title>Reservation Declined</title>
</head>
<body>
    <h1>Reservation Couldn't Be Confirmed</h1>
    <p>Hello {{ $reservation->name }},</p>
    <p>We're unable to confirm your reservation at {{ $reservation->branch->name }}.</p>
    <h2>Original Request:</h2>
    <ul>
        <li>Date: {{ $reservation->date->format('d M Y') }}</li>
        <li>Time: {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }}</li>
        <li>Party Size: {{ $reservation->number_of_people }}</li>
    </ul>
    <p>Please contact us for assistance.</p>
</body>
</html>