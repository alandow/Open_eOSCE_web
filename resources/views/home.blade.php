@extends('layouts.mainapp')

@section('title')
    Open eOSCE
@endsection


@section('menu')
    @include('layouts.commonmenu')
    <li>
        <a href="#" data-toggle="modal"
           data-target="#setupqrdialog"><i class="fa fa-fw fa-cogs"></i> Setup mobile app</a>
    </li>

@endsection

@section('content')
    <script>

    </script>
    <div class="container-fluid " style="height: 100%;">
        <!-- Examinations for the currently logged on user go here -->
        @if($user->exam_instances->count()>0)
            <div class="row">
                <div class="col-sm-12">
                    <h1 class="page-header">
                        <small>My Examinations</small>
                    </h1>
                    @foreach($user->exam_instances as $exam_instance)
                        <a class="btn btn-primary btn-lg btn-block"
                           href="{{URL::asset('/assess/'.$exam_instance->id)}}">{{$exam_instance->name}}</a>
                    @endforeach
                </div>
            </div>
    @endif
    <!-- Page Heading -->
        @can('is_admin')
        <div class="row">
            <div class="col-sm-12">

                <h1 class="page-header">
                    <small>Notifications</small>
                </h1>
                <ol class="breadcrumb">
                    <li class="active">
                        <i class="fa fa-dashboard"></i> TODO notifications go here. What sort?
                    </li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h1 class="page-header">
                    <small>{{$exams->count()}} currently Running Examinations</small>
                </h1>

                @foreach($exams as $exam)

                    <div class="col-md-12" style="padding-bottom: 10px">
                        <div class="col-md-4"><a href="{{URL::asset('/report/'.$exam->id)}}">
                                {{$exam->name}}
                            </a></div>
                        <div class="col-md-8" style=" border: 1px solid grey;">
                            <div class="progress-bar" role="progressbar"
                                 aria-valuenow="{{round(($exam->student_exam_submissions->count()/$exam->students->count())*100)}}"
                                 aria-valuemin="0" aria-valuemax="100"
                                 style="min-width: 2em; width:{{round(($exam->student_exam_submissions->count()/($exam->students->count()))*100)}}%">
                                {{round(($exam->student_exam_submissions->count()/($exam->students->count()))*100)}}%
                                ({{$exam->student_exam_submissions->count()}}/{{$exam->students->count()}})
                            </div>
                        </div>
                    </div>


                @endforeach

            </div>
        </div>
@endcan

    </div>

    <div id="setupqrdialog" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Setup mobile app</h4>
                </div>
                <div class="modal-body" style="text-align: center">
                    Scan the QR code below with the mobile app to configure it
                    <br/>
                    <img src="{{URL::asset('/setup/configqrimage')}}" style="width: 300px">
                </div>
            </div>
        </div>
    </div>

@endsection
