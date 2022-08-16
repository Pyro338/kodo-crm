<!doctype html>
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
    <script src="{{ asset('js/jquery.js')}}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/autosize.js') }}"></script>
    <script src="{{ asset('js/main.js')}}"></script>
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/autobahn.js') }}"></script>
    <script src="{{ asset('js/emojionearea.js') }}"></script>
</head>
<body>
<div id="app"></div>
<script src="{{asset('js/app.js')}}" ></script>
</body>
</html>