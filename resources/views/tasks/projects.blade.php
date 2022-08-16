@extends('layouts.crm')

@section('content')
    @can('editTasks')
        <div class="container-fluid text-center page-header">
            @if($filter_type == 0)
                <h1>Добро пожаловать в Kodo CRM</h1>
                <h2>Проекты <a href="#" class="new-project-button"><i class="fa fa-plus-circle" aria-hidden="true"></i></a></h2>
            @elseif($filter_type == 1)
                <h1>Удаленные проекты</h1>
            @else
                <h1>Проекты</h1>
            @endif
        </div>
        <div class="container-fluid page-body">
            <div class="row">
                <div class="col-md-12 projects-panel">
                    <div class="projects-block">
                        @include('tasks.partials.projects_list')
                    </div>
                    <div id="id-token" data-token="{{csrf_token()}}" data-last-page="{{$projects_to_main->lastPage()}}" style="display: none">
                    </div>
                    <div class="divider"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn btn-primary" id="show-more-projects">Показать еще проекты</div>
                        </div>
                    </div>
                    <div class="divider"></div>
                </div>
                <div class="col-md-12 edit-project-panel" style="display: none">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="float-right">
                                <span class="close-edit-project"><i class="fa fa-times" aria-hidden="true"></i></span>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <input type="hidden" id="edit-project-id">
                            <input type="hidden" id="edit-project-workspace_id">
                            <h3>Название*</h3>
                            <input type="text" class="form-control" id="edit-project-title" required>
                            <h3>Краткое название</h3>
                            <input type="text" class="form-control" id="edit-project-alias">
                            <h3>Описание</h3>
                            <div class="for-textarea" id="edit-project-text-block">
                                <div id="edit-project-text-quill-editor"></div>
                                <div id="edit-project-text-quill-toolbar">
                                    <button class="ql-bold"></button>
                                    <button class="ql-italic"></button>
                                    <button class="ql-underline"></button>
                                    <button class="ql-strike"></button>
                                </div>
                            </div>
                            <div class="divider2"></div>
                            <div class="btn btn-primary" id="edit-project-submit">
                                Сохранить
                            </div>
                            <div class="btn btn-danger float-right" id="delete-project-submit">
                                Удалить
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
    <script src="{{asset('js/pages_scripts/projects.js')}}"></script>
@endsection