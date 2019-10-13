<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\Exam_instance_item;
use App\Exam_instance_item_item;
use App\Group;
use App\Http\Requests;
use App\SortableExam_results;
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
        $this->middleware('persistence')->only(['index', 'show']);
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
        $results = SortableExam_results::where('exam_instances_id', '=', $id)->sortable()->get();
        //dd($results->pluck('total'));
        // $users = User::all();
        //    $students = Student::all();
        $groups = Group::all();
        // get max score here
        $maxscore = 0;
        foreach ($exam->exam_instance_items()->scorable()->get() as $item) {
            $maxscore += $item->items->max('value');

        }

        // stats
        $stats = [];
      //  dd($results->pluck('total')->toArray());
        $overall_hist = $this->hist_array(1, $results->pluck('total')->toArray());
        $stats['overall'] = ['n' => $results->count(), 'mean' => $results->avg('total'), 'median'=>$this->calculate_median($results->pluck('total')->toArray()), 'stdev' => $this->sd($results->pluck('total')->toArray()), 'min' => $results->min('total'), 'max' => $results->max('total'), 'hist_array'=>$overall_hist];

        //dd($overall_hist);
        // dd($student->examinations_sessions[0]->responses);
        if ($exam->exists) {
            return view("reports.view")
                ->with('exam', $exam)
                // ->with('users', $users)
                ->with('groups', $groups)
                ->with('maxscore', $maxscore)
                ->with('results', $results)
            ->with('stats', $stats);
            //->with('students', $students);
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
                if ($item->selecteditem) {
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


    public
    function ajaxdestroy(Request $request)
    {
        $input = $request::all();
        Exam_instance::destroy([$input['id']]);
        return 0;


    }

    /////////////////////////////////////////////////////////////////////////
    ///
    /// Helper functions
    ///
    /// ////////////////

    // Function to calculate square of value - mean
    private function sd_square($x, $mean)
    {
        return pow($x - $mean, 2);
    }

// Function to calculate standard deviation (uses sd_square)
    private function sd($array)
    {
        // square root of sum of squares devided by N-1
        return sqrt(array_sum(array_map(array($this, "sd_square"), $array, array_fill(0, count($array), (array_sum($array) / count($array))))) / (count($array) - 1));
    }

    private function hist_array($step_size, $inputArray)
    {
        $step_size = $step_size;
        $histogramArray = array();
        foreach ($inputArray as $v)
        {
            $k = (int)ceil($v / $step_size) * $step_size;
            if (!array_key_exists($k, $histogramArray)){
                $histogramArray[$k] = 0;
            }
            $histogramArray[$k]++;
        }
        return $histogramArray;
    }

    private function calculate_median($array) {
        rsort($array);
        $middle = round(count($array) , 2);
                $total = $array[$middle-1];
        return $total;
    }

}
