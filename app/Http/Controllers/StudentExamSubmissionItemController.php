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
use App\Student_exam_submission_item_changelog;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


/**
 * Class StudentExamSubmissionItemController
 * @package App\Http\Controllers
 * Controller for the management of assessment items
 * API access only, no frontend at this point
 */
class StudentExamSubmissionItemController extends Controller
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
        // return the answer with the exam instance item
        $answer = Student_exam_submission_item::find($id);
        // dd($answer);
        return array(
            'item' => Exam_instance_item::with('items')->find($answer->exam_instance_items_id),
            'answer' => $answer);
    }

    public
    function update(Request $request)
    {
        if (Gate::denies('update_results')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        // dd($input);
        $input['last_updated_by'] = Auth::user()->id;
        // update the main bits
        $item = \App\Student_exam_submission_item::findOrNew($input['id']);
        //  dd($input);
        $item->update($input);
//        $response = array(
//            'status' => $item->update($input) ? '0' : '-1'
//        );


        $response = array(
            'status' => '0',
            'msg' => 'Item updated successfully',

        );
        return $response;
    }

    public function changelog($id)
    {

        $logs = Student_exam_submission_item_changelog::where('student_exam_submissions_items_id', '=', $id)->get();
        $returnArr = [];
        foreach ($logs as $log){
            $returnArr[]=[
                'reason'=>(isset($log->reason)?$log->reason:'(no reason given)'),
                'old_label'=>Exam_instance_item_item::find($log->old_selected_exam_instance_items_items_id)->label,
                'new_label'=>Exam_instance_item_item::find($log->selected_exam_instance_items_items_id)->label,
                'old_comments'=>(isset($log->old_comments)?$log->old_comments:''),
                'new_comments'=>(isset($log->comments)?$log->comments:''),
                'updated_by'=>User::find($log->updated_by)->name,
            'updated_at'=>$log->created_at->toDateTimeString()];
        }

        return $returnArr;
    }

}
