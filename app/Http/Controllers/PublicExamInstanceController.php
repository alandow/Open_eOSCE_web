<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\Http\Requests;
use App\Student;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;


/**
 * Class ExamInstanceController
 * @package App\Http\Controllers
 * Controller for the examination instances (managing exams)
 */
class PublicExamInstanceController extends Controller
{

    public function __construct()
    {

    }

    public function index(Request $request)
    {

    }

//    public function reportview(Request $request)
//    {
//        $activeexamlist = Exam_instance::where('archived', '<>', 'true')
//            ->where('finalised', 'true')
//        ->where('active', 'true');
//
//        $activeexams = $activeexamlist->sortable();
//
//        $completedexamlist = Exam_instance::where('archived', '<>', 'true')
//            ->where('finalised', 'true')
//            ->where('active',  '<>','true')
//            ->where('exam_endtimestamp',  '>', 0);
//
//        $completedexams = $completedexamlist->sortable()->paginate(10);
//
//        //dd($exams);
//        return view("reports.list")
//           ->with('activeexams', $activeexams)
//            ->with('completedexams', $completedexams);
//    }

    public function show($id)
    {
        $exam = Exam_instance::findOrFail($id);
//        $users = User::all();
//        $students = Student::all();
        // dd($student->examinations_sessions[0]->responses);
        if ($exam->exists) {
            return view("examinstance.preview")
                ->with('exam', $exam);

        } else {
            return redirect('home');
        }


    }

    // get the exam definition as JSON
    public function getexamdefinition($id)
    {
        try {
            return Exam_instance::with('exam_instance_items.items')->where('id', $id)->get();
        } catch (ModelNotFoundException $e) {
            return array(
                'status' => -1,
            );
        }

    }

    public function ajaxstore(Request $request)
    {
        if (Gate::denies('update_exam')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();

        // find out if there's an existing exam with that name
        $item = \App\Exam_instance::where('name', $input['name'])->first();
        if (isset($item)) {
            return array(
                'status' => '-1',
                'statusText' => 'a examination with that name already exists',
            );
        } else {
            $newitem = new Exam_instance($input);
            return array(
                'status' => $status = strval($newitem->save()),
            );
        }
    }

    /**
     * @param Requests\UserRequest $request
     * @return array
     */
    public function ajaxupdate(MediaRequest $request)
    {

        return array(
            'status' => -1,
        );

    }

    public
    function update(Request $request)
    {
        $input = $request::all();

        $instance = \App\Exam_instance::findOrNew($input['id']);
        $status = strval($instance->update($input));
        $response = array(
            'status' => $status,
        );
        return $response;
        return array(
            'status' => $status,
        );
    }

    public function ajaxdestroy(\Illuminate\Http\Request $request)
    {
        //   dd($request);

        return 0;


    }

}
