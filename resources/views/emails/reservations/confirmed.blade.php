<!DOCTYPE html>
<html>
<head>
    <title>Reservation Confirmed</title>
</head>
<body>
    <h1>Reservation Confirmed</h1>
    <p>Hello {{ $reservation->name }},</p>
    <p>Your reservation at {{ $reservation->branch->name }} has been confirmed!</p>
    <h2>Details:</h2>
    <ul>
        <li>Date: {{ $reservation->date->format('d M Y') }}</li>
        <li>Time: {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }}</li>
        <li>Party Size: {{ $reservation->number_of_people }}</li>
        <li>Reservation ID: {{ $reservation->id }}</li>
    </ul>
    <p>We look forward to serving you!</p>
</body>
</html>