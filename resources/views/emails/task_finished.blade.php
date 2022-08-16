@component('mail::message')
# Задача была завершена

Задача "{{$task->title}}", за которой вы следите, была завершена пользователем {{$initiator->name}}

<a href="{{url('/').'/list/'.$task->workspace_id.'/'.$recipient->id.'/0/0/'.$task->id}}">Посмотреть задачу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent
