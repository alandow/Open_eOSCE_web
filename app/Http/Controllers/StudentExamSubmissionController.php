<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\Exam_instance_item;
use App\Exam_instance_item_item;
use App\Group;
use App\Http\Requests;
use App\Student;
use App\Student_exam_submission;
use App\Student_exam_submission_item;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


/**
 * Class StudentExamSubmissionController
 * @package App\Http\Controllers
 * Controller for the submission of examinations
 * API access only, no frontend at this point
 */
class StudentExamSubmissionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        //@TODO filter by archived and template
        return null;
    }

    public function show($id)
    {

        return null;

    }

    public
    function update(Request $request)
    {
return null;
    }

/////////////////////////////////////////////////////////////////////////////////////
///
/// Submission is good
///
/// ////////////////////////////////////////////////////////////////////////////////
///

    /**
     * Accept the submission of an assessment
     * @param Request $request
     * @return array
     */
    public
    function submitAssessment(Request $request)
    {
        $input = $request::all();
        //dd($input);
        //@TODO check that this hasn't been already submitted
        $student_id =$input['student_id'];
        $exam_instance_id =$input['exam_instances_id'];
        $comments = (isset($input['comments'])?$input['comments']:'');
        $answerdata = $input['answerdata'];
        //   dd($submitdata);
      
        $submission = Student_exam_submission::create(['student_id' => $student_id, 'exam_instances_id' => $exam_instance_id, 'comments' => $comments,
            'created_by' => (isset($input['real_author_id'])?$input['real_author_id']:Auth::user()->id)]);

        $answers = [];
        foreach ($answerdata as $answer) {
            if (strval(Exam_instance_item::find($answer['id'])->heading) != '1') {
                $answers[] = new Student_exam_submission_item(['exam_instance_items_id' => $answer['id'], 'selected_exam_instance_items_items_id' => (isset($answer['selected_id'])?$answer['selected_id']:null), 'comments' => ((isset($answer['comment']) ? $answer['comment']: ''))]);
            }
        }
        $submission->student_exam_submission_items()->saveMany($answers);

        $response = array(
            'status' => 0,
        );
        return $response;
    }


}
