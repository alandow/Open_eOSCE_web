<?php

namespace App\Mail;

use App\Emails_template;
use App\Student_exam_submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentExamFeedback extends Mailable
{
    use Queueable, SerializesModels;

    protected $template;
    protected $submission;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Student_exam_submission $submission, Emails_template $template)
    {
        //dd($offering);
        $this->template = $template;
        $this->submission = $submission;
    }

    public function getfulltext()
    {
        // build a results table
        $resultsTable = "<table class=\"table table-striped table-condensed\"><thead>
                    <tr>
                        <th>Item</th>
                        <th>Result</th>
                        <th>Value</th>";
        if (json_decode($this->submission->exam_instance->email_parameters)->exclude_items_comments != '1') {

            $resultsTable .= "<th > Comment</th >";
        }
        $resultsTable .= "</tr>
                    </thead>
                    <tbody>";

        foreach ($this->submission->exam_instance->exam_instance_items as $exam_instance_item) {
            if (($exam_instance_item->heading) == 1) {

                $resultsTable .= "<tr style = \"background-color: #7ab800\" ><td colspan = \"4\" ><h5 >{$exam_instance_item->label}</h5 ></td ></tr >";

            } else {
                $resultsTable .= "<tr ><td >{$this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->item->label}";
            }
            if ($exam_instance_item->exclude_from_total == '1') {
                $resultsTable .= "(Formative)";
            }
            $resultsTable .= "</td><td>";
            if ($this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem) {
                $resultsTable .= $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem->label;
            } else {
                $resultsTable .= "(not shown)";
            }
            $resultsTable .= "</td><td>";
            if ($this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem) {
                $resultsTable .= $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem->value;
            } else {
                $resultsTable .= "(not shown)";
            }
            $resultsTable .= "</td>";
            if (json_decode($this->submission->exam_instance->email_parameters)->exclude_items_comments != '1') {
                $resultsTable .= "<td>{$this->submission->student_exam_submission_items->where('exam_instance_items_id',$exam_instance_item->id)->first()->comments}</td>";
            }
            $resultsTable .= "</td></tr>";
        }
        $resultsTable .= "</tbody></table>";

        return view('mailview.studentfeedbackemail')
                 ->with('template', $this->template)
                 ->with('submission', $this->submission)
                 ->with('resultsTable', $resultsTable);

    }

    public function getsubject()
    {
        return str_replace(["{name}", "{exam}"],
            [$this->submission->student->fname, $this->submission->exam_instance->name], $this->template->subject);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // build a results table
       $exam = $this->submission->exam_instance;
        // a sample results
        $resultsTable = '<table class="table table-sm"><tr><th>Item</th><th>Result</th>';
        if (!json_decode($exam->email_parameters)->exclude_items_comments == '1') {
            $resultsTable .= '<th>Comments</th>';
        }
        $resultsTable .= '</tr>';
        foreach ($exam->exam_instance_items as $exam_instance_item) {
            {

                if (!in_array($exam_instance_item->id, json_decode($exam->email_parameters)->exclude_items)) {
                    if (($exam_instance_item->heading) == 1) {
                        $resultsTable .= "<tr style=\"background-color: #7ab800\">
                                <td colspan=\"4\" >
                                    <h5> {$exam_instance_item->label}</h5>
                                </td>

                            </tr>";
                    } else {
                        $resultsTable .= "<tr><td>" . $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->item->label;
                        if ($exam_instance_item->exclude_from_total == '1') {
                            $resultsTable .= "(Formative)";
                        }
                        $resultsTable .= "</td><td>";

                        if ($this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem) {
                            $resultsTable .= $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem->label;
                        } else {
                            $resultsTable .= "(not assessed)";
                        }
                        $resultsTable .= "</td>";
                    }

                    if (!json_decode($exam->email_parameters)->exclude_items_comments == '1') {
                        $resultsTable .= '<td>' . $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->comments . '</td>';
                    }
                    $resultsTable .= "</tr>";
                }
            }
        }

        $resultsTable .= "</table>";
        if (!json_decode($exam->email_parameters)->exclude_overall_comments == '1') {
            $resultsTable .= '<p><strong>Overall comments:</strong><p>' . $this->submission->comments. '</p>';
        }


//
//        $resultsTable = "<table class=\"table table-striped table-condensed\"><thead>
//                    <tr>
//                        <th>Item</th>
//                        <th>Result</th>
//                        <th>Value</th>";
//        if (json_decode($this->submission->exam_instance->email_parameters)->exclude_items_comments != '1') {
//
//            $resultsTable .= "<th > Comment</th >";
//        }
//        $resultsTable .= "</tr>
//                    </thead>
//                    <tbody>";
//
//        foreach ($this->submission->exam_instance->exam_instance_items as $exam_instance_item) {
//            if (($exam_instance_item->heading) == 1) {
//
//                $resultsTable .= "<tr style = \"background-color: #7ab800\" ><td colspan = \"4\" ><h5 >{$exam_instance_item->label}</h5 ></td ></tr >";
//
//            } else {
//                $resultsTable .= "<tr ><td >{$this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->item->label}";
//            }
//            if ($exam_instance_item->exclude_from_total == '1') {
//                $resultsTable .= "(Formative)";
//            }
//            $resultsTable .= "</td><td>";
//            if ($this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem) {
//                $resultsTable .= $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem->label;
//            } else {
//                $resultsTable .= "(not shown)";
//            }
//            $resultsTable .= "</td><td>";
//            if ($this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem) {
//                $resultsTable .= $this->submission->student_exam_submission_items->where('exam_instance_items_id', $exam_instance_item->id)->first()->selecteditem->value;
//            } else {
//                $resultsTable .= "(not shown)";
//            }
//            $resultsTable .= "</td>";
//            if (json_decode($this->submission->exam_instance->email_parameters)->exclude_items_comments != '1') {
//                $resultsTable .= "<td>{$this->submission->student_exam_submission_items->where('exam_instance_items_id',$exam_instance_item->id)->first()->comments}</td>";
//            }
//            $resultsTable .= "</td></tr>";
//        }
//        $resultsTable .= "</tbody></table>";

//dd($this->offering);
        return $this->from('noreply@openeosce.org')->view('mailview.studentfeedbackemail')
        ->with('template', $this->template)
        ->with('submission', $this->submission)
        ->with('resultsTable', $resultsTable)
        ->subject(str_replace(["{name}", "{exam}"],
            [$this->submission->student->fname, $this->submission->exam_instance->name ], $this->template->subject));
//str_replace("{coordinator}", explode(' ',$this->unit_instance->coordinator['name'])[0], str_replace("{unit}", $this->unit_instance->unit['unit_code'], $this->template->subject)));
    }
}
