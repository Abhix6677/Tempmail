@component('mail::message')
# You have a new message

Name: {{ $name }}
Email: {{ $email }}
Message: {{ $message }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
