<!DOCTYPE html>

<html lang="en">
<head>
    @include('layouts.includes')
</head>
<div id="wrapper">
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        @include('layouts.adminmenu')

        @yield('menu')
    </nav>
</div>
<div id="page-wrapper">
    <div class="container-fluid " style="height: 100%;">
        @yield('content')
    </div>
</div>