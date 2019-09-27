@extends('layouts.mainapp')

@section('title')
    Exam preparation
@endsection

@section('menu')

    <li>

        {{--@can('update_review_instance')--}}
            <button type="button" class="btn btn-primary" style="width: 220px" data-toggle="modal"
                    data-target="#newdialog">
                New Template <i
                        class="fa fa-plus"></i>
            </button>
        {{--@endcan--}}
        <p/>
    </li>

    <li>
        <a href="{{ url('/examtemplates') }}"><i class="fa fa-fw fa-cog"></i>Templates</a>

    </li>
    <li>
        <a href="{{ url('/examemails') }}"><i class="fa fa-fw fa-cog"></i>Email templates</a>

    </li>
@endsection


@section('content')
    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-editable.css')}}">
    <script src="{{ URL::asset('resources/assets/js/bootstrap-editable.min.js') }}"></script>
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
                deleteStudent(vars);
            });

        });


        /*
         *
         * New user AJAX
         *
         */
        function submitNewForm(vars) {
            $.ajax({
                url: '{{URL::to('examtemplates/ajaxstore')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    console.log(data.status)
                    if (data.status > 0) {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }


        function deleteExam(vars) {
            $.ajax({
                url: '{{URL::to('examtemplates/ajaxdestroy')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data == "1") {
                        location.reload(true);
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
    {!! Breadcrumbs::render('examtemplates.index') !!}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <div style="width: 100%; "></div>
        <div style="float: left; padding-left: 10px">
            <button type="button" class="btn btn-primary" style="float: right" data-toggle="modal"
                    data-target="#newdialog">New Template <i class="fa fa-plus" aria-hidden="true"></i></button>
        </div>
        &nbsp;
        <table class="table table-striped">
            <thead class="thead-inverse">
            <tr>
                <th class="headerSortable header"> @sortablelink ('name', 'Template Name')</th>
                <th class="headerSortable header">Description</th>
                <th></th>
            </tr>
            </thead>
            @foreach ($exams as $exam)

                <tr>
                    <td>
                        <a href="{{URL::asset('/examtemplates/'.$exam->id)}}">
                            {{$exam->name}}
                        </a>
                    </td>
                    <td>

                            {{$exam->description}}

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
                    <h4 class="modal-title">New Exam Template</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'role'=>'form', 'id'=>'newexamform',  'data-toggle'=>'validator'])!!}

                    @include('examinstance.templates.form.form', ['submitButtonText'=>'Add template',  'formid'=>'newexamform'])

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

