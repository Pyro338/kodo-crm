@extends('layouts.app')

@section('content')
    @role('superadmin')
    <div class="container">
        <h1>Загрузить XLSX файл с банками</h1>
        <form method="post" action="{{ route('loadBanks') }}" enctype="multipart/form-data">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">
            <input type="file" multiple name="file[]">
            <button type="submit">Загрузить</button>
        </form>
    </div>
    @else
        <div class="alert alert-danger">
            У вас нет прав для просмотра данной страницы
        </div>
    @endrole
@endsection