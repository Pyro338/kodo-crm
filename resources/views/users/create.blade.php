@extends('layouts.app')
@section('content')
    <div class="container">
        @can('editUsers')
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card text-left">
                        <div class="card-header">Пригласите нового пользователя</div>
                        <div class="card-body">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <strong>Упс!</strong> Проблемы с введенными данными.<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(Session::has('message'))
                                <div class="alert alert-success" id="message">
                                    {{Session::get('message')}}
                                </div>
                            @endif
                            <form class="form-horisontal" id="user-create-form">
                                {{csrf_field()}}
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="name">Nickname*</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="name" name="name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="last_name">Фамилия</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="last_name" name="last_name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="first_name">Имя</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="first_name" name="first_name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="middle_name">Отчество</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="middle_name" name="middle_name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="email">Email*</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control" name="email" id="email">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="phone">Телефон</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="role">Роль*</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select id="role" class="form-control" name="role">
                                            <option value="0" selected></option>
                                            <option value="superadmin">superadmin</option>
                                            <option value="user">user</option>
                                            <option value="operator">operator</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="workspace_id">Отдел*</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="hidden" id="department" name="department">
                                        <select id="workspace-id" class="form-control" name="workspace_id">
                                            <option value="0" selected></option>
                                            @foreach($workspaces as $workspace)
                                                <option value="{{$workspace->id}}">
                                                    {{$workspace->title}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-3 text-right">
                                        <label for="message">Сообщение</label>
                                    </div>
                                    <div class="col-md-9">
                                        <textarea name="message" id="message" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="btn btn-primary" id="submit">Отправить</div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-danger">
                Вы не имеете прав для просмотра этой страницы
            </div>
        @endcan
    </div>
    <script>
      let body = $('body');

      $('#workspace-id').change(function(){
        $('#department').val($('#workspace-id option:selected').text().trim());
      });

      $('#submit').click(function (e) {
        e.preventDefault();
        let name = $('#name').val();
        let email = $('#email').val();
        let role = $('#role').val();
        let workspace_id = $('#workspace-id').val();
        let validate = true;
        if (!name || $('#name').val().trim() == '') {
          alert('Поле "Nickname" обязательное для заполнения');
          validate = false;
        }
        if (!email || $('#email').val().trim() == '') {
          alert('Поле "Email" обязательное для заполнения');
          validate = false;
        }
        if(checkEmailUnique(email) == false){
          alert('Пользователь с таким адресом электронной почты уже существует в системе!');
          validate = false;
        }
        if (!role || $('#role').val() == 0) {
          alert('Поле "Роль" обязательное для заполнения');
          validate = false;
        }
        if (!workspace_id || $('#workspace-id').val() == 0) {
          alert('Поле "Отдел" обязательное для заполнения');
          validate = false;
        }
        if (validate) {
          createUserAjax();
        }
      })
    </script>
@endsection