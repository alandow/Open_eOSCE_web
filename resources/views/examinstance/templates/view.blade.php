@extends('layouts.nomenu')

@section('title')
    {{$exam->name}}
@endsection

@section('content')
    {{--Some extra libraries for inline editing--}}
    <link rel="stylesheet" href="{{URL::asset('resources/assets/css/bootstrap-editable.css')}}">
    <script src="{{ URL::asset('resources/assets/js/bootstrap-editable.min.js') }}"></script>
    <style>
        /*.modal-body {*/
        /*max-height: calc(100vh - 210px);*/
        /*overflow-y: auto;*/
        /*}*/

        body.dragging, body.dragging * {
            cursor: move !important;
        }

        .dragged {
            position: absolute;
            opacity: 0.5;
            z-index: 2000;
        }

        #questionstbl tr.placeholder:before {
            position: absolute;
            color: red;
            content: '\279e';
            /** Define arrowhead **/
        }
    </style>
    <script>

        $.fn.editable.defaults.mode = 'inline';

        var currenteditingid = 0;

        $(document).ready(function () {

            $('select').select2();

            $('.datepicker').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
            });

            if (location.hash.substr(0, 2) == "#!") {
                $("a[href='#" + location.hash.substr(2) + "']").tab("show");
            }

            $("a[data-toggle='tab']").on("shown.bs.tab", function (e) {
                var hash = $(e.target).attr("href");
                if (hash.substr(0, 1) == "#") {
                    location.replace("#!" + hash.substr(1));
                }
            });

            $('a[data-toggle=modal], button[data-toggle=modal]').click(function () {
                currenteditingid = $(this).data('id');
                switch ($(this).data('target')) {
                    case "#editoverviewdialog":
                        currenteditingid = '{{$exam->id}}';
                        console.log(currenteditingid)
                        break;
                    case '#additemdialog':
                        console.log($(this).data('id'));
                        if ($(this).data('id') > -1) {
                            getTemplateItemDetails($(this).data('id'));
                        }
                        break;
                    case '#edititemdialog':
                        currenteditingid = $(this).data('id');
                        console.log(currenteditingid)
                        getItemDetails(currenteditingid);
                        break;
                    case '#editnoteitemdialog':
                        currenteditingid = $(this).data('id');
                        console.log(currenteditingid)
                        getDetails(currenteditingid, 'reviewnotesitem', 'editnoteitemdialog');
                        break;
                    case '#editmediadialog':
                        currenteditingid = $(this).data('id');
                        console.log(currenteditingid)
                        getDetails(currenteditingid, 'reviewmedia', 'editmediadialog');
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
                vars.push({name: 'exam_instance_id', value: '{{$exam->id}}'});

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

            $('#deleteitemdialog').submit(function (event) {
                $('#deleteitemdialog').modal('hide');
                var vars = $("#deleteitemdialog").find("form").serializeArray();
                vars.push({name: 'id', value: currentdeletingid});
                //vars.push({name: '_method', value: 'DELETE'});
                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                deleteItem(vars);
            });

            $('.editdialog').submit(function (event) {
                // cancels the form submission
                event.preventDefault();
                $(this).modal('hide');
                //  var vars = $("#newmediadialog").find("form").serializeArray();
                var vars = $(this).find("form").serializeArray();
                vars.push({name: 'id', value: currenteditingid});
                vars.push({name: '_method', value: 'PATCH'});

                waitingDialog.show();
                submitUpdateForm(vars, $(this).data('route'));
            });


            // intercept new condition event dialogue form submission
            $('#newmediadialog').find("form").submit(function (event) {
                $('#newmediadialog').modal('hide');
                if ($('#upload_file')[0].files.length > 0) {
                    //  var vars = $("#newmediadialog").find("form").serializeArray();
                    var data = new FormData();
                    // file

                    jQuery.each($('#upload_file')[0].files, function (i, file) {
                        data.append('userfile', file);
                    });
                    data.append('review_id', '{{$exam->id}}');
                    // data.append('description', $descriptionctrl.val());
                    var $descriptionctrl = $('[name=description]', $("#newmediadialog").find("form"));
                    //console.log($descriptionctrl.val());
                    data.append('description', $descriptionctrl.val());
                    data.append('_token', '{{ csrf_token() }}');
                    // console.log(data);
                    // cancels the form submission
                    event.preventDefault();
                    //   waitingDialog.show();
                    submitNewMediaForm(data);
                } else {
                    alert('Need to pick a file')
                }
            });

            // intercept edit media event dialogue form submission
            $('#editmediadialog').submit(function (event) {
                $('#editmediadialog').modal('hide');
                //  var vars = $("#newmediadialog").find("form").serializeArray();
                console.log('editmediadialog submission');
                var data = new FormData();
                // file
                jQuery.each($('#altupload_file')[0].files, function (i, file) {
                    data.append('userfile', file);
                });
                var $descriptionctrl = $('[name=description]', $("#editmediadialog").find("form"));

                data.append('description', $descriptionctrl.val());
                data.append('id', currenteditingid);
                data.append('_method', 'PATCH');
                data.append('_token', '{{ csrf_token() }}');

                // cancels the form submission
                event.preventDefault();
                waitingDialog.show();
                submitEditMediaForm(data);
            });


            $('.modalform').on('shown.bs.modal', function () {
                $(this).find("form").validator();
            });


            // drag and drop reordering
            dragula([document.getElementById('itemsstablebody')]).on('drop', function (el, target, source, sibling) {
                // send an array of elements to backend to reorder
                //    waitingDialog.show();
                // console.log(sibling);

                var orderArray = ([]);
                var orderint = 0;
                $(".sortablerow").each(function () {
                    orderArray.push({id: $(this).attr('entryid'), order: orderint})
                    orderint++;
                    //    console.log($(this).attr('entryid'));
                });
                // for some reason, the last element of this is always the element being moved.
                orderArray.pop();

                var dataObj = ([{name: '_token', value: '{{csrf_token()}}'}, {
                    name: 'order',
                    value: JSON.stringify(orderArray)
                }]);

                $.ajax({
                    url: '{!! URL::to('')!!}/examitem/reorder',
                    type: 'post',
                    data: dataObj,
                    error: function (jqXHR, textStatus, errorThrown) {
                        waitingDialog.hide();
                        alert(errorThrown);
                    },
                    success: function (data) {
                        //   waitingDialog.hide();
                        if (data.status.toString() == "0") {
                            location.reload(true);
                        } else {
                            waitingDialog.hide();
                            alert('something went wrong with the update');
                        }
                    }
                });

            });


        });


        function submitNewItemForm(vars, route) {
            // console.log(route);
            $.ajax({
                url: '{!! URL::to('')!!}/examitem/store',
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

        function deleteItem(vars) {
            $.ajax({
                url: '{{URL::to('examitem/ajaxdestroy')}}',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    waitingDialog.hide();
                    if (data == "1") {
                        location.reload(true);
                    } else {
                        alert('something went wrong with the delete');
                    }
                }
            });
        }

        function submitUpdateForm(vars, route) {
            // console.log(route);
            $.ajax({
                url: '{!! URL::to('')!!}/' + route + '/update',
                type: 'post',
                data: vars,
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    //   waitingDialog.hide();
                    if (data.status.toString() == "1") {
                        location.reload(true);
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
        /*
         *
         * Helper functions
         *
         */

        // gets items items for logic
        function getItemItems(target, itemid, selected) {
            $.ajax({
                url: '{!! URL::to('')!!}/examitem/' + itemid + '/getitemitemsasarray',
                type: 'post',
                data: [{name: '_token', value: '{{csrf_token()}}'}],
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    //   waitingDialog.hide();
                    $("#" + target).select2({
                        data: data
                    })
                    if (typeof selected !== 'undefined') {
                        $("#" + target).val(selected).trigger("change");
                    }
                }
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
                                        } else {
                                            $(this).prop("checked", false);
                                        }
                                    });
                                    break;

                                default:
                                    $ctrl.val(value);
                            }
                        }

                        if ($("#edit_no_comment").prop('checked')) {
                            $(".updateinlineeditablecomment").hide();
                            $(".updateinlineeditablecommentlabel").hide();
                        } else {
                            $(".updateinlineeditablecomment").show();
                            $(".updateinlineeditablecommentlabel").show();
                        }

                    });
                }
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
                    console.log($(data));
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
                                        } else {
                                            $(this).prop("checked", false);
                                        }
                                    });
                                    break;

                                default:
                                    $ctrl.val(value);
                            }
                        }

                        if ($("#edit_no_comment").prop('checked')) {
                            $(".updateinlineeditablecomment").hide();
                            $(".updateinlineeditablecommentlabel").hide();
                        } else {
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


        function getTemplateItemDetails(id) {
            $("#itemitemstbl tbody tr").remove();
            console.log('getTemplateItemDetails(' + id + ")");
            $.ajax({
                url: '{!! URL::to('')!!}/examitem/' + id,
                type: 'GET',
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(errorThrown);
                },
                success: function (data) {
                        console.log('data is:');
                 //   console.log($(data));
                    // populate update form
                    // bit of hacky display control

                    // standard fields
                    $.each(data, function (key, value) {
                        $('#additemtitle').html('New '+$(data)[0].label);
                        var $ctrl = $('#itemform [name=' + key + ']');

                        // if we find the criteria items
                        if (key == 'items') {
                            for (var i = 0; i < value.length; i++) {

                                // make the table of criteria items
//
                                $("#itemitemstbl tbody").append('<tr class="itemitemrow">' +
                                        '<td><a href="#" class="inlineeditablelabel" data-name="label" data-type="text" data-pk="' + value[i].id + '" data-url="" data-title="Label">' + value[i].label + '</a></td>' +
                                        '<td><a href="#" class="inlineeditabledescription" data-name="description" data-type="text" data-pk="' + value[i].id + '" data-url="" data-title="Description">' + value[i].description + '</a></td>' +
                                        '<td><a href="#" class="inlineeditablevalue" data-name="value" data-type="number" data-pk="' + value[i].id + '" data-url="" data-title="value">' + value[i].value + '</a></td>' +
                                        '<td><a href="#" class="inlineeditablecomment" data-name="needscomment" data-type="select" data-pk="' + value[i].id + '" data-url="" data-title="comment">' + (value[i].hascomment == 'true' ? 'Yes' : 'No') + '</a></td>' +
                                        '<td><a href="#" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-times" style="font-size: 2em; color: red"aria-hidden="true"></i></a></td>' +
                                        '</tr> ');

                            }
                        } else {

                            switch ($ctrl.attr("type")) {
//                                    case "text" :
//                                        $ctrl.val(value);
                                case "checkbox":
                                    $ctrl.each(function () {
                                        console.log($(this).attr('value'));
                                        if ($(this).attr('value') == value) {
                                            $(this).prop("checked", true);
                                        } else {
                                            $(this).prop("checked", false);
                                        }
                                    });
                                    break;

                                default:
                                    $ctrl.val(value);
                            }
                        }

                        if ($("#no_comment").prop('checked')) {
                            $(".inlineeditablecomment").hide();
                            $(".inlineeditablecommentlabel").hide();
                        } else {
                            $(".inlineeditablecomment").show();
                            $(".inlineeditablecommentlabel").show();
                        }

                    });

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

//  advanced stuff

                    if ($(data)[0].show_if_id !== null) {
                        $('#show_if_id').val($(data)[0].show_if_id).trigger("change");
                        getItemItems('show_if_answer_id', $(data)[0].if_id, $(data)[0].if_answer_id);
                    }

                    // finally hide stuff if it's a heading
                    if ($("#heading_cb").prop("checked") == true) {
                        $(".template_text").hide()
                    } else {
                        $(".template_text").show()
                    }
                    //populate($("#" + formid).find("form"), data);
                    waitingDialog.hide();
                }
            });
        }

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
                                } else {
                                    $(this).removeAttr("checked");
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

    <!-- Tabs -->
    {!! Breadcrumbs::render('examtemplates.show', $exam) !!}
    <div style="padding-left: 15px; padding-right: 15px; margin-top: 0">
        <ul class="nav nav-tabs" id="tabslabels">
            <li class="active"><a data-toggle="tab" href='#detailstab'>Template Overview</a></li>
            <li><a data-toggle="tab" href='#itemstab'>Assessment Items</a></li>

        </ul>
        <div class="tab-content">
            <div id="detailstab" class="tab-pane active">
                <fieldset style="width: 90%">
                    <legend>Template Overview
                        <button id="edit_patient_but" class="btn btn-info btn-lg" data-toggle="modal"
                                data-target="#editoverviewdialog">Edit
                        </button>
                    </legend>
                    <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9; padding-top: 20px">
                        <div class="col-md-4">
                            <strong>Template name:</strong>
                        </div>
                        <div class="col-md-8">
                            {{$exam->name}}
                        </div>

                    </div>
                    <div class="col-md-12" style="border-bottom: 1px solid #d9d9d9;padding-top: 20px ">
                        <div class="col-md-4">
                            <strong>Description:</strong>
                        </div>
                        <div class="col-md-8">
                            {{$exam->description}}
                        </div>
                    </div>


                </fieldset>
            </div>
            <div id="itemstab" class="tab-pane">
                <fieldset style="width: 90%">
                    <legend>Assessment Items<br/>

                        <span style="font-size: 0.5em;">
                             <div class="dropdown">
                                 <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                     Add new...
                                     <span class="caret"></span></button>
                                 <ul class="dropdown-menu">
                                     <li><a href="#" data-toggle="modal"
                                            data-id="-1"
                                            data-target="#additemdialog">Blank item</a></li>
                                     @foreach($itemtemplates as $itemtemplate)
                                         <li><a href="#" data-toggle="modal"
                                                data-id="{{$itemtemplate->id}}"
                                                data-target="#additemdialog">{{$itemtemplate->label}}</a></li>
                                     @endforeach

                                 </ul>
                             </div>

                            </span>
                    </legend>
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
                        @foreach($exam->exam_instance_items as $exam_instance_item)
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

                </fieldset>
            </div>


        </div>
    </div>

    <div id="editoverviewdialog" class="modal fade editdialog" role="dialog" data-route="exam">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Examination Details</h4>
                </div>
                <div class="modal-body">
                    {!! Form::model($exam, ['class'=>'form-horizontal',  'data-toggle'=>'validator'])!!}

                    @include('examinstance.templates.form.updateform', ['submitButtonText'=>'Update Overview'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="additemdialog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="additemtitle">Add Item</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open( ['class'=>'form-horizontal', 'id'=>'itemform'])!!}

                    @include('examinstance.templates.form.newquestion', ['submitButtonText'=>'Add Item'])

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

                    @include('examinstance.templates.form.editquestion', ['submitButtonText'=>'Update Item'])

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div id="deleteitemdialog" class="modal fade" role="dialog">
        <div class="modal-dialog" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Really delete item?</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open()!!}
                    @include('form_common.deletedialog')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    {{--Placeholder--}}
    <div id="placeholderdialog" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title">Placeholder</h4>
                </div>
                <div class="modal-body">
                    Placeholder dialog
                </div>
            </div>
        </div>
    </div>






@stop


