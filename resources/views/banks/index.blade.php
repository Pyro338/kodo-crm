@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                @include('banks.partials.left-menu')
            </div>
            <div class="col-md-10">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            @can('editBanks')
                                <h1>Банки</h1>
                                <a href="{{route('banks.create')}}" class="btn btn-primary">Новый банк</a>
                                <div class="divider"></div>
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <!--th>Место на 01.08.2016</th>
                                        <th>Рег. номер</th-->
                                        <th>Наименование банка</th>
                                        <th>Город</th>
                                        <!--th>Место по активам на 01.08.2016</th>
                                        <th>Кредиты физ. лицам, всего на 01.08.2016, млн. руб.</th>
                                        <th>Лицензия</th>
                                        <th>Статус лицензии</th>
                                        <th>Адрес. Контакты</th-->
                                        <th>Комментарии</th>
                                        <th>Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($banks as $bank)
                                        <tr>
                                        <!--td>{{$bank->place}}</td>
                            <td>{{$bank->reg_number}}</td-->
                                            <td><a href="{{route('banks.show', $bank->id)}}">{{$bank->name}}</a></td>
                                            <td>{{$bank->city}}</td>
                                        <!--td>{{$bank->place_active}}</td>
                            <td>{{$bank->credits}}</td>
                            <td>{{$bank->license}}</td>
                            <td>{{$bank->license_status}}</td>
                            <td>{{$bank->contacts}}</td-->
                                            <td>{{$bank->comments}}</td>
                                            <td>
                                                <form onsubmit="if(confirm('Удалить?')){return true} else{return false}"
                                                      action="{{route('banks.destroy', $bank->id)}}" method="post">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    {{csrf_field()}}
                                                    <a class="btn btn-default" href="{{route('banks.edit', $bank->id)}}" style="margin-top: 0;">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button type="submit" class="btn btn-default" style="margin-top: 0;">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center"><h2>Данные отсутствуют</h2></td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <ul class="pagination pull-right">
                                                {{$banks->links()}}
                                            </ul>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            @else
                                <div class="alert alert-danger">
                                    У вас нет прав для просмотра данной страницы
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
