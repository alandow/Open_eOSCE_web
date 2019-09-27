<div class="form-group row">
    {!! Form::label('studentid', 'Student ID', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("studentid", null, ['class' => 'form-control', 'id' => 'studentid', 'required']) !!}
    </div>
</div>
<div class="form-group row">

    {!! Form::label('fname', 'Student First Name', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("fname", null, ['class' => 'form-control', 'id' => 'fname', 'required']) !!}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('lname', 'Student Last Name', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("lname", null, ['class' => 'form-control', 'id' => 'lname', 'required']) !!}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('email', 'Student Email', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::email("email", null, ['class' => 'form-control', 'id' => 'email', 'required']) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('studentimage', 'Select Student Image (image only)') !!}

    <input type="file" id="studentimage" name="studentimage">

    <div id="currentfilename"></div>
</div>

<div class="form-group">
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>