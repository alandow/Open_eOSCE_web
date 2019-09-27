<!DOCTYPE html>

@section('title')
    {{$exam->name}}
@endsection

<html lang="en">
<head>
    @include('layouts.includes')
    {{--Include angular.js--}}

    <style>
        label.btn span {
            font-size: 1.5em;
        }

        label input[type="radio"] ~ i.fa.fa-circle-o {
            color: #c8c8c8;
            display: inline;
        }

        label input[type="radio"] ~ i.fa.fa-dot-circle-o {
            display: none;
        }

        label input[type="radio"]:checked ~ i.fa.fa-circle-o {
            display: none;
        }

        label input[type="radio"]:checked ~ i.fa.fa-dot-circle-o {
            color: #7AA3CC;
            display: inline;
        }

        label:hover input[type="radio"] ~ i.fa {
            color: #7AA3CC;
        }

        label input[type="checkbox"] ~ i.fa.fa-square-o {
            color: #c8c8c8;
            display: inline;
        }

        label input[type="checkbox"] ~ i.fa.fa-check-square-o {
            display: none;
        }

        label input[type="checkbox"]:checked ~ i.fa.fa-square-o {
            display: none;
        }

        label input[type="checkbox"]:checked ~ i.fa.fa-check-square-o {
            color: #7AA3CC;
            display: inline;
        }

        label:hover input[type="checkbox"] ~ i.fa {
            color: #7AA3CC;
        }

        div[data-toggle="buttons"] label.active {
            color: #7AA3CC;
        }

        div[data-toggle="buttons"] label {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: normal;
            line-height: 2em;
            text-align: left;
            white-space: nowrap;
            vertical-align: top;
            cursor: pointer;
            border: 0px solid #c8c8c8;
            border-radius: 3px;
            color: #c8c8c8;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select: none;
            user-select: none;
        }

        div[data-toggle="buttons"] label:hover {
            color: #7AA3CC;
        }

        div[data-toggle="buttons"] label:active, div[data-toggle="buttons"] label.active {
            -webkit-box-shadow: none;
            box-shadow: none;
        }

        .item-heading {
            background-color: #7AB800;
            height: 50px;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .heading-label {
            font-size: 2em;
            color: #ffffff;
        }

        .item-text {
            background-color: #f0f0f0;
            border-bottom: 1px solid #7da8c3;
            height: 40px;
        }

        .item-text-text {
            font-size: 1.5em;
        }

        .comments-label {
            color: #7AA3CC;
            font-size: 1.5em;
        }

        #titlecontainer {
            font-size: 2em;
            color: #ffffff;
        }

        table td.shrink {
            width: 1px;
            white-space: nowrap
        }

        .btn-huge {
            padding-top: 20px;
            padding-bottom: 20px;
        }
    </style>
    <script>

        // exam object
        examdata = {};

        $(document).ready(function () {
// load exam by AJAX
            // @TODO explore http://api.jquery.com/jQuery.getJSON/
            $.ajax({
                url: '{!! URL::to('')!!}/api/exam/{!! $exam->id !!}/getexamdefinition',
                type: 'post',
                error: function (jqXHR, textStatus, errorThrown) {
                    waitingDialog.hide();
                    alert(errorThrown);
                },
                success: function (data) {
                    examdata = data[0];
                    console.log(examdata);
                    $("#examtitle").append(examdata.name);
                    for (var i = 0; i < examdata.exam_instance_items.length; i++) {
                        addItem(examdata.exam_instance_items[i])
                    }
                    validateform();
                }


            });

        })
        ;

        function addItem(data) {
//                console.log(data);
            var component = "";

            if ((typeof data.heading == 'string') && (data.heading == "1")) {
                component += "<tr style='background-color: #7ab800; border-bottom: 5px solid white' data-id='" + data.id + " '>" +
                        "<td class='shrink'><i style='font-size: 2em; color: #2a88bd' class='fa fa-info-circle' aria-hidden='true'></i></td>" +
                        "<td colspan='2'><h4>" + data.label + "</h4></td>" +
                        "</tr>";


            } else {
                component += "<tr style='background-color: #e0e0e0' data-id='" + data.id + "'>" +
                        "<td  class='shrink'><i style='font-size: 2em; color: #2a88bd' class='fa fa-info-circle' aria-hidden='true'></i></td>" +
                        "<td colspan='2' style='font-size: 1.2em'>" + data.label + "</td>" +
                        "</tr>";

                // questions
                component += "<tr data-id='" + data.id + "'><td class='shrink'></td><td> <div class='btn-group btn-group-vertical col-md-12' data-toggle='buttons'>";
                for (var i = 0; i < data.items.length; i++) {
                    component += " <label class='btn active'>" +
                            "<input type='radio' data-id='" + data.items[i].id + "' name='radio_" + data.id + "' value='" + data.items[i].value + "' onchange='validateform()'>" +
                            "<i class='fa fa-circle-o fa-2x'></i><i class='fa fa-dot-circle-o fa-2x'></i><span>" + data.items[i].description + "</span></label>";

                }
                component += "</div></td>";
                if (!((typeof data.no_comment == 'string') && (data.no_comment == "1"))) {

                    component += "<td>" +

                            "<span class='comments-label col-md-12'>Comments</span>" +

                            "<textarea id='comment_'" + data.id + " style='width: 100%' onchange='validateform()'></textarea>";
                }
                component += "</td>" +
                        "</tr>";


            }
            $("#itemsstablebody").append(component);
        }

        function validateform() {
            console.log('validating form');
            valid = false;
            $("#submitbtn").prop('disabled', true);
            for (var i = 0; i < examdata.exam_instance_items.length; i++) {
                // does this item have a value?
                console.log($('input[name=radio_' + i + ']:radio:checked').val())
                // should we show this item?
                if ((examdata.exam_instance_items[i].show_if_id !== null) && (examdata.exam_instance_items[i].show_if_id > 0)) {
                    if ($('input[name=radio_' + examdata.exam_instance_items[i].show_if_id + '][type=radio][data-id*=' + examdata.exam_instance_items[i].show_if_answer_id + ']').prop('checked')) {
                        console.log('Showing something!');
                        $("#questionstbl tbody tr[data-id*='" + examdata.exam_instance_items[i].id + "']").show(100);
                    } else {
                        console.log('hiding something!');
                        $("#questionstbl tbody tr[data-id*='" + examdata.exam_instance_items[i].id + "']").hide(100);
                    }
                }

            }
        }

        function changeLayout(width, height) {
            $("#layout").css('width', width);
            $("#layout").css('height', height);
        }
        // add a
    </script>

