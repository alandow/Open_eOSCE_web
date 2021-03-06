@extends('layouts.nomenu')

@section('title')
    {{$exam->name}} results
@endsection

@section('content')
    {{--Some extra libraries for inline editing--}}
    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-editable.css')}}">

    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/Chart.css')}}">


    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-datetimepicker.min.css')}}">
    <script src="{{ URL::asset('resources/assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('resources/assets/js/bootstrap-editable.min.js') }}"></script>
    <script src="{{ URL::asset('resources/assets/js/Chart.bundle.js') }}"></script>
    <style>

        canvas {
            max-width: 200px;
        }


    </style>


    <!-- Tabs -->
    {!! Breadcrumbs::render('report.show', $exam) !!}
    <fieldset style="width: 100%">
        <legend>Report for {{$exam->name}}&nbsp;
            <a href="{{URL::asset('/report/'.$exam->id.'/excelsummary')}}" type="button" class="btn btn-primary">Export
                to Excel&nbsp;<i class="fa fa-file-excel-o" aria-hidden="true"></i></a>
        </legend>
        <div style="padding-left: 15px; padding-right: 15px; margin-top: 0">
            <ul class="nav nav-tabs" id="tabslabels">
                <li class="active"><a data-toggle="tab" href='#resultstab'><i class="fa fa-table" aria-hidden="true"></i> Results</a></li>
                <li><a data-toggle="tab" href='#statstab'><i class="fa fa-bar-chart" aria-hidden="true"></i> Statistics/Analysis</a></li>
                <li><a data-toggle="tab" href='#feedbacktab'><i class="fa fa-comment" aria-hidden="true"></i> Feedback</a></li>
            </ul>
            <div class="tab-content">
                <div id="resultstab" class="tab-pane active">

                    <fieldset style="width: 90%">
                        <legend>Results
                        </legend>
                        <table class="table table-striped">
                            <thead class="thead-inverse">
                            <tr>
                                <th class="headerSortable header"> @sortablelink ('studentname', 'Student name')</th>
                                <th class="headerSortable header"> @sortablelink ('owner_id', 'Student ID')</th>
                                <th class="headerSortable header"> @sortablelink ('total', 'Score')</th>
                                <th class="headerSortable header"> @sortablelink ('groupcode', 'Group')</th>
                                <th class="headerSortable header"> @sortablelink ('created_at', 'Submitted at')</th>
                                <th class="headerSortable header"> @sortablelink ('created_by', 'Examiner')</th>
                            </tr>
                            </thead>
                            @foreach ($results as $result)

                                <tr>
                                    <td>
                                        <a href="{{URL::asset('/report/session/'.$result->id)}}">
                                            {{$result->studentname}}
                                        </a>
                                    </td>
                                    <td>
                                        {{$result->student->studentid}}
                                    </td>
                                    <td>
                                        {{$result->total}}/{{$maxscore}}
                                    </td>
                                    <td>
                                        {{$result->groupcode}}
                                    </td>
                                    <td>
                                        {{date_format($result->created_at, 'd/m/Y H:i:s A')}}
                                    </td>

                                    <td>
                                        {{$result->created_by}}
                                    </td>
                                </tr>

                            @endforeach
                        </table>

                    </fieldset>
                </div>
                <div id="statstab" class="tab-pane">

                    <fieldset style="width: 90%">
                        <legend>Overall
                        </legend>
                        <div class="col-md-12">
                            <div class="col-md-3">
                                <canvas id="myChart" width="20" height="20"></canvas>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-striped">
                                    <tr>
                                        <td>Number of students (<i>n</i>)</td>
                                        <td>{{$stats['overall']['n']}}</td>
                                    </tr>
                                    <tr>
                                        <td>Average</td>
                                        <td>{{$stats['overall']['mean']}}</td>
                                    </tr>
                                    <tr>
                                        <td>Median</td>
                                        <td>{{$stats['overall']['median']}}</td>
                                    </tr>
                                    <tr>
                                        <td>Standard Deviation</td>
                                        <td>{{$stats['overall']['stdev']}}</td>
                                    </tr>
                                    <tr>
                                        <td>Minimum</td>
                                        <td>{{$stats['overall']['min']}}</td>
                                    </tr>
                                    <tr>
                                        <td>Maximum</td>
                                        <td>{{$stats['overall']['max']}}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset style="width: 90%">
                        <legend>Results by assessor
                        </legend>
                        @foreach($exam->examiners as $examiner)
                            <div class="col-md-12">
                                {{$examiner->name}}
                            </div>
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <canvas id="examiner_chart_{{$examiner->id}}" width="20" height="20"></canvas>
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-striped">
                                        <tr>
                                            <td>Number of students (<i>n</i>)</td>
                                            <td>{{$stats['examiners'][$examiner->id]['n']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Average</td>
                                            <td>{{$stats['examiners'][$examiner->id]['mean']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Median</td>
                                            <td>{{$stats['examiners'][$examiner->id]['median']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Standard Deviation</td>
                                            <td>{{$stats['examiners'][$examiner->id]['stdev']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Minimum</td>
                                            <td>{{$stats['examiners'][$examiner->id]['min']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Maximum</td>
                                            <td>{{$stats['examiners'][$examiner->id]['max']}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </fieldset>
                    <fieldset style="width: 90%">
                        <legend>Results by group
                        </legend>
                        @foreach($exam->groupIDs as $group_id)
                            <div class="col-md-12">
                                {{\App\Group::find($group_id)->text}} ({{\App\Group::find($group_id)->code}})
                            </div>
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <canvas id="group_chart_{{$group_id}}" width="20" height="20"></canvas>
                                </div>
                                <div class="col-md-9">
                                    <table class="table table-striped">
                                        <tr>
                                            <td>Number of students (<i>n</i>)</td>
                                            <td>{{$stats['groups'][$group_id]['n']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Average</td>
                                            <td>{{$stats['groups'][$group_id]['mean']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Median</td>
                                            <td>{{$stats['groups'][$group_id]['median']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Standard Deviation</td>
                                            <td>{{$stats['groups'][$group_id]['stdev']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Minimum</td>
                                            <td>{{$stats['groups'][$group_id]['min']}}</td>
                                        </tr>
                                        <tr>
                                            <td>Maximum</td>
                                            <td>{{$stats['groups'][$group_id]['max']}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </fieldset>
                </div>
                <div id="feedbacktab" class="tab-pane">
                    <fieldset style="width: 90%">
                        <legend>Feedback <span id="pending_email_count"></span>
                        </legend>
                        <div class="col-ms-12" style="padding-bottom: 5px">
                            <div class="btn-group">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#setupemaildialog"
                                        style="margin-right: 5px"><i class="fa fa-cogs" aria-hidden="true"></i> Set up email
                                </button>
                                <button data-toggle="modal" data-target="#testemaildialog" class="btn btn-warning"
                                        style="margin-right: 5px"><i class="fa fa-stethoscope" aria-hidden="true"></i> Test email
                                </button>
                                <button class="btn btn-danger" data-toggle="modal" data-target="#confirmemaildialog">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i> Send feedback email
                                </button>
                            </div>
                        </div>

                        <div class="col-ms-12">

                            <table class="table table-striped table-condensed">
                                <tr>
                                    <th>Email template</th>
                                    <td><a href="#" style="color: coral; text-underline: coral" data-toggle="modal"
                                           data-target="#showfeedbackpreviewdialog">{{$exam->feedback_template->label}} (click to preview)</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Items</th>
                                    <td>
                                        @foreach($exam->exam_instance_items as $exam_instance_item)
                                            <div class="col-sm-12">

                                                @if(in_array($exam_instance_item->id, json_decode($exam->email_parameters)->exclude_items))

                                                    <i class="fa fa-times fa-2x" style="color: red"
                                                       aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-check fa-2x" style="color: green"
                                                       aria-hidden="true"></i>
                                                @endif
                                                @if($exam_instance_item->heading=='1')
                                                    <strong>{{$exam_instance_item->label}} (Heading)</strong>
                                                @else
                                                    {{$exam_instance_item->label}}
                                                @endif
                                                @if($exam_instance_item->exclude_from_total=='1')
                                                    (formative)
                                                @endif

                                            </div>

                                        @endforeach

                                    </td>
                                </tr>
                                <tr>
                                    <th>Items comments</th>
                                    <td>
                                        @if(json_decode($exam->email_parameters)->exclude_items_comments=='1')
                                            <i class="fa fa-times fa-2x" style="color: red"
                                               aria-hidden="true"></i>
                                        @else
                                            <i class="fa fa-check fa-2x" style="color: green"
                                               aria-hidden="true"></i>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Overall comments</th>
                                    <td>
                                        @if(json_decode($exam->email_parameters)->exclude_overall_comments=='1')
                                            <i class="fa fa-times fa-2x" style="color: red"
                                               aria-hidden="true"></i>
                                        @else
                                            <i class="fa fa-check fa-2x" style="color: green"
                                               aria-hidden="true"></i>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                    </fieldset>
                </div>


            </div>
        </div>
    </fieldset>



    <div id="setupemaildialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Setup email</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('reports.form.setupemailform')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


    <div id="deleteitemdialog" class="modal fade" role="dialog">
        <div class="modal-dialog" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Really delete item?</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('form_common.deletedialog')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="testemaildialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Send test email</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    <div class="form-group row">
                        <div class="col-sm-12">
                            {!! Form::label('send_to_id', 'Send test email to', ['class'=>'control-label   text-left']) !!}
                        </div>
                        <div class="col-sm-12">
                            {!! Form::select('send_to_id', $users->pluck('name', 'id'), null, ['style'=>"width: 300px", 'class'=>'form-control', 'required']) !!}
                        </div>

                    </div>

                    <div class="form-group row">
                        {!! Form::Button('<i class="fa fa-stethoscope" aria-hidden="true"></i> Send test email', ['type' => 'submit', 'class'=>'btn btn-warning form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="confirmemaildialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Really send feedback to {{$results->count()}} students?</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" value="1"
                                       onchange="console.log(event);if(event.target.checked){$('#delayuntil').show();}else{$('#delayuntil').hide()}"
                                       name="delay"><label>
                                    Delay email
                                </label>

                            </div>
                        </div>
                    </div>
                    <div class="form-group row" id="delayuntil" style="display: none">
                        <div class="col-sm-12">
                            <label>
                                Delay until:
                            </label>
                        </div>
                        <div class="col-sm-12">
                            <div class="input-append date form_datetime " >
                                <input  type="text" name="delaydate"  id="datetimepicker">
                                <span class="add-on"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                            </div>
                        </div>

                    </div>

                    <div class="form-group row">
                        {!! Form::button('<i class="fa fa-paper-plane" aria-hidden="true"></i> Send feedback', ['type' => 'submit', 'class'=>'btn btn-danger form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="showfeedbackpreviewdialog" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Feedback template: {{$exam->feedback_template->label}}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-12"><label class="'control-label text-left">Subject:<br/></label></div>
                        <div class="col-sm-12">
                            {{str_replace(['{name}', '{exam}'], [$results[0]->studentname, $exam->name], $exam->feedback_template->subject)}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12"><label class="'control-label text-left">Body:<br/></label></div>
                        <div class="col-sm-12">
                            {!!str_replace(['{name}', '{exam}', '{results}'], [$results[0]->studentname, $exam->name, $sample],$exam->feedback_template->text)!!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>

        //  var chartOptions =
        $(document).ready(function () {
            checkPendingEmails();
            //select2
            $('select').select2();

            // the datetimepicker
            $(".form_datetime").datetimepicker({
                format: "dd/mm/yyyy HH:ii P",
                showMeridian: true,
                autoclose: true,
                startDate: new Date(),
                initialDate: new Date(),
                todayBtn: true
            });

            $("#datetimepicker").val(moment().format("DD/MM/YYYY hh:mm A"));

            // Set up tab persistence across reloads
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
                switch ($(this).data('target')) {
                    case '#setupemaildialog':
                        //getEmailSetupDetails();
                        break;
                    default:
                        currenteditingid = -1;
                        currentdeletingid = -1;
                        break;
                }

            });

            $('#setupemaildialog').submit(function (event) {
                console.log('sending setup email params')
                // cancels the form submission
                event.preventDefault();
                $(this).modal('hide');
                //var vars = $("#edititemform").find("form").serializeArray();
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: '{{$exam->id}}'});
                waitingDialog.show();
                console.log(vars)
                submitUpdateEmailSetupForm(vars);
            });

            $('#testemaildialog').submit(function (event) {
                console.log('sending test email')
                // cancels the form submission
                event.preventDefault();
                $(this).modal('hide');
                //var vars = $("#edititemform").find("form").serializeArray();
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: '{{$exam->id}}'});
                waitingDialog.show();
                console.log(vars)
                sendTestEmail(vars);
            });

            $('#confirmemaildialog').submit(function (event) {
                console.log('sending actual feedback params')
                // cancels the form submission
                event.preventDefault();
                $(this).modal('hide');
                //var vars = $("#edititemform").find("form").serializeArray();
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: '{{$exam->id}}'});
                waitingDialog.show();
                console.log(vars)
                sendEmail(vars);
            });

            // little delay to show charts. Doesn't work without it.
            setTimeout(function () {
                showChart('myChart', [@for ($i = 0; $i<$maxscore+1; $i++)
                        @if (in_array($i,array_keys($stats['overall']['hist_array'])))
                        {{$stats['overall']['hist_array'][$i]}}
                        @else
                    0
                    @endif
                    @if ($i<($maxscore)) , @endif
                    @endfor]);
                // assessor specific stats
                @foreach($exam->examiners as $examiner)
                showChart('examiner_chart_{{$examiner->id}}', [@for ($i = 0; $i<$maxscore+1; $i++)
                        @if (in_array($i,array_keys($stats['overall']['hist_array'])))
                        {{$stats['examiners'][$examiner->id]['hist_array'][$i]}}
                        @else
                    0
                    @endif
                    @if ($i<($maxscore)) , @endif
                    @endfor]);
                @endforeach
                // group specific stats
                @foreach($exam->groupIDs as  $group_id)
                showChart('group_chart_{{$group_id}}', [@for ($i = 0; $i<$maxscore+1; $i++)
                        @if (in_array($i,array_keys($stats['overall']['hist_array'])))
                        {{$stats['groups'][$group_id]['hist_array'][$i]}}
                        @else
                    0
                    @endif
                    @if ($i<($maxscore)) , @endif
                    @endfor]);
                @endforeach
            }, 500);

            // watch for jobs
                var tid = setInterval(checkPendingEmails, 10000);
        });

        function submitUpdateEmailSetupForm(vars) {
            $.ajax({
                url: '{!! URL::to('')!!}/report/{{$exam->id}}/setfeedbacksetup',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data.status.toString() == "0") {
                        location.reload();
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the update');
                    }
                }
            });
        }

        function getEmailSetupDetails(id) {
            // get the email setup parameters and display them
            $.ajax({
                url: '{!! URL::to('')!!}/report/{{$exam->id}}/getfeedbacksetup',
                type: 'post',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {

                    waitingDialog.hide();
                }
            });
        }

        function sendTestEmail(vars) {
            $.ajax({
                url: '{!! URL::to('')!!}/report/{{$exam->id}}/sendtestemail',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data.status.toString() == "0") {
                      //  location.reload();
                        checkPendingEmails()
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the operation');
                    }
                }
            });
        }

        function sendEmail(vars) {
            $.ajax({
                url: '{!! URL::to('')!!}/report/{{$exam->id}}/sendallemails',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data.status.toString() == "0") {
                        checkPendingEmails();
                      //  location.reload();
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the operation');
                    }
                }
            });
        }



        function checkPendingEmails() {
            $.ajax({
                url: '{!! URL::to('')!!}/report/{{$exam->id}}/getpendingemailcount',
                type: 'get',
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    //    waitingDialog.hide();
                    if (data.status.toString() == "0") {
                        if (data.count > 0) {
                            $("#pending_email_count").html(" (" + data.count + ' emails pending...)')
                        }
                        else {
                            $("#pending_email_count").html('');
                        }

                    } else {
                        //  waitingDialog.hide();
                        alert('something went wrong with the operation');
                    }
                }
            });
        }

        var globaloptions = {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Overall'
            },
            scales: {
                yAxes:
                    [{
                        stepSize: 1,
                        scaleLabel: {
                            display: true,
                            labelString: 'Count'
                        }
                    }],
                xAxes:
                    [{
                        stepSize: 1,
                        scaleLabel: {
                            display: true,
                            labelString: 'Score'
                        }
                    }]
            }
        }

        function showChart(target, values) {
            var ctx = document.getElementById(target).getContext('2d');
            var dataValues = values;
            var dataLabels = [0
                @for ($i = 1; $i<$maxscore+1; $i++)
                ,{{$i}}
                @endfor]

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dataLabels,
                    datasets: [{
                        label: 'Count',
                        data: dataValues,
                        backgroundColor: 'rgba(255, 99, 132, 1)',
                    }]
                },
                options: globaloptions
            });
        }
    </script>





@stop



