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
                        <h1>Редактирование банка</h1>
                        <form action="{{route('banks.update', $bank->id)}}" method="post" class="form-horisontal">
                            {{csrf_field()}}
                            <input type="hidden" name="_method" value="put">
                            @include('banks.partials.form')
                        </form>
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