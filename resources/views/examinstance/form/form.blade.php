<div class="form-group row">
    {!! Form::label('template_id', 'Template', ['class'=>'control-label col-sm-2   text-left']) !!}
    <div class="col-sm-10">
        <select class="form-control" name="template_id" style="width: 300px">
            <option value='-1'>No template</option>
            @foreach ($templates as $template)
                <option value='{{$template->id}}'>{{$template->name}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    {!! Form::label('name', 'Exam Name', ['class'=>'control-label  col-sm-2 text-left']) !!}
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
<div class="form-group row">
    {!! Form::label('owned_by_id', 'Owner', ['class'=>'control-label col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::select('owned_by_id', $users->pluck('name', 'id'), null, ['style'=>"width: 300px", 'class'=>'form-control', 'required']) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::button('<i class="fa fa-floppy-o" aria-hidden="true"></i> '.$submitButtonText, ['type' => 'submit', 'class'=>'btn btn-primary form-control']) !!}
</div>