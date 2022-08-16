@component('mail::message')
# Добро пожаловать в Kodo CRM!

Вы успешно зарегестрированны в системе Kodo CRM.
<p><i>{{$message}}</i></p>

Ваши данные для авторизации:
Email: {{$user->email}}
Пароль: {{$password}}

Не забудьте сменить сгенерированный пароль в Личном кабинете

<a href="{{url('/').'/login/'}}">Начать работу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent