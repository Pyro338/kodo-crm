@component('mail::message')
# Вас упомянули в комментарии

Пользователь {{$initiator->name}} упомянул в комментарии к задаче "{{$task->title}}"

<a href="{{url('/').'/list/'.$recipient->workspace_id.'/'.$recipient->id.'/0/0/'.$task->id}}">Посмотреть задачу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent