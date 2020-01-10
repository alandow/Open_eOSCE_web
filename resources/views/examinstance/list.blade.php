@extends('layouts.mainapp')

{{--Access control this--}}
@section('menu')
    <li>
        <a href="{{ url('/examtemplates') }}"><i class="fa fa-fw fa-cog"></i>Examination templates</a>
    </li>
    <li>
        <a href="{{ url('/examitemtemplates') }}"><i class="fa fa-fw fa-cog"></i>Item templates</a>
    </li>
    <li>
        <a href="{{ url('/reportemails') }}"><i class="fa fa-fw fa-cog"></i>Feedback email templates</a>
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


            $('#newdialog').on('shown.bs.modal', function () {
                //$("#newuserform").validator();
                //changevalidation('newdialog');
            });


            // New patient event dialogue
            $('#newdialog').submit(function (event) {
                if (!event.isDefaultPrevented()) {
                    $('#newdialog').modal('hide');
                    var vars = $("#newexamform").serializeArray();

                    console.log(vars);
                    // cancels the form submission
                    event.preventDefault();
                    waitingDialog.show();
                    submitNewForm(vars);
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


        /*
         *
         * New user AJAX
         *
         */
        function submitNewForm(vars) {
            $.ajax({
                url: '{{URL::to('exam/ajaxstore')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    console.log(data.status)
                    if (data.status > 0) {
                        location.reload();
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }


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


        /*
         *
         * Helper functions
         *
         */

        // populates a form with data returned from Laravel
        function populate(frm, data) {
            $.each(data, function (key, value) {
                var $ctrl = $('[name=' + key + ']', frm);
                if ($ctrl.is("select")) {
                    $ctrl.select2('val', value);
                } else {

                    switch ($ctrl.attr("type")) {
                        case "text" :
                        case "hidden":
                            $ctrl.val(value);
                            break;
                        case "radio" :
                        case "checkbox":
                            $ctrl.each(function () {
                                if ($(this).attr('value') == value) {
                                    $(this).attr("checked", value);
                                }
                            });
                            break;

                        default:
                            $ctrl.val(value);
                    }

                }
            });
        }
    </script>
    {!! Breadcrumbs::render('exam.index') !!}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <div style="width: 100%; "></div>
        <div style="float: left; padding-left: 10px">
            <button type="button" class="btn btn-primary" style="float: right" data-toggle="modal"
                    data-target="#newdialog"><i class="fa fa-plus" aria-hidden="true"></i> New Session </button>
        </div>
        &nbsp;
        <table class="table table-striped">
            <thead class="thead-inverse">
            <tr>
                <th class="headerSortable header"> @sortablelink ('name', 'Session Name')</th>
                <th class="headerSortable header"> @sortablelink ('owner_id', 'Owner')</th>
                <th class="headerSortable header"> @sortablelink ('created_timestamp', 'Created')</th>
                <th class="headerSortable header"> @sortablelink ('status', 'Status')(TODO format)</th>
                <th class="headerSortable header">Student Count</th>
                <th></th>
            </tr>
            </thead>
            @foreach ($exams as $exam)

                <tr>
                    <td>
                        <a href="{{URL::asset('/exam/'.$exam->id)}}">
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
                        {{$exam->students->count()}}
                    </td>
                    <td>
                        <a href="#" data-toggle="modal" data-id="{{$exam->id}}"
                           data-target="#deletedialog">
                            <i class="fa fa-times" style="font-size: 2em; color: red" aria-hidden="true"></i>

                        </a>
                    </td>

            @endforeach

        </table>

        {!! $exams->appends(Request::all())->render() !!}
    </fieldset>

    <div id="newdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New Session</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'role'=>'form', 'id'=>'newexamform',  'data-toggle'=>'validator'])!!}

                    @include('examinstance.form.form', ['submitButtonText'=>'Add Exam',  'formid'=>'newexamform'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>




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

