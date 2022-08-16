@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                @include('banks.partials.left-menu')
            </div>
            <div class="col-md-10">
                <div class="container">
                    @can('editBanks')
                        <h1>Банк: {{$bank->name}}</h1>
                        <div class="text-left">
                            <table class="table">
                                <tbody>
                                <tr>
                                    <th>Место на 01.08.2016</th>
                                    <td>{{$bank->place}}</td>
                                </tr>
                                <tr>
                                    <th>Рег. номер</th>
                                    <td>{{$bank->reg_number}}</td>
                                </tr>
                                <tr>
                                    <th>Город</th>
                                    <td>{{$bank->city}}</td>
                                </tr>
                                <tr>
                                    <th>Место по активам на 01.08.2016</th>
                                    <td>{{$bank->place_active}}</td>
                                </tr>
                                <tr>
                                    <th>Кредиты физ. лицам, всего на 01.08.2016, млн. руб.</th>
                                    <td>{{$bank->credits}}</td>
                                </tr>
                                <tr>
                                    <th>Лицензия</th>
                                    <td>{{$bank->license}}</td>
                                </tr>
                                <tr>
                                    <th>Статус лицензии</th>
                                    <td>{{$bank->license_status}}</td>
                                </tr>
                                <tr>
                                    <th>Адрес. Контакты</th>
                                    <td>{{$bank->contacts}}</td>
                                </tr>
                                <tr>
                                    <th>Комментарии</th>
                                    <td>{{$bank->comments}}</td>
                                </tr>
                                </tbody>
                            </table>
                            <a href="{{route('banks.index')}}" class="btn btn-primary">Вернуться</a>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            У вас нет прав для просмотра данной страницы
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection