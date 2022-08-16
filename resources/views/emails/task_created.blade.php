@component('mail::message')
# Новая задача для вас

Для вас была создана новая задача: "{{$task->title}}". Просмотреть ее вы можете по ссылке ниже

<a href="{{url('/').'/list/'.$task->workspace_id.'/'.$recipient->id.'/0/0/'.$task->id}}">Посмотреть задачу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent
