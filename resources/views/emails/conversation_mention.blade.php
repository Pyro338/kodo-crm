@component('mail::message')
# Вас упомянули в беседе

Вас упомянули в беседе "{{$conversation->title}}"

<a href="{{url('/').'/conversations/' . $conversation->workspace_id . '/' . $conversation->id}}">Посмотреть беседу</a>

С уважением,<br>
{{ config('app.name') }}
@endcomponent