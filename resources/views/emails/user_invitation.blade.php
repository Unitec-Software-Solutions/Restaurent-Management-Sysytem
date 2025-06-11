@component('mail::message')
# You've been invited to {{ $organization->name }}

@if($branch)
You've been added to the **{{ $branch->name }}** branch
@endif

Please complete your registration to access the system:

@component('mail::button', ['url' => $activationUrl])
Complete Registration
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
