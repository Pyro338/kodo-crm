@component('mail::message')
# Просроченные задачи

У вас есть просроченные задачи. Количество просроченных задач: {{$tasks_count}}

<a href="{{url('/').'/list/'.$recipient->workspace_id.'/'.$recipient->id.'/0/0/'}}">Посмотреть задачи</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent