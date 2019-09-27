<div class="form-group row">
    {!! Form::label('name', 'Template Name', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("name", null, ['class' => 'form-control', 'id' => 'description', 'required']) !!}
        {{--<input type="text" name="studentid" id="studentid" class='form-control' required>--}}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('description', 'Description', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("description", null, ['class' => 'form-control', 'id' => 'description', 'required']) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>