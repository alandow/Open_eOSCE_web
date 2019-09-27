@extends('layouts.mainapp')

{{--Access control this--}}
@section('menu')

    <li>
        <a href="{{ url('/examtemplates') }}"><i class="fa fa-fw fa-cog"></i>Examination Templates</a>
    </li>
    <li>
        <a href="{{ url('/examitemtemplates') }}"><i class="fa fa-fw fa-cog"></i>Item Templates</a>
    </li>
    <li>
        <a href="{{ url('/examemails') }}"><i class="fa fa-fw fa-cog"></i>Email templates</a>
    </li>
@endsection


@section('content')
    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-editable.css')}}">
    <script src="{{ URL::asset('resources/assets/js/bootstrap-editable.min.js') }}"></script>
    <style>
        .modal-body {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
        }

        .fa-check {
            color: green;
        }

        .fa-times {
            color: red;
        }
    </style>
    <script>

        var currentid = -1;

        $.fn.editable.defaults.mode = 'inline';

        $(document).ready(function () {

            $('select').select2();



            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                currenteditingid = $(this).data('id');
                switch ($(this).data('target')) {

                    case '#edititemdialog':
                        currenteditingid = $(this).data('id');
                        console.log(currenteditingid)
                        getItemDetails(currenteditingid);
                        break;
                    case '#deleteitemdialog':
                        currentdeleteroute = $(this).data('route');
                        console.log($(this).data('route'));
                        currentdeletingid = $(this).data('id');
                        console.log($(this).data('id'));
                        break;

                    default:
                        currenteditingid = -1;
                        currentdeletingid = -1;
                        break;
                }

            });

            $('#additemdialog').submit(function (event) {
                // cancels the form submission
                console.log('additemdialog submitted');
                event.preventDefault();
                //$(this).modal('hide');
                var vars = $(this).find("form").serializeArray();
                console.log(vars);
                var i = 0;
                //@TODO make this a bit better,
                var items = '{"items":[';
                $(".itemitemrow").each(function () {
                    i++;
                    items += '{"label":"' + $(this).find('.inlineeditablelabel').editable('getValue')['label'] + '",' +
                            '"description":"' + $(this).find('.inlineeditabledescription').editable('getValue')['description'] + '",' +
                            '"value":"' + $(this).find('.inlineeditablevalue').editable('getValue')['value'] + '",' +
                            '"needscomment":"' + $(this).find('.inlineeditablecomment').editable('getValue')['needscomment'] + '"},';

                    //  vars.push({name: 'id', value: currenteditingid});
                })
                items = items.slice(0, -1);
                items += ']}';
                console.log(items);

                //  var vars = $("#newmediadialog").find("form").serializeArray();

                if (i > 0) {
                    vars.push({name: 'items', value: items});
                }
                // waitingDialog.show();
                submitNewItemForm(vars);
            });

            $('#edititemdialog').submit(function (event) {
                // cancels the form submission
                console.log('edititemdialog submitted');
                event.preventDefault();
                //$(this).modal('hide');
                var vars = $(this).find("form").serializeArray();
                /* Because serializeArray() ignores unset checkboxes and radio buttons: */
                vars = vars.concat(
                        $(this).find('form').find('input[type=checkbox]:not(:checked)').map(
                                function () {
                                    return {"name": this.name, "value": ''}
                                }).get()
                );
                console.log(vars);
                var i = 0;
                //@TODO make this a bit better,
                var items = '{"items":[';
                $(".updateitemitemrow").each(function () {
                    i++;
                    items += '{"label":"' + $(this).find('.updateinlineeditablelabel').editable('getValue')['label'] + '",' +
                            '"description":"' + $(this).find('.updateinlineeditabledescription').editable('getValue')['description'] + '",' +
                            '"value":"' + $(this).find('.updateinlineeditablevalue').editable('getValue')['value'] + '",' +
                            '"id":"' + $(this).find('.updateinlineeditablevalue').data('pk') + '",' +
                            '"needscomment":"' + $(this).find('.updateinlineeditablecomment').editable('getValue')['needscomment'] + '"},';

                    //  vars.push({name: 'id', value: currenteditingid});
                })
                items = items.slice(0, -1);
                items += ']}';
                //  console.log(items);

                //  var vars = $("#newmediadialog").find("form").serializeArray();
                if ($(".updateitemitemrow").length > 0) {
                    vars.push({name: 'items', value: items});
                }
                vars.push({name: 'id', value: currenteditingid});

                // waitingDialog.show();
                submitUpdateItemForm(vars);
            });

            $('#deletedialog').submit(function (event) {
                $('#deletedialog').modal('hide');
                var vars = $("#deletedialog").find("form").serializeArray();
                vars.push({name: 'id', value: currentid});
                //vars.push({name: '_method', value: 'DELETE'});
                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                deleteItem(vars);
            });

        });

        function submitNewItemForm(vars, route) {
            // console.log(route);
            $.ajax({
                url: '{!! URL::to('')!!}/examitemtemplates/ajaxstore',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    //   waitingDialog.hide();
                    if (data.status.toString() == '0') {
                        location.reload();
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the update');
                    }
                }
            });
        }

        function submitUpdateItemForm(vars, route) {
            console.log(vars);
            $.ajax({
                url: '{!! URL::to('')!!}/examitem/update',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    //   waitingDialog.hide();
                    if (data.status == 0) {
                        location.reload();
                    } else {
                        waitingDialog.hide();
                        alert('something went wrong with the update');
                    }
                }
            });
        }


        // adding items to items
        /////////////////////////////////////
        //inline editing
        /////////////////////////////////////
        function addItemItem() {
            // AJAX call here to add a new row?

            $("#itemitemstbl tbody").append('<tr class="itemitemrow">' +
                    '<td><a href="#" class="inlineeditablelabel" data-name="label" data-type="text" data-pk="" data-url="" data-title="Label"></a></td>' +
                    '<td><a href="#" class="inlineeditabledescription" data-name="description" data-type="text" data-pk="" data-url="" data-title="Description"></a></td>' +
                    '<td><a href="#" class="inlineeditablevalue" data-name="value" data-type="number" data-pk="" data-url="" data-title="value">0</a></td>' +
                    '<td><a href="#" class="inlineeditablecomment" data-name="needscomment" data-type="select" data-pk="" data-url="" data-title="comment">No</a></td>' +
                    '<td><a href="#" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-times" style="font-size: 2em; color: red"aria-hidden="true"></i></a></td>' +
                    '</tr> ');


            $('.inlineeditablelabel').editable({
                url: '',
                send: 'never',
                emptytext: 'Click to edit',
                onblur: 'submit'
            });
            $('.inlineeditabledescription').editable({
                url: '',
                send: 'never',
                emptytext: 'Click to edit',
                onblur: 'submit'
            });
            $('.inlineeditablevalue').editable({
                step: 'any',
                url: '',
                send: 'never',
                onblur: 'submit'
            });
            $('.inlineeditablecomment').editable({

                source: [
                    {value: 'true', text: 'Yes'},
                    {value: 'false', text: 'No'}

                ],
                send: 'never',
                url: '',
                onblur: 'submit'
            });

        }


        function addUpdateItemItem() {
            // AJAX call here to add a new row?

            $("#updateitemitemstbl tbody").append('<tr class="updateitemitemrow">' +
                    '<td><a href="#" class="updateinlineeditablelabel" data-name="label" data-type="text" data-pk="" data-url="" data-title="Label"></a></td>' +
                    '<td><a href="#" class="updateinlineeditabledescription" data-name="description" data-type="text" data-pk="" data-url="" data-title="Description"></a></td>' +
                    '<td><a href="#" class="updateinlineeditablevalue" data-name="value" data-type="number" data-pk="" data-url="" data-title="value">0</a></td>' +
                    '<td><a href="#" class="updateinlineeditablecomment" data-name="needscomment" data-type="select" data-pk="" data-url="" data-title="comment">No</a></td>' +
                    '<td><a href="#" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-times" style="font-size: 2em; color: red"aria-hidden="true"></i></a></td>' +
                    '</tr> ');


            $('.updateinlineeditablelabel').editable({
                url: '',
                send: 'never',
                emptytext: 'Click to edit',
                onblur: 'submit'
            });
            $('.updateinlineeditabledescription').editable({
                url: '',
                send: 'never',
                emptytext: 'Click to edit',
                onblur: 'submit'
            });
            $('.updateinlineeditablevalue').editable({
                step: 'any',
                url: '',
                send: 'never',
                onblur: 'submit'
            });
            $('.updateinlineeditablecomment').editable({

                source: [
                    {value: 'true', text: 'Yes'},
                    {value: 'false', text: 'No'}

                ],
                send: 'never',
                url: '',
                onblur: 'submit'
            });


        }


        function getItemDetails(id) {
            $("#updateitemitemstbl tbody tr").remove();
            console.log('getItemDetails(' + id + ")");
            $.ajax({
                url: '{!! URL::to('')!!}/examitem/' + id,
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
//                        console.log('data is:');
                    console.log($(data)[0].show_if_id);
                    // populate update form
                    // bit of hacky display control

                    // standard fields
                    $.each(data, function (key, value) {
                        var $ctrl = $('#edititemform [name=' + key + ']');
                        // if we find the criteria items
                        if (key == 'items') {
                            for (var i = 0; i < value.length; i++) {

                                // make the table of criteria items
//
                                $("#updateitemitemstbl tbody").append('<tr class="updateitemitemrow">' +
                                        '<td><a href="#" class="updateinlineeditablelabel" data-name="label" data-type="text" data-pk="' + value[i].id + '" data-url="" data-title="Label">' + value[i].label + '</a></td>' +
                                        '<td><a href="#" class="updateinlineeditabledescription" data-name="description" data-type="text" data-pk="' + value[i].id + '" data-url="" data-title="Description">' + value[i].description + '</a></td>' +
                                        '<td><a href="#" class="updateinlineeditablevalue" data-name="value" data-type="number" data-pk="' + value[i].id + '" data-url="" data-title="value">' + value[i].value + '</a></td>' +
                                        '<td><a href="#" class="updateinlineeditablecomment" data-name="needscomment" data-type="select" data-pk="' + value[i].id + '" data-url="" data-title="comment">' + (value[i].hascomment == 'true' ? 'Yes' : 'No') + '</a></td>' +
                                        '<td><a href="#" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-times" style="font-size: 2em; color: red"aria-hidden="true"></i></a></td>' +
                                        '</tr> ');

                            }
                        } else {
                            switch ($ctrl.attr("type")) {
//                                    case "text" :
//                                        $ctrl.val(value);
                                case "checkbox":
                                    $ctrl.each(function () {
                                        if ($(this).attr('value') == value) {
                                            $(this).prop("checked", true);
                                        }else{
                                            $(this).prop("checked", false);
                                        }
                                    });
                                    break;

                                default:
                                    $ctrl.val(value);
                            }
                        }

                        if($("#edit_no_comment").prop('checked')){
                            $(".updateinlineeditablecomment").hide();
                            $(".updateinlineeditablecommentlabel").hide();
                        }else{
                            $(".updateinlineeditablecomment").show();
                            $(".updateinlineeditablecommentlabel").show();
                        }

                    });


                    $('.updateinlineeditablelabel').editable({
                        url: '',
                        send: 'never',
                        emptytext: 'Click to edit',
                        onblur: 'submit'
                    });
                    $('.updateinlineeditabledescription').editable({
                        url: '',
                        send: 'never',
                        emptytext: 'Click to edit',
                        onblur: 'submit'
                    });
                    $('.updateinlineeditablevalue').editable({
                        step: 'any',
                        url: '',
                        send: 'never',
                        onblur: 'submit'
                    });
                    $('.updateinlineeditablecomment').editable({

                        source: [
                            {value: 'true', text: 'Yes'},
                            {value: 'false', text: 'No'}

                        ],
                        send: 'never',
                        url: '',
                        onblur: 'submit'
                    });

//  advanced stuff

                    if ($(data)[0].show_if_id !== null) {
                        $('#editshow_if_id').val($(data)[0].show_if_id).trigger("change");
                        getItemItems('edit_show_if_answer_id', $(data)[0].show_if_id, $(data)[0].show_if_answer_id);
                    }

                    // finally hide stuff if it's a heading
                    if ($("#edit_heading_cb").prop("checked") == true) {
                        $(".edit_template_text").hide()
                    } else {
                        $(".edit_template_text").show()
                    }
                    //populate($("#" + formid).find("form"), data);
                    waitingDialog.hide();
                }
            });
        }


        /*
         *
         * New user AJAX
         *
         */
        function submitNewForm(vars) {
            $.ajax({
                url: '{{URL::to('exam/ajaxstore')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    console.log(data.status)
                    if (data.status > 0) {
                        location.reload(true);
                    } else {
                        waitingDialog.hide();
                        alert(data.statusText);
                    }
                }
            });
        }




        /*
         *
         * Helper functions
         *
         */

        // populates a form with data returned from Laravel
        function populate(frm, data) {
            $.each(data, function (key, value) {
                var $ctrl = $('[name=' + key + ']', frm);
                if ($ctrl.is("select")) {
                    $ctrl.select2('val', value);
                } else {

                    switch ($ctrl.attr("type")) {
                        case "text" :
                        case "hidden":
                            $ctrl.val(value);
                            break;
                        case "radio" :
                        case "checkbox":
                            $ctrl.each(function () {
                                if ($(this).attr('value') == value) {
                                    $(this).attr("checked", value);
                                }
                            });
                            break;

                        default:
                            $ctrl.val(value);
                    }

                }
            });
        }
    </script>
    {!! Breadcrumbs::render('examitemtemplates.index') !!}
    <fieldset style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <div style="width: 100%; "></div>
        <div style="float: left; padding-left: 10px">
            <button type="button" class="btn btn-primary" style="float: right" data-toggle="modal"
                    data-target="#additemdialog">New Item <i class="fa fa-plus" aria-hidden="true"></i></button>
        </div>
        &nbsp;
        <table class="table table-striped table-bordered" id="questionstbl">
            <thead class="thead-inverse">

            <th>
                Label
            </th>
            <th>
                Description
            </th>

            <th>
                Criteria
            </th>
            <th>
                Advanced
            </th>
            <th>
            </th>
            </thead>
            <tbody id="itemsstablebody">
            @foreach($items as $exam_instance_item)
                @if(($exam_instance_item->heading)==1)
                    <tr style="background-color: #7ab800" class='sortablerow'
                        entryid='{{$exam_instance_item->id}}'>
                        <td>
                            <a href="#" data-toggle="modal"
                               data-id="{{$exam_instance_item->id}}"

                               data-target="#edititemdialog">

                                <h4> {{$exam_instance_item->label}}</h4>
                            </a>
                        </td>
                        <td colspan="2">
                            {{$exam_instance_item->description}}
                        </td>
                        <td>
                            <ul>
                                {!! ((isset($exam_instance_item->show_if_id)&&($exam_instance_item->show_if_id>0))?'<li> Show if <strong>'.\App\Exam_instance_item::find($exam_instance_item->show_if_id)->label.'</strong> has answer <strong>'.\App\Exam_instance_item_item::find($exam_instance_item->show_if_answer_id)->label.'</strong></li>':'') !!}
                                {!!((isset($exam_instance_item->exclude_from_total)?'<li>Excluded from total/is formative</li>':'')) !!}
                            </ul>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-id="{{$exam_instance_item->id}}"
                               data-route="examitem"
                               data-target="#deleteitemdialog">
                                <i class="fa fa-times" style="font-size: 2em; color: red"
                                   aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>

                @else
                    <tr class='sortablerow' entryid='{{$exam_instance_item->id}}'>
                        <td><a href="#" data-toggle="modal" data-id="{{$exam_instance_item->id}}"
                               data-target="#edititemdialog">{{$exam_instance_item->label}}</a></td>
                        <td>{{$exam_instance_item->description}}</td>

                        <td>
                            <table class="table-condensed littlesortablerow">
                                <tr>
                                    <th>Label</th>
                                    <th>Description</th>
                                    <th>Value</th>
                                    <th></th>
                                </tr>
                                @foreach($exam_instance_item->items as $item)
                                    <tr>
                                        <td>{{$item->label}}</td>
                                        <td>{{$item->description}}</td>
                                        <td>{{$item->value}}</td>
                                        <td>{{(($item->needscomment=='true')?'Needs comment':'')}}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                        <td>
                            <ul>
                                {!! ((isset($exam_instance_item->show_if_answer_id)&&($exam_instance_item->show_if_answer_id>0))?'<li> Show if <strong>'.\App\Exam_instance_item::find($exam_instance_item->show_if_id)->label.'</strong> has answer <strong>'.\App\Exam_instance_item_item::find($exam_instance_item->show_if_answer_id)->label.'</strong></li>':'') !!}
                                {!!((isset($exam_instance_item->exclude_from_total)?'<li>Excluded from total/is formative</li>':'')) !!}
                                {!!((isset($exam_instance_item->no_comment)?'<li>Hide comments input</li>':'')) !!}
                            </ul>
                        </td>
                        <td><a href="#" data-toggle="modal" data-id="{{$exam_instance_item->id}}"
                               data-route="examitem"
                               data-target="#deleteitemdialog">
                                <i class="fa fa-times" style="font-size: 2em; color: red"
                                   aria-hidden="true"></i>
                            </a></td>
                    </tr>

                @endif

            @endforeach
            </tbody>
        </table>

        {!! $items->appends(Request::all())->render() !!}
    </fieldset>



    <div id="additemdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Item</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal'])!!}

                    @include('examinstance.templates.items.form.newquestion', ['submitButtonText'=>'Add Item'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="edititemdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Item</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'id'=>'edititemform'])!!}

                    @include('examinstance.templates.items.form.editquestion', ['submitButtonText'=>'Update Item'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


    {{--  Deleting a contact event  --}}
    <div id="deletedialog" class="modal fade" role="dialog">
        <div class="modal-dialog" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Really delete exam?</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('form_common.deletedialog')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


@stop

