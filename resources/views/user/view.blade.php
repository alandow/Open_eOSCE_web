@extends('layouts.nomenu')

@section('title')
    {{$user->name}}
@endsection

{{--@section('menu')--}}
{{--@include('layouts.commonmenu')--}}
{{--@endsection--}}
{{--@include('layouts.formincludes')--}}

@section('content')
    {{--@section('title')--}}
    {{--{{$user->name}}--}}

    {{--@endsection--}}

    <script>

        var currentmediaid = 0;

        $(document).ready(function () {

            $('select').select2({
                placeholder: 'Select an option'
            });


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

            // Bootstrap AJAX
            //http://webdesign.tutsplus.com/tutorials/building-a-bootstrap-contact-form-using-php-and-ajax--cms-23068


            @can('activate_user')
            // activate/deactivate support
            // Update user dialogue
            $('#activatedialog').find("form").submit(function (event) {
                $('#activatedialog').modal('hide');
                var vars = $("#activatedialog").find("form").serializeArray();
                vars.push({name: 'id', value: '{{$user->id}}'});
                vars.push({name: '_token', value: '{{ csrf_token() }}'});
                // cancels the form submission so that we can submit it using AJAX
                event.preventDefault();
                waitingDialog.show();
                submitActivateUserForm(vars);
            });
            @endcan




            @if((\Illuminate\Support\Facades\Auth::user()->id == $user->id)||(\Illuminate\Support\Facades\Auth::user()->can('update_other_user')))


            $('#editdetailsdialog').on('shown.bs.modal', function () {
//                $('#editdetailsdialog').find("form").validator();
                changevalidation('updateuserform');
            });

            // Update user dialogue
            $('#editdetailsdialog').submit(function (event) {
                if (!event.isDefaultPrevented()) {

                    $('#editdetailsdialog').modal('hide');
                    var vars = $("#editdetailsdialog").find("form").serializeArray();
                    // little hack to get the multiple values in from the subspecialties input dialog box

                    vars.push({name: 'id', value: '{{$user->id}}'});
                    vars.push({name: '_token', value: '{{ csrf_token() }}'});
                    // cancels the form submission so that we can submit it using AJAX
                    event.preventDefault();
                    waitingDialog.show();
                    submitUpdateUserForm(vars);
                }

            });


            $('#edituserimagedialog').on('shown.bs.modal', function () {
                $('#edituserimagedialog').find("form").validator();

            });

            // intercept edit media event dialogue form submission
            $('#edituserimagedialog').submit(function (event) {
                $('#edituserimagedialog').modal('hide');
                //  var vars = $("#newmediadialog").find("form").serializeArray();
                console.log('edituserimagedialog submission');
                var data = new FormData();
                // file
                jQuery.each($('#userfile')[0].files, function (i, file) {
                    data.append('userfile', file);
                });
                data.append('description', $('#description').val());
                data.append('user_id', {{$user->id}});
                data.append('_method', 'PATCH');
                data.append('_token', '{{ csrf_token() }}');

                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                submitEditUserImageForm(data);
            });

            @endif
        })
        ;


        @if((\Illuminate\Support\Facades\Auth::user()->id == $user->id)||(\Illuminate\Support\Facades\Auth::user()->can('update_other_user')))
        function submitUpdateUserForm(vars) {
            $.ajax({
                url: '{{URL::to('/user/ajaxupdate')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    if (data.status.toString() == '0') {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }

        // Edit media
        function submitEditUserImageForm(vars) {
            $.ajax({
                url: '{{URL::to('userimage/update')}}',
                type: 'post',
                data: vars,
                processData: false,
                contentType: false,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {

                    if (data.status == "1") {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the update');
                    }
                }
            });
        }

        @endif

        @can('activate_user')
        function submitActivateUserForm(vars) {

            $.ajax({
                url: '{{URL::to('/user/activate')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    if (data.status.toString() == '0') {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }

        @endcan



        function changevalidation(frm) {
            //change the validation scheme
            @if(strval(\Request::route()->getName()) != 'user.my')
            console.log('validating ' + frm);
            $("#" + frm).validator('destroy');

            //$("#" + frm).validator('destroy')
            if ($('#' + frm + ' [name="type"]').select2('val') == 'ldap') {
                $('#' + frm + ' :password').removeAttr('required').prop('disabled', true);
                $('#' + frm + ' :password').val('');
                $('#' + frm + ' [name="name"]').removeAttr('required').prop('disabled', true);
            } else {
                $('#' + frm + ' :password').attr('required', true).prop('disabled', false);
                $('#' + frm + ' [name="name"]').attr('required', true).prop('disabled', false);
                if (frm == 'updateuserform') {
                    console.log('updating')
                    console.log('full password')
                    if (($('#' + frm + ' :password:first').val().length > 0)) {
                        $('#' + frm + ' :password').attr('required', true);
                        //$('#edituserform :password:first').next().attr('required', true);
                    } else {
                        console.log('empty password')

                        $('#' + frm + ' :password').removeAttr('required');
                        $('#' + frm + ' :password').val('');
                    }
                }

            }
            $("#" + frm).validator({'disable': true});
            @else
            if ($('#' + frm + ' :password:first').val().length > 0) {
                $('#' + frm + ' :password').attr('required', true);
                //$('#edituserform :password:first').next().attr('required', true);
            } else {
                console.log('empty password')

                $('#' + frm + ' :password').removeAttr('required');
                $('#' + frm + ' :password').val('');
            }
            @endif
        }

        // populates a form with data returned from Laravel
        function populate(frm, data) {
            $.each(data, function (key, value) {
                var $ctrl = $('[name=' + key + ']', frm);
                if ($ctrl.is("select")) {
                    if ($ctrl.attr('multiple')) {
                        $ctrl.select2('val', value.split(',')).trigger('change');
                    } else {
                        $ctrl.select2('val', value);
                    }

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


    @if(strval(\Request::route()->getName()) == 'user.my')
        {!! Breadcrumbs::render('user.my', $user) !!}
    @else
        {!! Breadcrumbs::render('user.show', $user) !!}
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="tabslabels">
        <li class="active"><a data-toggle="tab" href='#detailstab'>User Details</a></li>

        <li><a data-toggle="tab" href='#logtab'>Activity log</a></li>
    </ul>
    <div class="tab-content">
        <div id="detailstab" class="tab-pane active">
            <fieldset style="width: 90%">
                <legend>User details
                    @can('update_other_user')
                        <button class="btn btn-info btn-lg" data-toggle="modal"
                                data-target="#editdetailsdialog">Update
                        </button>@endcan
                    @if(strval(\Request::route()->getName()) != 'user.my')
                        @can('activate_user')
                            <button class="btn btn-info btn-lg" data-toggle="modal"
                                    data-target="#activatedialog">@if (isset( $user->active))
                                    <?php print($user->active == 'true' ? "Deactivate" : "Activate"); ?>
                                @else
                                    Activate
                                @endif
                            </button>@endcan
                    @endif

                </legend>


                <div class="col-md-12">
                    <div class="col-md-6">

                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9">
                            <div class="col-md-4">
                                <strong>Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{$user->name}}
                            </div>
                        </div>
                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9">
                            <div class="col-md-4">
                                <strong>Email:</strong>
                            </div>
                            <div class="col-md-8">
                                {{$user->email}}
                            </div>
                        </div>
                        {{--<div class="col-md-12" style="border-bottom: 1px solid #d9d9d9">--}}
                        {{--<div class="col-md-4">--}}
                        {{--<strong>Contact number:</strong>--}}
                        {{--</div>--}}
                        {{--<div class="col-md-8">--}}
                        {{--{{$user->phone}}--}}
                        {{--</div>--}}
                        {{--</div>--}}

                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9">
                            <div class="col-md-4">
                                <strong>Role(s):</strong>
                            </div>
                            <div class="col-md-8">
                                <ul>
                                    @foreach($user->roles as $role)
                                        <li>{{$role['text']}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9">
                            <div class="col-md-4">
                                <strong>Active:</strong>
                            </div>
                            <div class="col-md-8">
                                @if (isset( $user->active))
                                    <?php print($user->active == 'true' ? "<i class='fa fa-2x fa-check' style='color: green'></i>" : "<i class='fa fa-2x fa-close' style='color: red'></i>"); ?>
                                @else
                                    <i class='fa fa-2x fa-close' style='color: red'></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9">
                            <div class="col-md-4">
                                <strong>Notes:</strong>
                            </div>
                            <div class="col-md-8">
                                {{$user->notes}}
                            </div>

                        </div>
                        @if(($user->id != Auth::user()->id)||$user->hasSudoed())
                            <div class="col-md-12" style="padding-top: 20px">
                                <?php print(\Auth::user()->injectToView($user)); ?>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-12">
                            <img src="{{URL::asset('/user/thumb/'.(isset($user->image['id'])?$user->id:-1).'/400')}}">
                            {{--<img src="{!! URL::to('')!!}/user/{{$user->id}}/thumb/">--}}
                        </div>
                        @if((\Illuminate\Support\Facades\Auth::user()->id == $user->id)||(\Illuminate\Support\Facades\Auth::user()->can('update_other_user')))
                            <div class="col-md-12">
                                <button class="btn btn-info btn-sm" data-toggle="modal"
                                        data-target="#edituserimagedialog">Update image
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </fieldset>
        </div>


        <div id="logtab" class="tab-pane">
            <fieldset style="width: 90%">
                <legend>
                    Activity Log
                    (TODO this will get big, we should put a filter in this. What filters are good?)
                    {{--<a href="{!! URL::to('')!!}/user/{{$user->id}}/usercontactreport" class="btn btn-info" ><i class="fa fa-file-excel-o" aria-hidden="true"></i>--}}
                    {{--Download--}}
                    {{--</a>--}}
                </legend>

                <table class="table table-striped">
                    <thead class="thead-inverse">
                    <th>
                        Real user
                    </th>
                    <th>
                        Action
                    </th>
                    <th>
                        New value
                    </th>
                    <th>
                        Old value
                    </th>
                    <th>
                        Timestamp
                    </th>

                    </thead>
                    @foreach ($user->user_log as $log)
                        <tr>
                            <td>
                                {{\App\User::find($log->real_users_id)->username}}
                            </td>
                            <td>
                                {{$log->crud}} {{$log->action}}
                            </td>

                            <td>
                                {{$log->new_value}}
                            </td>
                            <td>
                                {{$log->old_value}}
                            </td>
                            <td>
                                {{$log->created_at}}
                            </td>

                    @endforeach
                </table>
                {{--{{ $messages->appends(\Illuminate\Support\Facades\Input::except('page'))->fragment('!historytab')->links() }}--}}
            </fieldset>
        </div>
    </div>
    {{--
    If the user can update themselves
    --}}

    @if((\Illuminate\Support\Facades\Auth::user()->id == $user->id)||(\Illuminate\Support\Facades\Auth::user()->can('update_other_user')))


        {{--  Dialog for editing the users details  --}}
        <div id="editdetailsdialog" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Details</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::model($user, ['class'=>'form-horizontal', 'role'=>'form', 'id'=>'updateuserform', 'data-toggle'=>'validator'])!!}

                        @include('user.form.form', ['submitButtonText'=>'Update',  'formid'=>'updateuserform'])

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>


        {{--Add an image to this record--}}


        {{--Update a file--}}
        <div id="edituserimagedialog" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Update File</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open()!!}
                        @include('user.form.imageupload', ['submitButtonText'=>'Update Image', 'user_id'=>$user->id , 'files' => true] )
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        {{--  Deleting a media  --}}
        <div id="deletemediadialog" class="modal fade" role="dialog">
            <div class="modal-dialog" style="width: 300px">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Really delete file?</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open()!!}
                        {{--@include('patients.form.patientfiledelete', [ 'patientid'=>$user->id])--}}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @can('update_other_user')
        <div id="activatedialog" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Really @if (isset( $user->active))
                                <?php print($user->active == 'true' ? "deactivate" : "activate"); ?>
                            @else
                                activate
                            @endif
                            user?</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::open()!!}

                        @include('form_common.confirmdialog')

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endcan

@stop
 