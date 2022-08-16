@component('mail::message')
# Добро пожаловать в Kodo CRM

Можете начать работу с системой.

@component('mail::button', ['url' => route('welcome', 0)])
Начать
@endcomponent

С уважением,<br>
{{ config('app.name') }}
@endcomponent
