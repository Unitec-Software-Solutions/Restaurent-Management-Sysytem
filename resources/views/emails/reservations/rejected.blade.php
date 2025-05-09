@component('mail::message')
# Reservation Update

Dear {{ $customerName }},

We regret to inform you that your reservation at {{ $branch->name }} has been rejected.

**Reservation Details:**
- Date: {{ $reservation->date->format('l, F j, Y') }}
- Time: {{ $reservation->start_time->format('g:i A') }} - {{ $reservation->end_time->format('g:i A') }}
- Number of People: {{ $reservation->no_of_people }}
- Branch: {{ $branch->name }}

@if($reservation->payments()->exists())
**Refund Information:**
A refund has been processed for your reservation fee. The amount will be credited back to your original payment method within 5-7 business days.
@endif

We apologize for any inconvenience this may have caused. If you would like to make a new reservation, please visit our website or contact us directly.

@component('mail::button', ['url' => route('reservations.create')])
Make New Reservation
@endcomponent

If you have any questions, please don't hesitate to contact us.

Best regards,<br>
{{ config('app.name') }}
@endcomponent 