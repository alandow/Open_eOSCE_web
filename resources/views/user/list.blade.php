@extends('layouts.nomenu')

@section('title')
User management
@endsection

{{--@section('menu')--}}
{{--@include('layouts.commonmenu')--}}
{{--@endsection--}}
@section('content')
    <!-- include form specific libraries -->
    {{--@include('layouts.formincludes')--}}
    <script>

        var currentid = -1;

        $(document).ready(function () {

            $('select').select2({
                placeholder: 'Select an option'
            });


            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                if ((typeof $(this).data('id')) !== 'undefined') {
                    currentid = $(this).data('id');
                    if ($(this).data('target') == "#editdialog") {
                        getUserDetails(currentid);
                    }
                }
            });


            $('#newuserdialog').on('shown.bs.modal', function () {

                changevalidation('newuserdialog');
            });


            // New patient event dialogue
            $('#newuserdialog').submit(function (event) {
                if (!event.isDefaultPrevented()) {
                    $('#newuserdialog').modal('hide');

                    var vars = $("#newuserform").serializeArray();
                    //vars.push({name: 'type', value: $("#newuserform :input[name='type']").select2('val')});
                    console.log(vars);
                    // cancels the form submission
                    event.preventDefault();
                    waitingDialog.show();
                    submitNewUserForm(vars);
                }
            });

            $('#editdialog').on('shown.bs.modal', function () {
                $("#edituserform").validator();
            });


            $('#deletedialog').submit(function (event) {
                $('#deletedialog').modal('hide');
                var vars = $("#deletedialog").find("form").serializeArray();
                vars.push({name: 'id', value: currentid});
                //vars.push({name: '_method', value: 'DELETE'});
                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                deleteUser(vars);
            });

        });


        /*
         *
         * New user AJAX
         *
         */
        function submitNewUserForm(vars) {
            $.ajax({
                url: '{{URL::to('user/ajaxstore')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    console.log(data.status)
                    if (data.status.toString() == '0') {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }

        // update user AJAX

        function getUserDetails(id) {
            console.log('getUserDetails:' + id);
            $.ajax({
                url: '{!! URL::to('user')!!}/' + id,
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
                    //  console.log(data);
                    //$("#editcontactdialog").find("form").values(data);
                    populate($("#edituserform"), data);
                    changevalidation('edituserform');
                    waitingDialog.hide();
                }
            });
        }

        function submitUpdateUserForm(vars) {
            $.ajax({
                url: '{{URL::to('user/ajaxupdate')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    if (data.status > 0) {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }

        function changevalidation(frm) {
            //change the validation scheme
            $("#" + frm).validator('destroy');

            //$("#" + frm).validator('destroy')
            if ($('#' + frm + ' [name="type"]').select2('val') == 'ldap') {
                $('#' + frm + ' :password').removeAttr('required').prop('disabled', true);
                $('#' + frm + ' :password').val('');
                $('#' + frm + ' [name="name"]').removeAttr('required').prop('disabled', true);
            } else {
                $('#' + frm + ' :password').attr('required', true).prop('disabled', false);
                $('#' + frm + ' [name="name"]').attr('required', true).prop('disabled', false);
                if (frm == 'edituserform') {
                    if ($('#edituserform :password:first').val().length > 0) {
                        $('#edituserform :password').attr('required', true);
                        //$('#edituserform :password:first').next().attr('required', true);
                    } else {
                        console.log('empty password')

                        $('#edituserform :password').removeAttr('required');
                        $('#edituserform :password').val('');
                    }
                }

            }
            $("#" + frm).validator({'disable': true});
        }


        function deleteUser(vars) {
            $.ajax({
                url: '{{URL::to('user/ajaxdestroy')}}',
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
                var $ctrl = $('[name="' + key + '"]', frm);
                if ($ctrl.is("select")) {
                    if ($ctrl.attr('multiple')) {
                        console.log(key, value)
                        //  $ctrl.select2('val', value.split(',')).trigger('change');
                    } else {
                        //$ctrl.select2('val', value);
                    }
                } else {

                    switch ($ctrl.attr("type")) {
                        case "text" :
                        case "hidden":


                            $ctrl.val(value);
                            // special case if a colorpicker
                            if ($ctrl.parent().hasClass('colorpicker-component')) {
                                $ctrl.parent().colorpicker('setValue', value);
                            }
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
    {!! Breadcrumbs::render('user.index') !!}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">

        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newuserdialog">New User
            <i class="fa fa-user-plus"></i></button>
        &nbsp;
        <table class="table table-striped">
            <thead class="thead-inverse">
            <tr>
                <th class="headerSortable header">User Login</th>
                <th class="headerSortable header">Login Type</th>
                <th class="headerSortable header">User Name</th>
                <th class="headerSortable header">User Role(s)</th>
                <th></th>
            </tr>
            </thead>

            @foreach ($users as $user)
                <tr>
                    <td>
                        <a href="{{action('UserController@show', $user->id)}}">
                            {{$user->username}}
                        </a>
                    </td>
                    <td>
                        {{$user->type}}
                    </td>

                    <td>
                        {{$user->name}}
                    </td>
                    <td>
                        <ul>
                            @foreach($user->roles as $role)
                                <li>{{$role['text']}}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <a href="#" data-toggle="modal" data-id="{{$user->id}}"
                           data-target="#deletedialog">
                            Delete
                        </a>
                    </td>

            @endforeach

        </table>
        {{ $users->links() }}
    </fieldset>

    <div id="newuserdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New User</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'role'=>'form', 'id'=>'newuserform', 'data-toggle'=>'validator'])!!}

                    @include('user.form.form', ['submitButtonText'=>'Add User',   'formid'=>'newuserform'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="editdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update User</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'role'=>'form', 'id'=>'edituserform',  'data-toggle'=>'validator'])!!}

                    @include('user.form.form', ['submitButtonText'=>'Update User',  'formid'=>'edituserform'])

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
                    <h4 class="modal-title">Really delete user?</h4>
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
