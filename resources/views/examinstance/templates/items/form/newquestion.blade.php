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
    {{--{!! Form::label('heading', 'This is a heading', ['class'=>'control-label  col-sm-2 text-left']) !!}--}}
    <div class="col-sm-10">
        <div class="checkbox checkbox-success">
            <input type="checkbox" name="heading" value="1" onclick='if($(this).prop("checked")==true){$(".template_text").hide()}else{$(".template_text").show()}'>
            <label>This is a heading</label>
        </div>
        {{--{!! Form::checkbox('heading', 1, false, ['id'=>'heading_cb', 'onclick' => 'if($("#heading_cb").prop("checked")==true){$(".template_text").hide()}else{$(".template_text").show()}']) !!}--}}
    </div>
</div>

<div class="form-group row template_text" style="padding-left: 10px">
    <fieldset style="width: 100%">
        <legend style="">Assessment Items
            <button id="additemitembut" type="button" class="btn btn-info btn-sm" onclick="addItemItem()"><i
                        class="fa fa-plus" aria-hidden="true"></i></button>
        </legend>

        <table class="table table-striped table-condensed" id="itemitemstbl">
            <thead>
            <tr>
                <th>Label</th>
                <th>Description</th>
                <th>Value</th>
                <th class="inlineeditablecommentlabel">Needs comment</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </fieldset>
</div>
<div class="form-group row template_text">
    <legend> <a style="padding-left: 10px" data-toggle="collapse" href="#newadvanced" aria-expanded="false">Advanced... </a></legend>
</div>
<div class="collapse" id="newadvanced" style="width: 100%">

    <div class="form-group row">
        <div class="col-sm-10">
            <div class="checkbox checkbox-info">
                <input type="checkbox" name="exclude_from_total" value="1" >
                <label>Exclude from total/formative</label>
            </div>
        </div>
        {{--{!! Form::label('exclude_from_total', 'Exclude from total/formative', ['class'=>'control-label  col-sm-2 text-left']) !!}--}}
        {{--<div class="col-sm-10">--}}
            {{--{!! Form::checkbox('exclude_from_total', 1, false) !!}--}}
        {{--</div>--}}
    </div>
    <div class="form-group row">
        <div class="col-sm-10">
        <div class="checkbox checkbox-info">
            <input type="checkbox" name="no_comment" value="1" onchange='if($("#no_comment").prop("checked")){
            $(".inlineeditablecomment").hide();
            $(".inlineeditablecommentlabel").hide();
            }else{
            $(".inlineeditablecomment").show();
            $(".inlineeditablecommentlabel").show();
            }'>
            <label>Hide comments input</label>
        </div>
        </div>
        {{--{!! Form::label('no_comment', 'Hide comments input', ['class'=>'control-label  col-sm-2 text-left']) !!}--}}
        {{--<div class="col-sm-10">--}}
            {{--{!! Form::checkbox('no_comment', 1, false, ['id'=>'no_comment', 'onchange'=>'if($("#no_comment").prop("checked")){--}}
                       {{--$(".inlineeditablecomment").hide();--}}
                       {{--$(".inlineeditablecommentlabel").hide();--}}
                   {{--}else{--}}
                       {{--$(".inlineeditablecomment").show();--}}
                       {{--$(".inlineeditablecommentlabel").show();--}}
                   {{--}']) !!}--}}
        {{--</div>--}}
    </div>
</div>
<div class="form-group">
    {!! Form::submit($submitButtonText, ['class'=>'btn btn-primary form-control']) !!}
</div>