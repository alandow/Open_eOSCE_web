@extends('layouts.nomenu')

@section('title')
    {{$exam->name}} results
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

                    default:
                        currenteditingid = -1;
                        currentdeletingid = -1;
                        break;
                }

            });


            $('.modalform').on('shown.bs.modal', function () {
                $(this).find("form").validator();
            });


        });


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
    {!! Breadcrumbs::render('report.show', $exam) !!}
    <div style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <ul class="nav nav-tabs" id="tabslabels">
            <li class="active"><a data-toggle="tab" href='#resultstab'>Results</a></li>
            <li><a data-toggle="tab" href='#statstab'>Statistics/Analysis</a></li>

        </ul>
        <div class="tab-content">
            <div id="resultstab" class="tab-pane active">
                <fieldset style="width: 90%">
                    <legend>Results for {{$exam->name}}

                    </legend>

                    <table class="table table-striped">
                        <thead class="thead-inverse">
                        <tr>
                            <th class="headerSortable header"> @sortablelink ('name', 'Student name')</th>
                            <th class="headerSortable header"> @sortablelink ('owner_id', 'Student ID')</th>
                            <th class="headerSortable header"> @sortablelink ('score', 'Score')</th>
                            <th class="headerSortable header"> @sortablelink ('group', 'Group')</th>
                            <th class="headerSortable header"> @sortablelink ('start_datetime', 'Submitted at')</th>
                            <th class="headerSortable header"> @sortablelink ('status', 'Examiner')</th>
                        </tr>
                        </thead>
                        @foreach ($exam->sortable_student_exam_submissions as $results)

                            <tr>
                                <td>
                                    <a href="{{URL::asset('/report/session/'.$results->id)}}">
                                        {{$results->studentname}}
                                    </a>
                                </td>
                                <td>
                                    {{$results->student->studentid}}
                                </td>
                                <td>
                                    {{$results->total}}/{{$maxscore}}
                                </td>
                                <td>
                                    {{$results->groupcode}}
                                </td>
                                <td>
                                    {{date_format($results->created_at, 'd/m/Y H:i:s A')}}
                                </td>

                                <td>
                                    {{$results->created_by}}
                                </td>


                        @endforeach
                    </table>

                </fieldset>
            </div>
            <div id="statstab" class="tab-pane">
                <fieldset style="width: 90%">
                    <legend>Statistics
                        <span style="font-size: 0.5em;">


                            </span>
                    </legend>

                </fieldset>
            </div>


        </div>
    </div>

    <div id="showresultsdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Item</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'id'=>'edititemform'])!!}



                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="editresultsdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Item</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'id'=>'edititemform'])!!}



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


