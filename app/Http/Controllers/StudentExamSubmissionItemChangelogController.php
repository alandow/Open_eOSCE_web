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
 * Class StudentExamSubmissionItemController
 * @package App\Http\Controllers
 * Controller for the management of assessment items
 * API access only, no frontend at this point
 */
class StudentExamSubmissionItemChangelogController extends Controller
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
            'item'=>Exam_instance_item::with('items')->find($answer->exam_instance_items_id),
                'answer'=> $answer);
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


}
