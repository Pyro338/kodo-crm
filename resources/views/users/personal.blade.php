@extends('layouts.crm')

@section('content')
    @can('editTasks')
        <div class="container-fluid text-center page-header">
            <h1>Личный кабинет</h1>
        </div>
        <div class="container-fluid page-body">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <ul class="nav nav-tabs justify-content-center">
                        <li class="active"><a data-toggle="tab" href="#panel1" class="active">Персональные данные</a></li>
                        <li><a data-toggle="tab" href="#panel2">Оповещения</a></li>
                        <li><a data-toggle="tab" href="#panel3">Аватар</a></li>
                        <li><a data-toggle="tab" href="#panel4">Смена пароля</a></li>
                    </ul>

                    <div class="tab-content">
                        <div id="panel1" class="tab-pane active">
                            <div class="card">
                                <div class="card-header">Персональные данные</div>
                                <div class="card-body">
                                    <form class="form-horisontal" method="post">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$user->id}}" id="user-update-id">
                                        <div class="form-croup row">
                                            <label for="user-update-name" class="col-md-4 col-form-label text-md-right">Nickname</label>
                                            <div class="col-md-6">
                                                <input type="text" name="name" class="form-control" id="user-update-name" value="{{$user->name}}"
                                                       placeholder="Nickname">
                                            </div>
                                        </div>
                                        <div class="form-croup row">
                                            <label for="user-update-last_name" class="col-md-4 col-form-label text-md-right">Фамилия</label>
                                            <div class="col-md-6">
                                                <input type="text" name="last_name" class="form-control" id="user-update-last_name"
                                                       value="{{$user->last_name}}" placeholder="Фамилия">
                                            </div>
                                        </div>
                                        <div class="form-croup row">
                                            <label for="user-update-first_name" class="col-md-4 col-form-label text-md-right">Имя</label>
                                            <div class="col-md-6">
                                                <input type="text" name="first_name" class="form-control" id="user-update-first_name"
                                                       value="{{$user->first_name}}" placeholder="Имя">
                                            </div>
                                        </div>
                                        <div class="form-croup row">
                                            <label for="user-update-middle_name" class="col-md-4 col-form-label text-md-right">Отчество</label>
                                            <div class="col-md-6">
                                                <input type="text" name="middle_name" class="form-control" id="user-update-middle_name"
                                                       value="{{$user->middle_name}}" placeholder="Отчество">
                                            </div>
                                        </div>
                                        <div class="form-croup row">
                                            <label for="user-update-email" class="col-md-4 col-form-label text-md-right">Email</label>
                                            <div class="col-md-6">
                                                <input type="text" name="email" class="form-control" id="user-update-email"
                                                       value="{{$user->email}}" placeholder="Email">
                                            </div>
                                        </div>
                                        <div class="form-croup row">
                                            <label for="user-update-phone" class="col-md-4 col-form-label text-md-right">Телефон</label>
                                            <div class="col-md-6">
                                                <input type="text" name="phone" class="form-control" id="user-update-phone"
                                                       value="{{$user->phone}}" placeholder="Телефон">
                                            </div>
                                        </div>
                                        <div class="form-croup row">
                                            <label for="user-update-about" class="col-md-4 col-form-label text-md-right">Обо мне</label>
                                            <div class="col-md-6">
                                                <label class="for-textarea">
                                            <textarea name="about" id="user-update-about" class="form-control" placeholder="Обо мне" rows="3"
                                            >{{$user->about}}</textarea>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <div class="col-md-6 offset-md-4">
                                                <button type="submit" class="btn btn-primary" id="user-update-submit">
                                                    Сохранить
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div id="panel2" class="tab-pane fade">
                            <div class="card">
                                <div class="card-header">Оповещения</div>
                                <div class="card-body">
                                    <div>
                                        <label>
                                            <input type="checkbox" id="subscribe-radio"
                                                   @if($user->subscribe == 1)checked="checked"@endif>
                                            Email оповещения о задачах
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="checkbox" id="conversation-subscribe-radio"
                                                   @if($user->conversations_subscribe == 1)checked="checked"@endif>
                                            Email оповещения об обсуждениях
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="checkbox" id="enable-sound-radio"
                                                   @if($user->enable_sound == 1)checked="checked"@endif>
                                            Звук уведомлений
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="panel3" class="tab-pane fade">
                            <div class="card">
                                <div class="card-header">Аватар</div>
                                <div class="card-body justify-content-center avatar-body">
                                    <input type="hidden" id="avatar-id-input">
                                    <input type="file" id="avatar-input" accept="image/*" style="display: none">
                                    <label for="avatar-input" class="avatar">
                                        <div id="avatar-block" style="background-image: url('{{$user->avatar_link}}')"></div>
                                    </label>
                                    @if($user->avatar)
                                        <div class="btn btn-danger" id="avatar-delete-button">Удалить</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div id="panel4" class="tab-pane fade">
                            <div class="card">
                                <div class="card-header">Смена пароля</div>
                                <div class="card-body justify-content-center">
                                    <div class="row form-group">
                                        <div class="col-md-4 text-right">
                                            <label for="old-password">Старый пароль</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" class="form-control" id="old-password">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-md-4 text-right">
                                            <label for="new-password">Новый пароль</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" class="form-control" id="new-password">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-md-4 text-right">
                                            <label for="confirm-password">Подтверждение</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" class="form-control" id="confirm-password">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="btn btn-primary" id="password-submit">
                                                Сменить
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            У вас нет прав для просмотра данной страницы
        </div>
    @endcan
    <script src="{{asset('js/pages_scripts/personal.js')}}"></script>
@endsection