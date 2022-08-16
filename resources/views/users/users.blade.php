@extends('layouts.app')

@section('content')
    <div class="container">
        @can('editUsers')
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <ul class="nav nav-tabs text-center">
                        <li class="active btn btn-default" style="margin-right: 10px;">
                            <a data-toggle="tab" href="#panel1" class="active">Пользователи</a>
                        </li>
                        <li class="btn btn-default">
                            <a data-toggle="tab" href="#panel2">Отделы</a>
                        </li>
                    </ul>
                    <div class="divider"></div>
                    <div class="tab-content">
                        <div id="panel1" class="tab-pane active">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Пользователи</h3>
                                </div>
                                <div class="card-body">
                                    @role('superadmin')
                                    <a href="{{route('usersCreate')}}" class="btn btn-primary">Пригласить пользователя</a>
                                    <div class="divider"></div>
                                    @endrole
                                    <table class="table text-left table-sm table-responsive">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Имя</th>
                                            @role('superadmin')
                                            <th>Роль</th>
                                            @endrole
                                            <th>Отдел</th>
                                            <th>Контакты</th>
                                            @role('superadmin')
                                            <th>Доступ</th>
                                            <th class="text-right">Действия</th>
                                            @endrole
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($users as $user)
                                            @if($user->is_active == 1)
                                            <tr id="user-item-{{$user->id}}">
                                                <td>
                                                    <a href="/list/{{$user->workspace_id}}/{{$user->id}}/0/0"
                                                       class="users-item"
                                                       style="background-color: {{$user->color}};
                                                               background-image: {{$user->background_image}};"
                                                       data-user_fullname="{{$user->fullname}}"
                                                       data-user_department="{{$user->department}}"
                                                       data-user_office="{{$user->office}}"
                                                    >
                                                        {{$user->first_letters}}
                                                    </a>
                                                </td>
                                                <td>
                                                    <h3 class="user-fullname">{{$user->fullname}}</h3>
                                                </td>
                                                @role('superadmin')
                                                <td>
                                                    <div class="user-role_name popup-label"
                                                         data-id="{{$user->id}}"
                                                         id="user-role-{{$user->id}}"
                                                    >
                                                        {{$user->role_name or 'Роль не указана'}}
                                                    </div>
                                                    <div class="user-role-container popup-container"
                                                         style="display: none"
                                                         id="user-role-container-{{$user->id}}"
                                                    >
                                                        <select id="user-role-{{$user->id}}"
                                                                class="form-control user-role"
                                                                data-id="{{$user->id}}"
                                                                data-key="role"
                                                        >
                                                            <option value="" id="user-role-{{$user->id}}-" disabled></option>
                                                            <option value="superadmin"
                                                                    id="user-role-{{$user->id}}-superadmin"
                                                                    @if($user->role == 'superadmin') selected="selected" @endif
                                                            >
                                                                Management
                                                            </option>
                                                            <option value="user"
                                                                    id="user-role-{{$user->id}}-user"
                                                                    @if($user->role == 'user') selected="selected" @endif
                                                            >
                                                                Technology
                                                            </option>
                                                            <option value="operator"
                                                                    id="user-role-{{$user->id}}-operator"
                                                                    @if($user->role == 'operator') selected="selected" @endif
                                                            >
                                                                Operations
                                                            </option>
                                                        </select>
                                                    </div>
                                                </td>
                                                @endrole
                                                <td>
                                                    <div class="user-department popup-label"
                                                         data-id="{{$user->id}}"
                                                         id="user-department-{{$user->id}}"
                                                    >
                                                        {{$user->department or 'Отдел не указан'}}
                                                    </div>
                                                    @role('superadmin')
                                                    <div class="user-department-container popup-container"
                                                         style="display: none"
                                                         id="user-department-container-{{$user->id}}"
                                                    >
                                                        <select id="user-department-{{$user->id}}"
                                                                class="form-control"
                                                                data-id="{{$user->id}}"
                                                                data-key="department"
                                                        >
                                                            <option value="" id="user-department-{{$user->id}}-" disabled></option>
                                                            @foreach($workspaces as $workspace)
                                                                <option value="{{$workspace->id}}"
                                                                        id="user-department-{{$user->id}}-{{$workspace->id}}"
                                                                        @if($user->workspace_id == '$workspace->id')
                                                                            selected="selected"
                                                                        @endif
                                                                >
                                                                    {{$workspace->title}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @endrole
                                                    <div class="user-office popup-label"
                                                         data-id="{{$user->id}}"
                                                         id="user-office-{{$user->id}}"
                                                    >
                                                        {{$user->office or 'Должность не указана'}}
                                                    </div>
                                                    @role('superadmin')
                                                    <div class="user-office-container popup-container"
                                                         style="display: none"
                                                         id="user-office-container-{{$user->id}}"
                                                    >
                                                        <input type="text"
                                                               id="user-office-{{$user->id}}"
                                                               class="form-control"
                                                               data-id="{{$user->id}}"
                                                               data-key="office"
                                                        >
                                                    </div>
                                                    @endrole
                                                </td>
                                                <td>
                                                    <div class="user-phone">
                                                        <a href="tel://{{$user->phone}}">{{$user->phone}}</a>
                                                    </div>
                                                    <div class="user-email">
                                                        <a href="mailto:{{$user->email}}">{{$user->email}}</a>
                                                    </div>
                                                </td>
                                                @role('superadmin')
                                                <td>
                                                    <span class="icon-button user-access"
                                                          id="user-access-{{$user->id}}"
                                                          data-id="{{$user->id}}"
                                                    >
                                                        <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                                    </span>
                                                    <div class="dialog user-access-dialog" id="user-access-dialog-{{$user->id}}">
                                                        <ul data-user_id="{{$user->id}}">
                                                            @foreach($user->workspaces_info as $workspace_info)
                                                                    <li class="user-access-item {{$workspace_info->item_class}}"
                                                                        id="user-access-item-{{$user->id}}-{{$workspace_info->id}}"
                                                                        data-user_id="{{$user->id}}"
                                                                        data-workspace_id="{{$workspace_info->id}}"
                                                                    >
                                                                        <span class="access-toggle-button"
                                                                              id="access-toggle-button-{{$user->id}}-{{$workspace_info->id}}"
                                                                              data-user_id="{{$user->id}}"
                                                                              data-workspace_id="{{$workspace_info->id}}"
                                                                        >
                                                                            <i class="fa {{$workspace_info->toggle_class}}"
                                                                               aria-hidden="true"
                                                                            >
                                                                            </i>
                                                                        </span>
                                                                        {{$workspace_info->title}}
                                                                    </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <a href="#" class="delete-user" data-id="{{$user->id}}">
                                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                                @endrole
                                            </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="panel2" class="tab-pane">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Отделы</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table  table-sm table-responsive" id="workspaces-list">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Название</th>
                                                <th>Описание</th>
                                                <th class="text-right">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($workspaces as $workspace)
                                                <tr id="workspace-item-{{$workspace->id}}">
                                                    <td class="text-left">
                                                        <span id="workspace-title-{{$workspace->id}}">
                                                            {{$workspace->title}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span id="workspace-description-{{$workspace->id}}">
                                                            {{$workspace->description}}
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <span id="edit-workspace-{{$workspace->id}}"
                                                              class="icon-button edit-workspace"
                                                              data-id="{{$workspace->id}}"
                                                        >
                                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                                        </span>
                                                        <span id="delete-workspace-{{$workspace->id}}"
                                                              class="icon-button delete-workspace"
                                                              data-id="{{$workspace->id}}"
                                                        >
                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr id="workspace-item-edit-{{$workspace->id}}"
                                                    style="display: none"
                                                    class="workspace-item-edit"
                                                >
                                                    <td class="text-left">
                                                        <input type="text"
                                                               id="edit-workspace-title-{{$workspace->id}}"
                                                               value="{{$workspace->title}}"
                                                        >
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               id="edit-workspace-description-{{$workspace->id}}"
                                                               value="{{$workspace->description}}"
                                                        >
                                                    </td>
                                                    <td class="text-right">
                                                        <span id="edit-workspace-save"
                                                              class="icon-button save-workspace"
                                                              data-id="{{$workspace->id}}"
                                                        >
                                                            <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr id="empty-workspaces-row">
                                                    <td colspan="3">
                                                        <div class="alert alert-warning">
                                                            <p>Пока не создан ни один отдел</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <hr>
                                    <div class="text-left">
                                        <h4>Новый отдел</h4>
                                        <div class="row" style="margin-bottom: 20px;">
                                            <div class="col-md-2">
                                                <label for="new-workspace-title">Название</label>
                                            </div>
                                            <div class="col-md-10">
                                                <input type="text" id="new-workspace-title" placeholder="Название">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label for="new-workspace-description">Описание</label>
                                            </div>
                                            <div class="col-md-10">
                                                <textarea type="text" id="new-workspace-description" placeholder="Описание"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="btn btn-primary" id="new-workspace-submit">
                                                    Создать
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
                Вы не имеете прав для просмотра этой страницы
            </div>
        @endcan
    </div>
    <script src="{{asset('js/pages_scripts/users.js')}}"></script>
@endsection
