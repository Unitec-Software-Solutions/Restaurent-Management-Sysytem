<form method="POST" action="{{ url('/reservation/check-phone') }}">
    @csrf
    <label for="phone">Enter your phone number:</label>
    <input type="text" name="phone" id="phone" required>
    <button type="submit">Continue</button>
</form>