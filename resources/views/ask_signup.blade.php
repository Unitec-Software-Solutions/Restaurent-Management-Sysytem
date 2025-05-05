<p>This phone number is not registered. Do you want to sign up?</p>
<form method="GET" action="{{ route('signup') }}">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <button type="submit">Yes, Sign Up</button>
</form>
<form method="GET" action="{{ route('reservations.create') }}">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <button type="submit">No, Continue as Guest</button>
</form>