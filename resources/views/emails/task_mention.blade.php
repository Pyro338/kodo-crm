@component('mail::message')
# Вас упомянули в задаче

Пользователь {{$initiator->name}} упомянул в задаче "{{$task->title}}"

<a href="{{url('/').'/list/'.$task->workspace_id.'/'.$recipient->id.'/0/0/'.$task->id}}">Посмотреть задачу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent