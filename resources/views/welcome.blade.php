@extends('layouts.app')

@section('content')
    @guest
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="welcome-msg"><strong>Kodo CRM</strong></div>
            </div>
        </div>
    @else
            <div class="flex-center position-ref full-height">
                <div class="content">
                    <div class="welcome-msg">Приветствуем, <strong>{{Auth::user()->name}}.</strong></div>
                </div>
            </div>
            <div class="bottom-content"><a class="process-link" href="#">Процесс</a></div>
    @endguest
@endsection
