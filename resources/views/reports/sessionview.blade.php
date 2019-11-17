@extends('layouts.nomenu')

@section('title')
    {{$submission->name}} results
@endsection

@section('content')
    {{--Some extra libraries for inline editing--}}
    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-editable.css')}}">
    <script src="{{ URL::asset('resources/assets/js/bootstrap-editable.min.js') }}"></script>
    <style>


        #questionstbl tr.placeholder:before {
            position: absolute;
            color: red;
            content: '\279e';
            /** Define arrowhead **/
        }
    </style>
    <script>

        $.fn.editable.defaults.mode = 'inline';

        var currenteditingid = 0;

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

            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                currenteditingid = $(this).data('id');
                switch ($(this).data('target')) {
                    case '#edititemdialog':
                        currenteditingid = $(this).data('id');
                        console.log(currenteditingid)
                        getItemDetails(currenteditingid);
                        break;
                    case '#changelogdialog':
                        getItemChangelog($(this).data('id'));
                        break;
                    default:
                        currenteditingid = -1;
                        currentdeletingid = -1;
                        break;
                }

            });

            $('#edititemdialog').submit(function (event) {
                // cancels the form submission
                event.preventDefault();
                $(this).modal('hide');
                //var vars = $("#edititemform").find("form").serializeArray();
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: currenteditingid});
                waitingDialog.show();
                console.log(vars)
                submitUpdateForm(vars, 'submissionitem');
            });


            $('.modalform').on('shown.bs.modal', function () {
                $(this).find("form").validator();
            });


        });

        function getItemDetails(id) {
            // get the item details and answer
            $.ajax({
                url: '{!! URL::to('')!!}/submissionitem/' + id,
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
                    console.log($(data)[0]);
                    $("#update_item_heading").text('"' + ($(data)[0].item.label) + '"');
                    // clear up select
                    $("#selected_exam_instance_items_items_id").find('option').remove();
                    // populate update form

                    // populate with options
                    $.each($(data)[0].item.items, (function () {
                        console.log($(this)[0]);
                        $("#selected_exam_instance_items_items_id").append("<option value='" + $(this)[0].id + "' >" + $(this)[0].label + "</option>")
                    }));
                    //  $("#selected_exam_instance_items_items_id").val($(data)[0].answer.selected_exam_instance_items_items_id).trigger('change')
                    populate(edititemform, $(data)[0].answer)
                    $('#edititemform').validator()
                    waitingDialog.hide();
                }
            });
        }


        function submitUpdateForm(vars, route) {
            // console.log(route);
            $.ajax({
                url: '{!! URL::to('')!!}/' + route + '/update',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    //   waitingDialog.hide();
                    if (data.status.toString() == "0") {
                        location.reload();
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the update');
                    }
                }
            });
        }

        function getItemChangelog(id) {
            // get the item  changelog and display it
            $.ajax({
                url: '{!! URL::to('')!!}/submissionitem/changelog/' + id,
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {

                    $("#changelogtable tbody").find('tr').remove();
                    $.each(data, function () {

                        $("#changelogtable tbody").append('<tr>' +
                            '<td>' + $(this)[0].reason + '</td>' +
                            '<td>' + $(this)[0].old_label + '</td>' +
                            '<td>' + $(this)[0].new_label + '</td>' +
                            '<td>' + $(this)[0].old_comments + '</td>' +
                            '<td>' + $(this)[0].new_comments + '</td>' +
                            '<td>' + $(this)[0].updated_by + '</td>' +
                            '<td>' + $(this)[0].updated_at + '</td>' +
                            '</tr> ');


                    });


                    waitingDialog.hide();
                }
            });
        }

        function sendEmail() {
            // get the item  changelog and display it
            $.ajax({
                url: '{!! URL::to('')!!}/report/session/{{$submission->id}}/email/',
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
                    console.log(data);
                    alert(data);


                    waitingDialog.hide();
                }
            });
        }

        function placeholder() {
            alert('Do something here')
        }

        // populates a form with data returned from Laravel
        function populate(frm, data) {
            $.each(data, function (key, value) {
                var $ctrl = $('[name=' + key + ']', frm);
                if ($ctrl.is("select")) {
                    $ctrl.val(value).trigger('change');
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
    {!! Breadcrumbs::render('report.session', $submission) !!}
    {{--{!! Breadcrumbs::render('report.session', 3) !!}--}}
    <div style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <fieldset style="width: 90%">
            <fieldset style="width: 100%">
                <div class="col-md-12" style="padding-left: 0px; margin-left: 0px">
                    <div class="col-md-4">
                        <legend style="">Report for: {{$submission->student->fname}} {{$submission->student->lname}}
                            , {{$submission->student->studentid}}<br/>
                            Score:{{$score}}/{{$maxscore}}


                        </legend>
                    </div>
                    <div class="col-md-8">
                        <button class="btn bg-info" onclick="sendEmail()">Send feedback</button>
                    </div>
                </div>


                <table class="table table-striped table-condensed" id="updateitemitemstbl">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Result</th>
                        <th>Value</th>
                        <th>Comment</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($definition->exam_instance_items as $submission_instance_item)

                        @if(($submission_instance_item->heading)==1)
                            <tr style="background-color: #7ab800">
                                <td colspan="4">

                                    <h5> {{$submission_instance_item->label}}</h5>

                                </td>

                            </tr>


                        @else
                            <tr>
                                <td>

                                    <a href="#" data-toggle="modal"
                                       data-target="#edititemdialog"
                                       data-id="{{$submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->id}}">

                                        {{$submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->item->label}}

                                        @if($submission_instance_item->exclude_from_total=='1')
                                            (Formative)
                                        @endif
                                    </a>
                                    {{--Check to see if there's changes--}}
                                    @if($submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->changelog->count()>0)
                                        <a href="#" style="color: coral" data-toggle="modal"
                                           data-target="#changelogdialog"
                                           data-id="{{$submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->id}}"><i
                                                    class="fa fa-exclamation-circle"></i>(Updated)</a>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->selecteditem)
                                        {{$submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->selecteditem->label}}
                                    @else
                                        (not shown)
                                    @endif
                                    {{--{{$item->selecteditem->label}}--}}
                                </td>
                                <td>
                                    @if($submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->selecteditem)
                                        {{$submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->selecteditem->value}}
                                    @else
                                        (not shown)
                                    @endif
                                </td>
                                <td>
                                    {{$submission->student_exam_submission_items->where('exam_instance_items_id',$submission_instance_item->id)->first()->comments}}
                                    {{--{{$item->comments}}--}}
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    {{--@foreach ($submission->student_exam_submission_items as $item)--}}

                    {{--<tr>--}}
                    {{--<td>--}}
                    {{--<a href="#" onclick="placeholder()">--}}
                    {{--{{$item->item->label}}--}}
                    {{--</a>--}}
                    {{--</td>--}}
                    {{--<td>--}}

                    {{--{{$item->selecteditem->label}}--}}
                    {{--</td>--}}
                    {{--<td>--}}

                    {{--{{$item->selecteditem->value}}--}}
                    {{--</td>--}}
                    {{--<td>--}}
                    {{--{{$item->comments}}--}}
                    {{--</td>--}}


                    {{--@endforeach--}}
                    <tr style="background-color: #7ab800">
                        <td colspan="4">
                            Overall comments
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            {{$submission->comments}}
                        </td>
                    </tr>
                    </tbody>
                </table>

            </fieldset>

        </fieldset>
    </div>

    <div id="edititemdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update <span id="update_item_heading"></span></h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'id'=>'edititemform', 'data-toggle'=>"validator", 'role'=>"form"])!!}


                    @include('reports.form.form')


                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="changelogdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Changelog <span id="update_item_heading"></span></h4>
                </div>
                <div class="modal-body">
                    <table id="changelogtable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Reason</th>
                            <th>Old answer</th>
                            <th>New answer</th>
                            <th>Old comment</th>
                            <th>New comment</th>
                            <th>Updated by</th>
                            <th>Updated at</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{--Placeholder--}}
    <div id="placeholderdialog" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Placeholder</h4>
                </div>
                <div class="modal-body">
                    Placeholder dialog
                </div>
            </div>
        </div>
    </div>






@stop


