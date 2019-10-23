<div class="form-group row">
    <div class="col-sm-12">
        {!! Form::label('label', 'Template Label', ['class'=>'control-label  text-left']) !!}
    </div>
    <div class="col-sm-12">
        {!! Form::text('label',null, [ 'class'=>'form-control', 'required']) !!}
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-12">
        {!! Form::label('subject', 'Subject- use {name} for student name,  {exam} for examination label', ['class'=>'control-label text-left']) !!}
    </div>
    <div class="col-sm-12">
        {!! Form::text('subject',null, [ 'class'=>'form-control', 'required']) !!}
    </div>
</div>
<div class="form-group row ">
    <div class="col-sm-12">
        {!! Form::label('text', 'Email text- use {name} for student name, {exam} for examination label, {results} for results table', ['class'=>'control-label  text-left']) !!}
    </div>
    <div class="col-sm-12">
        {!! Form::textarea('text', null, ['class'=>'form-control tinymce', 'id'=>'text1']) !!}
    </div>
</div>
<div class="form-group">

    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>