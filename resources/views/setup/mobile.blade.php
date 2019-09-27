@extends('layouts.nomenu')

@section('title')
   Mobile client setup
@endsection

@section('content')
    {{--Some extra libraries for inline editing--}}
    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-editable.css')}}">
    <script src="{{ URL::asset('resources/assets/js/bootstrap-editable.min.js') }}"></script>
    <style>

    </style>
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

            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                currenteditingid = $(this).data('id');
                switch ($(this).data('target')) {

                    default:
                        currenteditingid = -1;
                        currentdeletingid = -1;
                        break;
                }

            });
        }


        function deleteItem(vars) {
            $.ajax({
                url: '{{URL::to('examitem/ajaxdestroy')}}',
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
                    if (data.status.toString() == "1") {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the update');
                    }
                }
            });
        }


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
    {!! Breadcrumbs::render('setup.mobile') !!}
    <div style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <ul class="nav nav-tabs" id="tabslabels">
            <li class="active"><a data-toggle="tab" href='#rolestab'>System roles</a></li>
            <li><a data-toggle="tab" href='#mediatab'>Media</a></li>
        </ul>
        <div class="tab-content">
            <div id="rolestab" class="tab-pane active">
                <fieldset style="width: 90%">
                    <legend>System Roles

                    </legend>
                   TODO make system roles settings available here
                </fieldset>
            </div>

            <div id="mediatab" class="tab-pane ">
                <fieldset style="width: 90%">
                    <legend>Associated Media
                        <button id="edit_patient_but" class="btn btn-info btn-lg" data-toggle="modal"
                                data-target="#editstudentdialog">Add
                        </button>
                    </legend>
                    <div style="display: table" class="col-xs-12 col-md-8">

                    </div>
                    <div class="col-xs-6 col-md-4">

                    </div>
                </fieldset>
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


