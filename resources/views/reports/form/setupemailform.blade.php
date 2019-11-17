<div class="form-group row">
    <div class="col-sm-12">{!! Form::label('template_id', 'Use template', ['class'=>'control-label  text-left']) !!}</div>

    <div class="col-sm-12">
        {!! Form::select('template_id', $emailtemplates->pluck('label', 'id'), null, ['style'=>"width: 300px", 'class'=>'form-control', 'required']) !!}
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-12"><label class="'control-label text-left"><span style="text-decoration: underline;">Exclude</span> items from report</label></div>

    @foreach($exam->exam_instance_items as $exam_instance_item)
        @if($exam_instance_item->heading!='1')
            <div class="col-sm-12">
                <div class="checkbox checkbox-danger">
                    <input type="checkbox" value="{{$exam_instance_item->id}}"
                    @if(in_array($exam_instance_item->id, json_decode($exam->email_parameters)->exclude_items))
                    checked
                    @endif
                           name="exclude[]"><label>{{$exam_instance_item->label}}

                        @if($exam_instance_item->exclude_from_total=='1')
                            (formative)
                        @endif</label>
                </div>
            </div>
        @endif
    @endforeach
</div>
<div class="form-group row">
    <div class="col-sm-12"><label class="'control-label text-left"><span style="text-decoration: underline;">Exclude</span> comments from report</label></div>
    <div class="col-sm-12">
        <div class="checkbox checkbox-danger">
            <input type="checkbox" value="1"
                   @if(json_decode($exam->email_parameters)->exclude_items_comments=='1')
                   checked
                   @endif
                   name="exclude_items_comments"><label>Item comments</label>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="checkbox checkbox-danger">
            <input type="checkbox" value="1"
                   @if(json_decode($exam->email_parameters)->exclude_overall_comments=='1')
                           checked
                           @endif
                   name="exclude_overall_comments"><label>Overall comments</label>
        </div>
    </div>
</div>



<div class="form-group">

    {!! Form::submit('Save', ['class'=>'btn btn-primary form-control']) !!}
</div>