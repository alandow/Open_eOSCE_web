{{--This is the main form for the Patient class. It handles creation and update functionality--}}
{{--TODO format this properly- it'll end up as an injected jQuery dialog I reckon...--}}

<!-- Temp bodge -->
{{--{!! Form::hidden('user_id', Auth::user()->id) !!}--}}

<div class="form-group row">
    {!! Form::label('edit_type', 'Login Type', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::select('type', array('ad' => 'Institution Login', 'manual' => 'Manual'), null, ['class'=>'form-control' , 'required', 'onchange'=>'changevalidation(this.form)', 'id'=>'type']) !!}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('username', 'User Login', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text('username', null, ['class'=>'form-control' , 'required']) !!}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('name', 'User Full Name', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text('name', null, ['class'=>'form-control' , 'required']) !!}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('role_id', 'Role', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <select class="select2 form-control" name="role_id" id="role_id" style="width: 300px">
        <option value='-1'>None</option>
        @foreach ($roles as $role)
            <option value='{{$role->id}}'>{{$role->text}}</option>
        @endforeach
    </select>
</div>

<div class="form-group row">
    {!! Form::label('site_id', 'Site', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <select class="select2 form-control" name="site_id" id="site_id" style="width: 300px">
        <option value='-1'>None</option>
        @foreach ($sites as $site)
            <option value='{{$site->id}}'>{{$site->text}}</option>
        @endforeach
    </select>
</div>
<div class="form-group row">
    {!! Form::label('password1', 'Password', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        <input type="password" id="password1" class='form-control' required>

    </div>
</div>
<div class="form-group row">
    {!! Form::label('password2', 'Confirm', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        <input type="password" class='form-control' id="password2" required, data-match='#password1'
               data-match-error="Whoops, these don't match" placeholder="Confirm">
        <div class="help-block with-errors"></div>
    </div>

</div>

<div class="form-group">
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>