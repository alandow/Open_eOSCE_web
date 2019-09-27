<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Open eOSCE</title>
@include('layouts.includes')

{{--<link rel="stylesheet" href="{{URL::asset('resources/assets/css/sortertheme.css')}}"  >--}}

{{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}


<!-- JavaScripts -->

    {{--<script src="{{ URL::asset('resources/assets/js/jquery.tablesorter.min.js') }}"></script>--}}
    {{--<script src="{{ URL::asset('resources/assets/js/jquery.tablesorter.widgets.js') }}"></script>--}}
</head>
<body id="app-layout">


@yield('content')

</body>
</html>
