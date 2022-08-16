<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.structure.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.js')}}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/autosize.js') }}"></script>
    <script src="{{ asset('js/main.js')}}"></script>
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/autobahn.js') }}"></script>
</head>
<body>
<input type="hidden" id="ip" value="{{isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1'}}">
<div class="admin-layout">
    <div class="global-wrapper js-open-menu-wrapper"><a class="logo js-open-menu-link" href="#"></a>
        <div class="global-nav-wrapper">
            <nav class="global-nav">
                @guest
                    <div class="global-nav-item"><a class="js-nav-link" href="{{route('login')}}" data-section="task-section">Войти</a></div>
                @else
                    @role('superadmin|manager|user|operator')
                        @can('editUsers')
                                <div class="global-nav-item">
                                    <a class="js-nav-link" href="{{route('usersIndex')}}" data-section="task-section">
                                        Пользователи
                                    </a>
                                </div>
                        @endcan
                        @can('editBanks')
                                <div class="global-nav-item">
                                    <a class="js-nav-link" href="{{env('BANKS_URL')}}" data-section="task-section">
                                        Базы
                                    </a>
                                </div>
                        @endcan
                        @can('editTasks')
                                <div class="global-nav-item">
                                    <a class="js-nav-link" href="{{route('projects', [
                                    'workspace_id' => Auth::user()->workspace_id,
                                    'filter_type' => 0])}}"
                                       data-section="task-section">
                                        Задачи
                                    </a>
                                </div>
                        @endcan
                    @else
                            <div class="alert">
                                Вы успешно зарегистрированы, но вам еще не назначены права доступа.
                            </div>
                    @endrole
                            <div class="global-nav-item">
                                <a class="js-nav-link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                    Выйти
                                </a>
                            </div>
                 @endguest
            </nav>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <div class="global-container">
            <main class="global-content">
                <section class="global-section task-page current-section js-nav-section" data-section="task-section">
                    @yield('content')
                </section>
            </main>
        </div>
        <div class="global-framebox"></div>
    </div>
</div>
<img src="{{asset('img/loading.gif')}}" class="loading">
</body>
</html>
