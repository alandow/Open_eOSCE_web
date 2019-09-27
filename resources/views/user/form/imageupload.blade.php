<div class="form-group">

    {!! Form::label('userfile', 'Select File (images only)') !!}

    <input type="file" id="userfile" name="userfile" required>

    <div id="currentfilename"></div>
</div>
<div class="form-group">
    {!! Form::label('description', 'Description (optional)') !!}
    {!! Form::text('description', null, ['class'=>'form-control', 'id'=>'description', ]) !!}
    <div class="help-block with-errors"></div>
</div>
<div class="form-group">
    {{--<button type="submit" class="btn btn-primary">Submit</button>--}}
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>



