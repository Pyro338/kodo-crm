@component('mail::message')
# Новое сообщение в беседе

В беседе "{{$conversation->title}}", за которой вы следите, было добавлено новое сообщение

<a href="{{url('/').'/conversations/' . $conversation->workspace_id . '/' .$conversation->id}}">Посмотреть беседу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent
