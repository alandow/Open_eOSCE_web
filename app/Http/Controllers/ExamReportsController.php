<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\Exam_instance_item;
use App\Exam_instance_item_item;
use App\Group;
use App\Http\Requests;
use App\Student;
use App\Student_exam_submission;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


/**
 * Class ExamInstanceController
 * @package App\Http\Controllers
 * Controller for the examination instances (managing exams)
 */
class ExamReportsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        //@TODO filter by archived and template
        $activeexams = Exam_instance::where('status', '=', 'active')
            ->whereNull('archived_at')
            ->sortable()->paginate(20);
        $completedexams = Exam_instance::where('status', '=', 'complete')
            ->whereNull('archived_at')
            ->sortable()->paginate(20);

        return view("reports.list")
            ->with('activeexams', $activeexams)
            ->with('completedexams', $completedexams);
    }


    public function show($id)
    {
        $exam = Exam_instance::findOrFail($id);
        $users = User::all();
        $students = Student::all();
        $groups = Group::all();
        // get max score here
        $maxscore = 0;
        foreach ($exam->exam_instance_items()->scorable()->get() as $item) {
            $maxscore += $item->items->max('value');

        }
        // dd($student->examinations_sessions[0]->responses);
        if ($exam->exists) {
            return view("reports.view")
                ->with('exam', $exam)
                ->with('users', $users)
                ->with('groups', $groups)
                ->with('maxscore', $maxscore)
                ->with('students', $students);
        } else {
            return redirect('home');
        }


    }

    public function detail($sessionid)
    {
        // if (Gate::denies('view_exam')) {
        //  return array(
        //     'status' => -2,
        // );
        // }
        //  try {
        $exam = Student_exam_submission::findOrFail($sessionid);
        $definition = Exam_instance::findOrFail($exam->exam_instances_id);
        // dd($definition);
// get total
        $score = 0;
        $maxscore = 0;
        foreach ($exam->student_exam_submission_items as $item) {
                if ($item->item->exclude_from_total != 1) {
                    if($item->selecteditem) {
//                        print_r($item->selecteditem);
//                        print ('<br/><hr/>');
                        $score += $item->selecteditem->value;
                        $maxscore += $item->item->items->max('value');
                    }
                }

        }

        return view("reports.sessionview")
            ->with('exam', $exam)
            ->with('definition', $definition)
            ->with('score', $score)
            ->with('maxscore', $maxscore);
//        } catch (ModelNotFoundException $e) {
//            return array(
//                'status' => -1,
//            );
        //  }


    }

    public function ajaxstore(Request $request)
    {
        if (Gate::denies('update_exam')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();

        // find out if there's an existing exam with that name
        $instance = \App\Exam_instance::where('name', $input['name'])->first();
        if (isset($item)) {
            return array(
                'status' => '-1',
                'statusText' => 'a examination with that name already exists',
            );
        } else {
            $newinstance = Exam_instance::create($input);
            if ($input['template_id'] > -1) {
                $templateitems = Exam_instance::find($input['template_id'])->exam_instance_items;

//            $template = Exam_instance::with(['exam_instance_items', 'exam_instance_items.items'])->where('id', $input['template_id'])->first();
//            $newinstance = $template->replicate();


                // here is where it gets tricky. We have to work out how to transfer the dependencies

                // first, build a new exam without the dependencies

                foreach ($templateitems as $item) {
                    $newitem = $item->replicate();
                    $newitem->exam_instance_id = $newinstance->id;
                    $newitem->save();
                    foreach ($item->items as $itemitem) {
                        $newitemitem = $itemitem->replicate();
                        $newitemitem->exam_instance_items_id = $newitem->id;
                        $newitemitem->save();
                    }
                    // then, work out the dependencies from the template. They will have the same order and name, but different ids
//                if ($item->show_if_id > 0) {
//                    $oldshowifid = $item->show_if_id;
//                    $oldifanswerid = $item->show_if_answer_id;
//                    $newitem->show_if_id = Exam_instance_item::where('order', '=', Exam_instance_item::find($oldshowifid)->order)->andwhere('exam_instance_id', '=', $newinstance->id)->first()->id;
                    //}
                }

// then, work out the dependencies from the template. They will have the same order and name, but different ids
                foreach ($templateitems as $item) {
                    if ($item->show_if_id > 0) {
                        // the new item that is the same as the templateitem
                        $currentitem = Exam_instance_item::where('order', '=', $item->order)->where('exam_instance_id', '=', $newinstance->id)->first();
                        // find the show if id
                        $oldshowifid = $item->show_if_id;
                        $oldifanswerid = $item->show_if_answer_id;
                        $currentitem->show_if_id = Exam_instance_item::where('order', '=', Exam_instance_item::find($oldshowifid)->order)->where('exam_instance_id', '=', $newinstance->id)->first()->id;
                        $currentitem->show_if_answer_id = Exam_instance_item_item::where('order', '=', Exam_instance_item_item::find($oldifanswerid)->order)->where('exam_instance_items_id', '=', $currentitem->show_if_id)->first()->id;
                        $currentitem->save();

                    }
                }

            }
            return array(
                'status' => $newinstance->id
            );
        }
    }


    public
    function ajaxupdate(Request $request)
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

///////////////////////////////////////////////////////////////
// template functions
///////////////////////////////////////////////////////////////
    public
    function templateindex(Request $request = null)
    {

        // if (Auth::user()->can('update_templates')) {
        $users = \App\User::all();

        $templateslist = \App\Exam_instance::where('is_template', '=', 'true');
        $templates = $templateslist->sortable()->paginate(20);

        //    dd($templates);
        //dd($reviews);
        return view('examinstance.templates.list')
            ->with('exams', $templates)
            ->with('users', $users);
//        } else {
//            abort(500, 'You don\'t have permission to do that');
//        }
    }

    public
    function templateshow($id)
    {
        //$this->authorize('view', Review_instances::class);

        $instance = \App\Exam_instance::findOrFail($id);
        $users = \App\User::all();
        $itemtemplates = \App\Exam_instance_item::where('is_template', '=', 'true')->get();
        return view('examinstance.templates.view')
            ->with('exam', $instance)
            ->with('itemtemplates', $itemtemplates)
            ->with('users', $users);
    }

    public
    function templatestore(Request $request)
    {
        $input = $request::all();
        $input['is_template'] = 'true';
        $newinstance = \App\Exam_instance::create($input);
        $response = array(
            'id' => strval($newinstance->id),
            'status' => strval($newinstance->id) > 0,
        );
        return $response;
    }

    public
    function templateajaxupdate(Request $request)
    {
        $input = $request::all();
        $instance = \App\Review_instances::findOrFail($input['id']);
        $status = strval($instance->update($input));
        $response = array(
            'status' => $status ? '1' : 0,
        );
        return $response;
    }

// Clone a template
    public
    function templateclone_instance(Request $request)
    {
        $input = $request::all();
        $input['is_template'] = 'true';
        $originalid = $input['id'];

// get the checklist items
        $checklistitems = Review_instances::find($originalid)->checklist;
        //  dd($checklistitems);
        // get the new instance ID
        // kill off id, or there'll be issues
        unset($input['id']);
        $newinstance = \App\Review_instances::create($input);

        // dd($newinstance);
        // build an array of new items to be inserted
        $newitems = [];
        foreach ($checklistitems as $item) {
            $newitems[] = new Review_instances_checklist_items(['description' => $item['description'], 'order' => $item['order'], 'heading' => $item['heading'], 'template_text' => $item['template_text']]);
        }
// just gunna have to assume this works. Doesn't return anything if it fails...
        $newinstance->checklist()->saveMany($newitems);

        $response = array(
            'id' => $newinstance->id,
        );

        return $response;
    }


    public
    function ajaxdestroy(Request $request)
    {
        $input = $request::all();
        Exam_instance::destroy([$input['id']]);
        return 0;


    }

///////////////////////////////////////////////////////////////
//
//Assessor assignments
//
////////////////////////////////////////////////////////////////
// get items for this item as an array
    public
    function addassessors(Request $request)
    {
        $input = $request::all();
        $exam = Exam_instance::find($input['exam_instance_id']);
        //try {
        // test to see if they're already assigned
        if ($exam->examiners()->where('users_id', 'in', $input['examinersselect'])->get()->count() == 0) {
            // do something if they're not
            $exam->examiners()->attach($input['examinersselect']);
        }
        $response = array(
            'status' => 0,
        );
//        } catch (\Exception $e) {
//            $response = array(
//                'status' => -1,
//            );
//        }


        return $response;
    }

    public
    function removeassessors(Request $request)
    {
        $input = $request::all();
        // print_r($input['exam_instance_id']);
        $exam = Exam_instance::find($input['exam_instance_id']);

        //  try {
        // @TODO test to see if they're already marked an assessment. If not, it could break reporting
        //if ($exam->examiners()->where('user_id', 'in', $input['examinersselect'])->get()->count() == 0) {

        $exam->examiners()->detach($input['examiner_id']);
        //}
        $response = array(
            'status' => 0,
        );
//        } catch (\Exception $e) {
//            dd($e);
//            $response = array(
//                'status' => -1,
//            );
//        }


        return $response;
    }

////////////////////////////////////////////////////////////////////////////////////////////////
///
/// Student management
///
/// ////////////////////////////////////////////////////////////////////////////////////////////////
    public
    function addcandidates(Request $request)
    {
        $input = $request::all();
        // dd($input);
        $exam = Exam_instance::find($input['exam_instance_id']);
        $errormessage = "";
        //try {
        // test to see if they're already assigned
        if ($exam->students()->where('students_id', 'in', $input['candidatesselect'])->get()->count() == 0) {
            // do something if they're not
            foreach ($input['candidatesselect'] as $candidate) {
                $exam->students()->attach($candidate, ['group_id' => $input['group_id']]);
            }
        }

        $response = array(
            'status' => 0,
        );
        return $response;
    }

    public
    function addcandidatesbycsv(Request $request)
    {
        $input = $request::all();
        $exam = Exam_instance::find($input['exam_instance_id']);
        //try {

        // get the CSV file
        $file = Request::file('userfile');

        // see if it's got a CSV extension. @TODO Probably should do more than this for validation
        $type = $file->getClientOriginalExtension();
        if (strtolower($type) != 'csv') {
            $errormessage = "You need to use a CSV file";
            $response = array(
                'status' => -1,
                'message' => $errormessage
            );
            return $response;
        }
        $errormessage = "";

        $createfailcount = 0;
        $isfirstrow = true;
        $candidatesarray = [];
        // open the CSV
        if (($handle = fopen($file, "r")) !== FALSE) {
            // get the rows using headers, check for sanity
            while (($data = fgetcsv($handle)) !== FALSE) {
                // print_r($data);
                if ($isfirstrow) {
                    if (array_search('studentid', $data) !== false) {
                        $studentnumrow = array_search('studentid', $data);
                    } else {
                        $errormessage = "Field header missing: Need to have a header called 'studentid'";
                    }
                    if (array_search('group', $data) !== false) {
                        $grouprow = array_search('group', $data);
                    } else {
                        $errormessage = "Field header missing: Need to have a header called 'group'";
                    }
                    $isfirstrow = false;
                } else {
                    // check that this student is already in the user table. If not, we'll have to check LDAP for an entry, and failing that we'll need to pass
                    // if they're already there
                    if (Student::where('studentid', '=', $data[$studentnumrow])->get()->count() == 0) {
                        //
                        // @TODO get from LDAP (or other source), create then add to the attach array
                        //
                    } else {
                        // use the internal data if the student already exists in the student db (but not if they're already in the exam...
                        if ($exam->students()->where('students_id', 'in', Student::where('studentid', '=', $data[$studentnumrow])->first()->id)->get()->count() == 0) {
                            $candidatesarray[Student::where('studentid', '=', $data[$studentnumrow])->first()->id] = ['group_id' => Group::where('code', '=', $data[$grouprow])->first()->id];
                        }
                    }

                }
                // moving on...
            }
        }
        fclose($handle);
        // perform the addition
        $exam->students()->attach($candidatesarray);
        $response = array(
            'status' => 0,
            'message' => $errormessage
        );
        return $response;
    }


    public
    function updatecandidategroup(Request $request)
    {
        $input = $request::all();

        // print_r($input);
        $modif = DB::table('student_exam_instances')->where('id', '=', $input['pk'])->first();
        // print_r($modif);
        if ($modif !== null) {  // just to make sure the bookmaker exists
            DB::table('student_exam_instances')
                ->where('id', $input['pk'])
                ->update(array('group_id' => $input['value']));
        }
        $response = array(
            'status' => 0,
        );


        return $response;
    }

    public
    function removecandidates(Request $request)
    {
        $input = $request::all();
        // print_r($input['exam_instance_id']);
        $exam = Exam_instance::find($input['exam_instance_id']);

        //  try {
        // @TODO test to see if they're already marked an assessment. If not, it could break reporting
        //if ($exam->examiners()->where('user_id', 'in', $input['examinersselect'])->get()->count() == 0) {

        $exam->students()->detach($input['candidate_id']);
        //}
        $response = array(
            'status' => 0,
        );
        return $response;
    }


/////////////////////////////////////////////////////////////////////////////////////
///
/// Submission
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
        //@TODO check that this hasn't been already submitted
        $submitdata = json_decode($input['submitdata']);
        dd($submitdata);

        $response = array(
            'status' => 0,
        );
        return $response;
    }

}
