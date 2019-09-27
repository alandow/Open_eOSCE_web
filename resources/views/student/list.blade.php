@extends('layouts.nomenu')

@section('content')
    <style>

    </style>
    <script>

        var currentid = -1;

        $(document).ready(function () {

            $('select').select2();

            {{--@can('is_admin')--}}

             $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                if ((typeof $(this).data('id')) !== 'undefined') {
                    currentid = $(this).data('id');

                }
            });
            @can('update_student')
            // New student event dialogue
            $('#newstudentdialog').submit(function (event) {
                if (!event.isDefaultPrevented()) {
                    $('#newstudentdialog').modal('hide');
                    var data = new FormData();
                    // file
                    if ($('#studentimage')[0].files.length > 0) {
                        jQuery.each($('#studentimage')[0].files, function (i, file) {
                            data.append('userfile', file);
                        });
                    }
                    data.append('studentid', $('#studentid').val());
                    data.append('fname', $('#fname').val());
                    data.append('lname', $('#lname').val());
                    data.append('email', $('#email').val());
                    data.append('_token', '{{ csrf_token() }}');
//                    var vars = $("#newstudentform").serializeArray();
                    // cancels the form submission
                    event.preventDefault();
                    waitingDialog.show();
                    submitNewStudentForm(data);
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
            @endcan
        });

        @can('update_student')
        /*
         *
         * New student AJAX
         *
         */
        function submitNewStudentForm(vars) {
            $.ajax({
                url: '{{URL::to('student/ajaxstore')}}',
                type: 'post',
                data: vars,
                processData: false,
                contentType: false,
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


        function deleteStudent(vars) {
            $.ajax({
                url: '{{URL::to('student/ajaxdestroy')}}',
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

        @endcan

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
    {!! Breadcrumbs::render('student.index') !!}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <div style="width: 100%; ">
            @can('update_student')
                <button type="button" class="btn btn-primary" style="float: right" data-toggle="modal"
                        data-target="#newstudentdialog">New Student <i class="fa fa-plus" aria-hidden="true"></i>

                </button>
            @endcan
        </div>
        <div style="float: left; padding-left: 10px">{!! Form::open( ['class'=>'form-inline', 'role'=>'form', 'url'=>Request::path(), 'method' => 'get'])!!}
            <div class="form-group row"><input type="text" class="form-control" name="search"
                                               value="{{isset(Request::all()['search'])?Request::all()['search']:''}}">
                <button type="submit" class="btn btn-primary" style="vertical-align: bottom">Search <i
                            class="fa fa-search" aria-hidden="true"></i></button>
            </div>{!! Form::close()!!}</div>
        &nbsp;
        <table class="table table-striped">
            <thead class="thead-inverse">
            <tr>
                <th class="headerSortable header"> @sortablelink ('studentid', 'Student ID')</th>
                <th class="headerSortable header"> @sortablelink ('fname', 'First Name')</th>
                <th class="headerSortable header"> @sortablelink ('lname', 'Last Name')</th>
                <th></th>
            </tr>
            </thead>

            @foreach ($students as $student)
                <tr>
                    <td>
                        <a href="{{URL::asset('/student/show/'.$student->id)}}">
                            {{$student->studentid}}
                        </a>
                    </td>
                    <td>
                        {{$student->fname}}
                    </td>
                    <td>
                        {{$student->lname}}
                    </td>
                    <td>
                        <img src="{{URL::asset('/student_image/thumb/'.(isset($student->image['id'])?$student->image['id']:-1).'/100')}}">
                    </td>

                    <td>
                        <a href="#" data-toggle="modal" data-id="{{$student->id}}"
                           data-target="#deletedialog">
                            <i class="fa fa-times" style="color: red; font-size: 2em" aria-hidden="true"></i>

                        </a>
                    </td>

            @endforeach

        </table>

        {!! $students->appends(Request::all())->render() !!}
    </fieldset>
    @can('update_student')
        <div id="newstudentdialog" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">New Student</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open( ['class'=>'form-horizontal', 'role'=>'form', 'id'=>'newstudentform', 'data-toggle'=>'validator'])!!}

                        @include('student.form.form', ['submitButtonText'=>'Add Student',  'formid'=>'newstudentform'])

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
                        <h4 class="modal-title">Really delete student?</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open()!!}
                        @include('form_common.deletedialog')
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endcan

@stop

