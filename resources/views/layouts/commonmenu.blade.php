<!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->

@can('is_admin')
<li class="active">
    <a href="{{ url('/exam') }}"><i class="fa fa-fw fa-dashboard"></i> Examination Preparation and Administration</a>
</li>
<li>
    <a href="{{ url('/report') }}"><i class="fa fa-fw fa-bar-chart-o"></i> Examination Monitoring and Reporting</a>
</li>
<li>
    <a href="{{ url('/archive') }}"><i class="fa fa-fw fa-table"></i> Examination Archive</a>
</li>

@endcan
<!-- /.navbar-collapse -->