</head>
<div id="wrapper">
    <div id="layout" style="max-width: 1024px; height: 768px; border: 2px solid black; float: none;
     margin-left: auto;
     margin-right: auto;">
        <div style="width: 100%; margin-left: auto; margin-right: auto; margin-bottom: 0">
            <nav class="navbar navbar-inverse " style="margin-left: auto; margin-right: auto; border-radius: 0">
                <div style="font-size: 2em; color: #f0f0f0; padding-left: 10px">Exam name: <span id="examtitle"></span>
                </div>
            </nav>
        </div>
        <div class="container-fluid " style="height: 100%;">
            {{--@yield('content')--}}


            <table class="table " id="questionstbl">
                <thead class="thead-inverse">
                </thead>
                <tbody id="itemsstablebody">

                </tbody>
            </table>
            <div style="width: 100%; border-bottom: 1px solid grey">&nbsp;</div>
            <div class="col-md-12">
                <span style="font-size: 2em">Overall comments</span>
            </div>
            <div class="col-md-12">
                <textarea style="width: 100%"></textarea>
            </div>
            <div class="col-md-12" style="padding: 0">
                <button id="submitbtn" class="btn btn-warning btn-lg btn-block btn-huge"
                        style="width: 100%; margin-left: 0; margin-right: 0; margin-top: 20px">Submit
                </button>
            </div>

        </div>
    </div>
    <div class="side-nav" style="top:0; padding-left: 15px; color: #f0f0f0;">
        <span style=" font-size: 2em">Layout</span>
        <p/>
        <p/>
        <button class="btn btn-default" onclick="changeLayout(1024, 768)"><i class="fa fa-tablet  fa-rotate-90"
                                                                             aria-hidden="true"
                                                                             style="font-size: 3em"></i>&nbsp; iPad
            landscape
        </button>
        <p/>
        <button class="btn btn-default" onclick="changeLayout(768, 1024)"><i class="fa fa-tablet  " aria-hidden="true"
                                                                             style="font-size: 3em"></i> iPad portrait
        </button>

        <p/>

    </div>
</div>
</html>