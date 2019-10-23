@extends('layouts.nomenu')

@section('content')
    <link rel="stylesheet" href="{{ URL::asset('resources/assets/css/awesome-bootstrap-checkbox.css') }}">
    <script src="{{ URL::asset('resources/assets/js/tinymce/tinymce.min.js') }}"></script>
    <script>

        //tinymce Bootstrap fix
        // https://www.tinymce.com/docs/integrations/bootstrap/
        $(document).on('focusin', function (e) {
            if ($(e.target).closest(".mce-window").length) {
                e.stopImmediatePropagation();
            }
        });

        var currentid = -1;

        $(document).ready(function () {
            {{--@can('update-review-instance')--}}
                        $('select').select2();

            // init timyMCE
            tinymce.init({
                selector: '.tinymce',
                height: 500,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table contextmenu paste code'
                ],
                toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                content_css: '//www.tinymce.com/css/codepen.min.css'
            });

            // make all date class elements into datepicker
            $('.datepicker').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
            });


            $('.modalform').on('shown.bs.modal', function () {
                $(this).find("form").validator();
            });

            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                currenteditingid = $(this).data('id');
                switch ($(this).data('target')) {
                    case '#editdialog':
                        currenteditingid = $(this).data('id');
                        console.log(currenteditingid)
                        getDetails(currenteditingid, 'reportemails', 'editdialog');
                        break;
                    case '#deletedialog':
                        currentid = $(this).data('id');
                        break;
                    case '#copydialog':
                        currentid = $(this).data('id');
                        break;
                    default:
                        currentid = -1;
                        break;
                }

            });

            $('.newdialog').submit(function (event) {
                event.preventDefault();
                $(this).modal('hide');
                var vars = $(this).find("form").serializeArray();
                console.log(vars);
                waitingDialog.show();
                submitNewForm(vars, $(this).data('route'));
            });

            $('#editdialog').submit(function (event) {
                // cancels the form submission
                event.preventDefault();
                $(this).modal('hide');
                //  var vars = $("#newmediadialog").find("form").serializeArray();
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: currenteditingid});
                waitingDialog.show();
                submitUpdateForm(vars, 'reportemails');
            });

            $("#copydialog").submit(function (event) {
                $(this).modal('hide');
                // cancels the form submission
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: currentid});
                event.preventDefault();
                waitingDialog.show();
                submitCopyForm(vars);
            });

            // all deletes are the same
            $("#deletedialog").submit(function (event) {
                $(this).modal('hide');
                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                deleteItem(currentid, 'reportemails');
            });
            {{--@endcan--}}
        });

        function getDetails(id, route, formid) {
            //  console.log('getDetails(' + id + "," + route + "," + formid + ")");
            $.ajax({
                url: '{!! URL::to('')!!}/' + route + '/' + id,
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
                    //      console.log(data);
                    populate($("#" + formid).find("form"), data);
                    waitingDialog.hide();
                }
            });
        }

        {{--@can('update-review-instance')--}}
        function submitNewForm(vars, route) {
            $.ajax({
                url: '{!! URL::to('')!!}/' + route + '/create',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();

                    if (Number(data.id) > 0) {
                        location.reload(true);
                    }
                    else if ((data.error).length > 0) {
                        alert(data.error);
                    }
                    else {
                        alert('something went wrong with the insertion');
                    }
                }
            });
        }

        function submitCopyForm(vars) {
            $.ajax({
                url: '{!! URL::to('')!!}/reportemails/clone',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();

                    if (Number(data.id) > 0) {
                        // take me to the newly created instance
                        location.replace('{!! URL::to('')!!}/reportemails/' + data.id);
                    }
                    else if ((data.error).length > 0) {
                        alert(data.error);
                    }
                    else {
                        alert('something went wrong with the operation');
                    }
                }
            });
        }

        // Edit
        function submitUpdateForm(vars, route) {
            // console.log(route);
            $.ajax({
                url: '{!! URL::to('')!!}/' + route + '/' + currenteditingid + '/update',
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

        function deleteItem(id, route) {
            $.ajax({
                url: '{!! URL::to('')!!}/' + route + '/destroy',
                type: 'post',
                data: ([{name: 'id', value: id}, {
                    name: '_token',
                    value: '{{csrf_token()}}'
                }]),
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data.status == "1") {
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
                } else if ($ctrl.is("textarea")) {
                    if ($ctrl.hasClass('tinymce')) {
                        // fix for this weird bug where tinymce chucks a wobbly if the content is set to a null value
                        //tinyMCE.get($ctrl.attr('id')).setContent((value === null) ? '' : value);
                        tinyMCE.activeEditor.setContent(value);
                
                    } else {
                        $ctrl.val((value === null) ? '' : value);
                    }
                }
                else {

                    switch ($ctrl.attr("type")) {
                        case "text" :
                        case "hidden":
                            $ctrl.val(value);
                            $ctrl.trigger('change');
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


        {{--@endcan--}}


    </script>
    {!! Breadcrumbs::render('reportemails.index') !!}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        {{--@can('update-review-instance')--}}

        <div class="col-md-12" style="padding-left: 0px">
            <div class="col-md-8 text-left" style="padding-left: 0px">
                <div class="col-sm-12"
                     style="padding-left: 0px"><h3>Feedback email templates</h3>
                </div>
                <div class="col-md-4" style="padding-left: 0px">

                </div>
                <div class="col-md-10 text-left" style="padding-left: 0px">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newdialog">New email
                        template
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            {{--@endcan--}}
            &nbsp;
            <table class="table table-striped">
                <thead class="thead-inverse">
                <tr>
                    <th class="headerSortable header">Label</th>
                    <th class="headerSortable header">Sent count</th>
                    <th></th>

                </tr>
                </thead>
                @if(isset($emails))
                    @foreach ($emails as $email)
                        <tr>
                            <td>
                                <a href="#" data-toggle="modal" data-id="{{$email->id}}"
                                   data-route="reportemails"
                                   data-target="#editdialog">
                                    {{$email->label}}
                                </a>

                            </td>
                            <td>
                                {{$email->logs->count()}}
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-id="{{$email->id}}"
                                   data-target="#deletedialog">
                                    <i class="fa fa-2x fa-times-circle" style='color: #ff6600'></i>
                                </a>
                            </td>
                    @endforeach
                @endif
            </table>
    </fieldset>
    {{--@can('update-review-instance')--}}
    <div id="newdialog" class="modal fade newdialog modalform" role="dialog"
         data-route="reportemails">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New email template</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'role'=>'form'])!!}

                    @include('reports.emails_templates.form.emailform', ['submitButtonText'=>'Add template'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="editdialog" class="modal fade editdialog" role="dialog"
         data-route="reportemails">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update email template</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('reports.emails_templates.form.emailform', ['submitButtonText'=>'Update template'])
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    {{--  Deleting a email template  --}}
    <div id="deletedialog" class="modal fade" role="dialog">
        <div class="modal-dialog" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Really delete?</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('form_common.deletedialog')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    {{--@endcan--}}

@stop

