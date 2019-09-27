@extends('layouts.nomenu')

@section('content')

    <script>


        $(document).ready(function () {

            $('select').select2();

            $('.datepicker').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
            });

            if (location.hash.substr(0, 2) == "#!") {
                $("a[href='#" + location.hash.substr(2) + "']").tab("show");
            }

            $("a[data-toggle='tab']").on("shown.bs.tab", function (e) {
                var hash = $(e.target).attr("href");
                if (hash.substr(0, 1) == "#") {
                    location.replace("#!" + hash.substr(1));
                }
            });

            {{--
If the user can update patients
--}}

            // Bootstrap AJAX
            //http://webdesign.tutsplus.com/tutorials/building-a-bootstrap-contact-form-using-php-and-ajax--cms-23068

            /**
             *
             * Patient details AJAX form support
             *
             * */

            $('#editstudentdialog').on('shown.bs.modal', function () {
                $('#editstudentdialog').find("form").validator();
            });


            // Update patient dialogue
            $('#editstudentdialog').submit(function (event) {
                event.preventDefault();
                $('#editstudentdialog').modal('hide');
                var data = new FormData();
                // file
                if ($('#studentimage')[0].files.length > 0) {
                    jQuery.each($('#studentimage')[0].files, function (i, file) {
                        data.append('userfile', file);
                    });
                }
                data.append('id', '{{$student->id}}');
                data.append('studentid', $('#studentid').val());
                data.append('fname', $('#fname').val());
                data.append('lname', $('#lname').val());
                data.append('email', $('#email').val());
                data.append('_token', '{{ csrf_token() }}');
                // console.log(data);
                // cancels the form submission

                waitingDialog.show();
                submitUpdateStudentForm(data);
            });

            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                switch ($(this).data('target')) {
                    case '#viewassessmentdialog':
                        getExamDetailForSession($(this).data('id'));
                        break;
                    default:
                        break;
                }
            });

        });

        /*
         *
         * Update patient details AJAX
         *
         */
        function submitUpdateStudentForm(vars) {
            $.ajax({
                url: '{{URL::to('student/ajaxupdate')}}',
                type: 'post',
                data: vars,
                processData: false,
                contentType: false,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data.status.toString() == '0') {
                        if (typeof data.mediastatus !== 'undefined') {
                            alert('Image upload error: ' + data.mediastatus);
                        }
                        location.reload(true);
                    } else {
                        alert(data.statusText);
                    }
                }
            });
        }

        // show an individual exam detail
        function getExamDetailForSession(sessionID) {
            waitingDialog.show();
            // Get contact event
            $.ajax({
                url: '{!! URL::to('session/detail')!!}/' + sessionID,
                type: 'GET',
                dataType: 'json',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
                    //$("#editcontactdialog").find("form").values(data);
                    $("#end_timestamp").html(data.end_timestamp);
                    $("#overall_rating").html(data.overall_rating_text);
                    $("#additional_rating").html(data.additional_rating_text);
                    $("#comments").html(data.comments);
                    $("#questions_responses").html("<tr><th>Item</th><th>Result</th><th>Comments</th></tr>");

                    $.each(data.responses, function (index, element) {
                        if (element) {
                            var itemRow = "<tr><td>" + element.question_id + "</td><td>" + element.answer + "</td><td>" + element.comments + "</td></tr>";
                            $("#questions_responses").html($("#questions_responses").html() + "<tr><td>" + element.question_id + "</td><td>" + element.answer + "</td><td>" + element.comments + "</td></tr>");
                        }
                    });

                    waitingDialog.hide();
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

    <!-- Tabs -->
    {!! Breadcrumbs::render('student.show', $student) !!}
    <div style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <ul class="nav nav-tabs" id="tabslabels">
            <li class="active"><a data-toggle="tab" href='#detailstab'>Student Details</a></li>
            <li><a data-toggle="tab" href='#examtab'>Assessments</a></li>
        </ul>
        <div class="tab-content">
            <div id="detailstab" class="tab-pane active">
                <div class="col-md-6">
                    <fieldset style="width: 90%">
                        <legend>Student details...
                            <button id="edit_patient_but" class="btn btn-info btn-lg" data-toggle="modal"
                                    data-target="#editstudentdialog">Edit
                            </button>
                        </legend>
                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9; ">

                            <div class="col-md-4">
                                <strong>Student ID:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $student->studentid}}
                            </div>
                        </div>
                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9; padding-top: 20px">
                            <div class="col-md-4">
                                <strong>Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $student->fname}} {{$student->lname}}
                            </div>
                        </div>
                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9; padding-top: 20px">
                            <div class="col-md-4">
                                <strong>Email:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $student->email}}
                            </div>
                        </div>


                    </fieldset>
                </div>
                <div class="col-xs-6 col-md-4 " style="border-bottom: 1px solid #d9d9d9; padding-top: 20px">
                    <img src="{{URL::asset('/student_image/thumb/'.(isset($student->image['id'])?$student->image['id']:-1).'/400')}}">
                </div>
            </div>

            <div id="examtab" class="tab-pane">
                <fieldset style="width: 90%">
                    <legend>Examination history
                    </legend>
                    (TBA when exams are made)
                    <table class="table table-striped">
                        <thead class="thead-inverse">
                        <th>
                            Exam Date/Time
                        </th>
                        <th>
                            Exam name
                        </th>

                        <th>
                            Score
                        </th>
                        <th>
                            Overall Rating
                        </th>

                        <th>
                            Additional Rating
                        </th>
                        <th>
                            Examiner
                        </th>
                        </thead>
                        {{--@foreach($student->examinations_sessions as $examination)--}}

                        {{--<tr>--}}
                        {{--<td><a href="#" data-toggle="modal" data-target="#viewassessmentdialog"--}}
                        {{--data-id="{{$examination->id}}">{{$examination->end_timestamp}}</a></td>--}}
                        {{--<td>{{$examination->form['name']}}</td>--}}
                        {{--<td>{{$examination->responses->sum('answer')}}/{{$examination->responses->count()}}</td>--}}
                        {{--<td>{{$examination->overall_rating_text['text']}}</td>--}}
                        {{--<td>{{$examination->additional_rating_text['text']}}</td>--}}
                        {{--<td>{{$examination->created_by['name']}}</td>--}}
                        {{--</tr>--}}



                        {{--@endforeach--}}

                    </table>

                </fieldset>
            </div>


        </div>
    </div>
    {{--
    If the user can update patients

        Dialogs for various things
        --}}

    {{--  Dialog for editing the patient details  --}}
    <div id="editstudentdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Student Details</h4>
                </div>
                <div class="modal-body">
                    {!! Form::model($student, ['class'=>'form-horizontal'])!!}

                    @include('student.form.form', ['submitButtonText'=>'Update Student'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>



    {{--<div id="viewassessmentdialog" class="modal fade">--}}
    {{--<div class="modal-dialog">--}}
    {{--<div class="modal-content">--}}
    {{--<div class="modal-header">--}}

    {{--<h4 class="modal-title">Assessment detail for <span id="assessmentdetail"></span></h4>--}}
    {{--</div>--}}
    {{--<div class="modal-body">--}}
    {{--@include('student.examdetail')--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}






@stop

