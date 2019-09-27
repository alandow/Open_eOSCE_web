<div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="{{ url('/') }}">Open eOSCE</a>
</div>
<!-- Top Menu Items -->
<ul class="nav navbar-right top-nav">
    @can('is_admin')
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cogs"></i>&nbsp;System
                Administration <b class="caret"></b></a>
            <ul class="dropdown-menu alert-dropdown">
                <li>
                    <a href="{{ url('student')}}"><i class="fa fa-mortar-board"></i>&nbsp;Students Management</a>
                </li>
                <li>
                    <a href="{{ url('user')}}"><i class="fa fa-users"></i>&nbsp;System Users</a>
                </li>
                <li>
                    <a href="{{ url('setup')}}"><i class="fa fa-cogs"></i>&nbsp;System Setup</a>
                </li>

                </li>
                <li class="divider"></li>
                <li>
                    <a href="#">View All</a>
                </li>
            </ul>
        </li>
    @endcan
    <li class="dropdown">
    @if (Auth::guest())
        <li><a href="{{ url('/login') }}">Login</a></li>
    @else
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                Logged in as: <b>{{ Auth::user()->name }} </b><span class="caret"></span>
            </a>

            <ul class="dropdown-menu" role="menu">
                @if(Auth::user()->hasSudoed())
                    <li>
                        <form action="{{ route('sudosu.return') }}" method="post">
                            {!! csrf_field() !!}
                            <button class="btn btn-danger " type="submit">
                                Return to {{Auth::user()->getOriginalUser()->name}}</button>
                        </form>
                    </li>
                @endif
                <li>
                    <a href="{{ route('user.my') }}">
                        My profile
                    </a>
                </li>
                <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
            </ul>


        </li>
        @endif
        </li>
</ul>