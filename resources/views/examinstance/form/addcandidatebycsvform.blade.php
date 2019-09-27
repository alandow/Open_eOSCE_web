<div class="form-group">
    {!! Form::label('userfile', 'Select File (CSV only)') !!}
    <input type="file" id="userfile" name="userfile" accept="text/csv" required>

    <div id="currentfilename"></div>
</div>
<div class="form-group">
    {{--<button type="submit" class="btn btn-primary">Submit</button>--}}
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>



