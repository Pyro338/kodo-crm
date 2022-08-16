@component('mail::message')
    # Задача была удалена

    Задача "{{$task->title}}", за которой вы следите, была удалена

    <a href="{{url('/').'/list/'.$task->workspace_id.'/'.$recipient_id.'/0/0/'.$task->id}}">Посмотреть задачу</a>

    С уважением,<br>
    {{ config('app.name') }}
@endcomponent
