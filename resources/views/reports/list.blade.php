@extends('layouts.mainapp')

{{--Access control this--}}
@section('menu')

    <li>
        <a href="{{ url('/examemails') }}"><i class="fa fa-fw fa-cog"></i>Email templates</a>
    </li>
@endsection


@section('content')
    <style>
        .modal-body {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
        }

        .fa-check {
            color: green;
        }

        .fa-times {
            color: red;
        }
    </style>
    <script>

        var currentid = -1;

        $(document).ready(function () {

            $('select').select2();


            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                if ((typeof $(this).data('id')) !== 'undefined') {
                    currentid = $(this).data('id');

                }
            });



            $('#deletedialog').submit(function (event) {
                $('#deletedialog').modal('hide');
                var vars = $("#deletedialog").find("form").serializeArray();
                vars.push({name: 'id', value: currentid});
                //vars.push({name: '_method', value: 'DELETE'});
                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                deleteExam(vars);
            });

        });




        function deleteExam(vars) {
            $.ajax({
                url: '{{URL::to('exam/ajaxdestroy')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data == "0") {
                        location.reload();
                    } else {
                        alert('something went wrong with the delete');
                    }
                }
            });
        }



    </script>
    {{--{!! Breadcrumbs::render('exam.index') !!}--}}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <div style="width: 100%; "></div>
        <div style="float: left; padding-left: 10px">
            <h4>Active exams</h4>
        </div>
        &nbsp;
        <table class="table table-striped">
            <thead class="thead-inverse">
            <tr>
                <th class="headerSortable header"> @sortablelink ('name', 'Session Name')</th>
                <th class="headerSortable header"> @sortablelink ('owner_id', 'Owner')</th>
                <th class="headerSortable header"> @sortablelink ('start_datetime', 'Started at')</th>
                <th class="headerSortable header"> @sortablelink ('status', 'Status')(TODO format)</th>
                <th class="header">Completed</th>
                <th></th>
            </tr>
            </thead>
            @foreach ($activeexams as $exam)

                <tr>
                    <td>
                        <a href="{{URL::asset('/report/'.$exam->id)}}">
                            {{$exam->name}}
                        </a>
                    </td>
                    <td>
                        {{$exam->owner->name}}
                    </td>

                    <td>
                        {{date_format($exam->created_at, 'd/m/Y H:i:s A')}}
                    </td>
                    <td>
                        {{$exam->status}}
                    </td>
                    <td>
                        <div class="progress-bar" role="progressbar"
                             aria-valuenow="{{round(($exam->student_exam_submissions->count()/$exam->students->count())*100)}}"
                             aria-valuemin="0" aria-valuemax="100"
                             style="min-width: 2em; width:{{round(($exam->student_exam_submissions->count()/($exam->students->count()))*100)}}%">
                            {{round(($exam->student_exam_submissions->count()/($exam->students->count()))*100)}}% ({{$exam->student_exam_submissions->count()}}/{{$exam->students->count()}})
                        </div>
                    </td>
                    <td>
                        <a href="#" data-toggle="modal" data-id="{{$exam->id}}"
                           data-target="#deletedialog">
                            <i class="fa fa-times" style="font-size: 2em; color: red" aria-hidden="true"></i>

                        </a>
                    </td>

            @endforeach

        </table>

        {!! $activeexams->appends(Request::all())->render() !!}
    </fieldset>

    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <div style="width: 100%; "></div>
        <div style="float: left; padding-left: 10px">
            <h4>Completed exams</h4>
        </div>
        &nbsp;
        <table class="table table-striped">
            <thead class="thead-inverse">
            <tr>
                <th class="headerSortable header"> @sortablelink ('name', 'Session Name')</th>
                <th class="headerSortable header"> @sortablelink ('owner_id', 'Owner')</th>
                <th class="headerSortable header"> @sortablelink ('start_datetime', 'Started at')</th>
                <th class="headerSortable header"> @sortablelink ('end_datetime', 'Ended at')</th>
                <th class="headerSortable header"> @sortablelink ('status', 'Status')(TODO format)</th>
                <th class="header">Completed</th>
                <th></th>
            </tr>
            </thead>
            @foreach ($completedexams as $exam)

                <tr>
                    <td>
                        <a href="{{URL::asset('/report/'.$exam->id)}}">
                            {{$exam->name}}
                        </a>
                    </td>
                    <td>
                        {{$exam->owner->name}}
                    </td>

                    <td>
                        {{date_format($exam->created_at, 'd/m/Y H:i:s A')}}
                    </td>
                    <td>
                        {{date_format($exam->created_at, 'd/m/Y H:i:s A')}}
                    </td>
                    <td>
                        {{$exam->status}}
                    </td>
                    <td>
                        <div class="progress-bar" role="progressbar"
                             aria-valuenow="{{round(($exam->student_exam_submissions->count()/$exam->students->count())*100)}}"
                             aria-valuemin="0" aria-valuemax="100"
                             style="min-width: 2em; width:{{round(($exam->student_exam_submissions->count()/($exam->students->count()))*100)}}%">
                            {{round(($exam->student_exam_submissions->count()/($exam->students->count()))*100)}}% ({{$exam->student_exam_submissions->count()}}/{{$exam->students->count()}})
                        </div>

                    </td>
                    <td>
                        <a href="#" data-toggle="modal" data-id="{{$exam->id}}"
                           data-target="#deletedialog">
                            <i class="fa fa-times" style="font-size: 2em; color: red" aria-hidden="true"></i>

                        </a>
                    </td>

            @endforeach

        </table>

        {!! $activeexams->appends(Request::all())->render() !!}
    </fieldset>

    {{--  Deleting a contact event  --}}
    <div id="deletedialog" class="modal fade" role="dialog">
        <div class="modal-dialog" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Really delete exam?</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('form_common.deletedialog')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


@stop

