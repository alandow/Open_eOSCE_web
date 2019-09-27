
    <div class="form-group row">
        {!! Form::label('selected_exam_instance_items_items_id', 'Result', ['class'=>'control-label  col-sm-2 text-left']) !!}

        <select class="form-control" id="selected_exam_instance_items_items_id" name="selected_exam_instance_items_items_id" style="width: 300px" required>

        </select>
    </div>


<div class="form-group row">
    {!! Form::label('comments', 'Comment', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {{ Form::textarea('comments', null, ['class'=>'form-control', 'id'=>'updatecomments']) }}
    </div>
</div>

<div class="form-group row">
    {!! Form::label('reason', 'Reason', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {{ Form::textarea( 'reason', null, ['class'=>'form-control', 'required']) }}
    </div>
</div>

<div class="form-group">
    {!! Form::submit('Update', ['class'=>'btn btn-primary form-control']) !!}
</div>