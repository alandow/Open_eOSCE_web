<!DOCTYPE html>

<html lang="en">
<head>

    @include('layouts.includes')

</head>
<body>

<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        @include('layouts.adminmenu')
        <div class="collapse navbar-collapse navbar-ex1-collapse">

            @yield('menu0')

            <ul class="nav navbar-nav side-nav">
                @yield('menu')
            </ul>
        </div>
    </nav>

    <div id="page-wrapper">

    @yield('content')
    <!-- /.container-fluid -->

    </div>
    <!-- /#page-wrapper -->

</div>


</body>
</html>
