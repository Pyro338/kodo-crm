@component('mail::message')
# Новый комментарий

К задаче "{{$task->title}}", за которой вы следите, был оставлен комментарий:

{!! $comment->text !!}

<a href="{{url('/').'/list/'.$task->workspace_id.'/'.$recipient->id.'/0/0/'.$task->id.'/'}}">Посмотреть задачу</a>
<br>
С уважением,<br>
{{ config('app.name') }}
@endcomponent