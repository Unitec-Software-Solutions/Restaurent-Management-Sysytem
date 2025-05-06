<p>This phone number is registered. Do you want to login?</p>
<form method="GET" action="{{ route('login') }}">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <button type="submit">Yes, Login</button>
</form>
<form method="GET" action="{{ route('reservation.create') }}">
    <input type="hidden" name="phone" value="{{ $phone }}">
    <button type="submit">No, Continue as Guest</button>
</form>