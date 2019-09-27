@if(strval(\Request::route()->getName()) != 'user.my')
    <div class="form-group row">
        {!! Form::label('type', 'Login Type', ['class'=>'control-label  col-sm-2 text-left']) !!}
        <div class="col-sm-10">
            {!! Form::select('type', array('manual' => 'Manual'),null , ['class'=>'form-control select2' ,"style"=>"width: 300px",  'required',  'onchange'=>'changevalidation("'.$formid.'")']) !!}
        </div>
    </div>
@endif
<div class="form-group row">
    {!! Form::label('username', 'User Login', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {{ Form::input('text', 'username', null, ['class'=>'form-control', 'required']) }}
        {{--<input type="text" name="username" class='form-control' required>--}}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('name', 'User Full Name', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {{ Form::input('text', 'name', null, ['class'=>'form-control', 'required']) }}
        {{--<input type="text" name="name" class='form-control' required>--}}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('email', 'User Email', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {{ Form::input('email', 'email', null, ['class'=>'form-control', 'required']) }}
        {{--<input type="text" name="name" class='form-control' required>--}}
    </div>
</div>
@if(strval(\Request::route()->getName()) != 'user.my')
    <div class="form-group row">
        {!! Form::label('role_id[]', 'Role(s)', ['class'=>'control-label  col-sm-2 text-left']) !!}

        <select class=" form-control" name="role_id[]" style="width: 300px" required multiple>
            @foreach ($roles as $role)
                <option value='{{$role->id}}'
                        @foreach($user->roles as $userrole)
                        @if($userrole->id == $role->id)
                        selected
                        @endif

                        @endforeach
                >{{$role->text}}</option>
            @endforeach
        </select>
    </div>
@endif
<div class="form-group row">
    {!! Form::label('password1', 'Password', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">

        <input type="password" class='form-control' data-minlength="5" onchange="changevalidation('{{$formid}}')"
               onblur="changevalidation('{{$formid}}')" )">
        <div class="help-block">Minimum of 5 characters</div>
    </div>
</div>
<div class="form-group row">
    {!! Form::label('password2', 'Confirm', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        <input type="password" class='form-control' name="password" data-match='#{{$formid}} :password:first'
               onchange="changevalidation('{{$formid}}')" onblur="changevalidation('{{$formid}}')"
               data-match-error="Whoops, these don't match" placeholder="Confirm">
        <div class="help-block with-errors"></div>
    </div>

</div>

<div class="form-group">
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>