<div class="form-group row">
    {!! Form::label('name', 'Item Label', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("label", null, ['class' => 'form-control',  'required']) !!}
        {{--<input type="text" name="studentid" id="studentid" class='form-control' required>--}}
    </div>
</div>
<div class="form-group row ">
    {!! Form::label('description', 'Description', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::text("description", null, ['class' => 'form-control',]) !!}
    </div>
</div>
<div class="form-group row">
    {!! Form::label('heading', 'This is a heading', ['class'=>'control-label  col-sm-2 text-left']) !!}
    <div class="col-sm-10">
        {!! Form::checkbox('heading', 1, false, ['id'=>'edit_heading_cb', 'onclick' => 'if($("#edit_heading_cb").prop("checked")==true){$(".edit_template_text").hide()}else{$(".edit_template_text").show()}']) !!}
    </div>
</div>

<div class="form-group row edit_template_text" style="padding-left: 10px">
    <fieldset style="width: 100%">
        <legend style="">Assessment Items
            <button id="additemitembut" type="button" class="btn btn-info btn-sm" onclick="addUpdateItemItem()"><i
                        class="fa fa-plus" aria-hidden="true"></i></button>
        </legend>

        <table class="table table-striped table-condensed" id="updateitemitemstbl">
            <thead>
            <tr>
                <th>Label</th>
                <th>Description</th>
                <th>Value</th>
                <th class="updateinlineeditablecommentlabel">Needs comment</th>
                <th></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </fieldset>
</div>
<div class="form-group row edit_template_text">
    <legend><a style="padding-left: 10px" data-toggle="collapse" href="#editadvanced"
               aria-expanded="false">Advanced... </a></legend>
</div>
<div class="collapse edit_template_text" id="editadvanced" style="width: 100%">
    <div class="form-group row ">
        {!! Form::label('show_if_id', 'Show this question', ['class'=>'control-label template_text col-sm-2 text-left']) !!}
        <div class="col-sm-10">
            <select class="select2 form-control" id="editshow_if_id" name="show_if_id"
                    onchange="getItemItems('edit_show_if_answer_id', this.value)" style="width: 100%">
                <option value='-1'>Not used</option>
                @foreach ($exam->exam_instance_items as $item)
                    @if($item->heading!=1)
                        <option value='{{$item->id}}'>{{$item->label}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('edit_show_if_answer_id', 'Has selected', ['class'=>'control-label template_text col-sm-2 text-left']) !!}
        <div class="col-sm-10">
            <select class="select2 form-control" id="edit_show_if_answer_id" name="show_if_answer_id"
                    style="width: 100%">

            </select>
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('exclude_from_total', 'Exclude from total/formative', ['class'=>'control-label  col-sm-2 text-left']) !!}
        <div class="col-sm-10">
            {!! Form::checkbox('exclude_from_total', 1, false) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('no_comment', 'Hide comments input', ['class'=>'control-label  col-sm-2 text-left']) !!}
        <div class="col-sm-10">
            {!! Form::checkbox('no_comment', 1, false, ['id'=>'edit_no_comment', 'onchange'=>'if($("#edit_no_comment").prop("checked")){
                            $(".updateinlineeditablecomment").hide();
                            $(".updateinlineeditablecommentlabel").hide();
                        }else{
                            $(".updateinlineeditablecomment").show();
                            $(".updateinlineeditablecommentlabel").show();
                        }']) !!}
        </div>
    </div>
</div>
<div class="form-group">
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>