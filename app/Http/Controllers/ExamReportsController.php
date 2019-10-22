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
use App\Student_exam_submission_item;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use DebugBar\DebugBar;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
        $stats['overall'] = ['n' => $results->count(), 'mean' => $results->avg('total'), 'median' => $this->median($results->pluck('total')->toArray()), 'stdev' => $this->sd($results->pluck('total')->toArray()), 'min' => $results->min('total'), 'max' => $results->max('total'), 'hist_array' => $overall_hist];

        // get examiner stats
        $stats['examiners'] = [];
        foreach ($exam->examiners as $examiner) {
            $examiner_results = $results->filter(function ($value, $key) use ($examiner) {
                return ($value->created_by_id == $examiner->id);
            });
            $examiner_hist = $this->hist_array(1, $examiner_results->pluck('total')->toArray());
            $stats['examiners'][$examiner->id] = ['n' => $examiner_results->count(), 'mean' => $examiner_results->avg('total'), 'median' => $this->median($examiner_results->pluck('total')->toArray()), 'stdev' => $this->sd($examiner_results->pluck('total')->toArray()), 'min' => $examiner_results->min('total'), 'max' => $examiner_results->max('total'), 'hist_array' => $examiner_hist];
        }

        // get group stats

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
        $submission = Student_exam_submission::findOrFail($sessionid);
        $definition = Exam_instance::findOrFail($submission->exam_instances_id);
        // dd($definition);
// get total
        $score = 0;
        $maxscore = 0;
        foreach ($submission->student_exam_submission_items as $item) {
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
            ->with('submission', $submission)
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
    /// Export to Excel functions
    ///
    /// ////////////////

    /**
     * Get a summary report for an assessment session as Excel
     * 2 worksheets: overview and details
     * @param type $session_ID
     * @return PHPExcel an Excel spreadsheet containing a summary report of the results of an assessment session
     */
    public function getReportAsExcel($id)
    {
        // kill the debugbar briefly
        \Debugbar::disable();

        // get some exam stats
        $exam = Exam_instance::findOrFail($id);
        $results = SortableExam_results::where('exam_instances_id', '=', $id)->sortable()->get();
        $maxscore = 0;
        foreach ($exam->exam_instance_items()->scorable()->get() as $item) {
            $maxscore += $item->items->max('value');
        }
        $stats = [];
        //  dd($results->pluck('total')->toArray());
        $overall_hist = $this->hist_array(1, $results->pluck('total')->toArray());
        $stats['overall'] = ['n' => $results->count(), 'mean' => $results->avg('total'), 'median' => $this->median($results->pluck('total')->toArray()), 'stdev' => $this->sd($results->pluck('total')->toArray()), 'min' => $results->min('total'), 'max' => $results->max('total'), 'hist_array' => $overall_hist];


        // an array containing the alphabet
        $alphabetArr = array();
        $j = 0;
        foreach (range('A', 'Z') as $i) {
            $alphabetArr[] = $i;
        }
        // this extends the possible spreadsheet cells a bit.
        foreach (range('A', 'Z') as $i) {
            $alphabetArr[] = "A" . $i;
        }
        foreach (range('A', 'Z') as $i) {
            $alphabetArr[] = "B" . $i;
        }
        foreach (range('A', 'Z') as $i) {
            $alphabetArr[] = "C" . $i;
        }

        // the spreadsheet object
        $phpspreadsheetObj = new Spreadsheet();

        // make a new Excel sheet
        $summaryWorksheet = new Worksheet($phpspreadsheetObj, 'Assessment summary');
        // $phpexcelObj->createSheet();
        $phpspreadsheetObj->addSheet($summaryWorksheet, 0);

        // put some headings in
        $summaryWorksheet->setCellValue('A1', "Assessment summary: {$exam->name} {$exam->start_datetime}");
        $summaryWorksheet->getStyle('A1')->getFont()->setSize(16);
        $summaryWorksheet->setCellValue('A2', "Student Number");
        $summaryWorksheet->getStyle('A2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('B2', "Student Name");
        $summaryWorksheet->getStyle('B2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('C2', "Assessment Date/Time");
        $summaryWorksheet->getStyle('C2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('D2', "Site");
        $summaryWorksheet->getStyle('D2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('E2', "Score");
        $summaryWorksheet->getStyle('E2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('F2', "Out of a Possible");
        $summaryWorksheet->getStyle('F2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('G2', "Comments");
        $summaryWorksheet->getStyle('G2')->getFont()->setBold(true);
        $summaryWorksheet->setCellValue('H2', "Assessor");
        $summaryWorksheet->getStyle('H2')->getFont()->setBold(true);

// format a bit
        $summaryWorksheet->getColumnDimension('A')->setWidth(26);
        $summaryWorksheet->getColumnDimension('B')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('C')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('D')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('E')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('F')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('G')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('H')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('I')->setAutoSize(true);
        $summaryWorksheet->getColumnDimension('J')->setAutoSize(true);

        // write the summary to the spreadsheet
        $i = 0;
        foreach ($results as $result) {
            $summaryWorksheet->setCellValue('A' . ($i + 3), $result->student->studentid);
            $summaryWorksheet->setCellValue('B' . ($i + 3), $result->studentname);
            $summaryWorksheet->setCellValue('C' . ($i + 3), date_format($result->created_at, 'd/m/Y H:i:s A'));
            $summaryWorksheet->setCellValue('D' . ($i + 3), $result->groupcode);
            $summaryWorksheet->setCellValue('E' . ($i + 3), $result->total);
            $summaryWorksheet->setCellValue('F' . ($i + 3), $maxscore);
            $summaryWorksheet->setCellValue('G' . ($i + 3), $result->comments);
            $summaryWorksheet->setCellValue('H' . ($i + 3), $result->created_by);
            $i++;
        }

        ///////////////////////////////////////////////
        ///  make a detailed worksheet- sort of a data dump
        /// /////////////////////////////////////////////

        $detailWorksheet = new Worksheet($phpspreadsheetObj, 'Assessment detail');
        $phpspreadsheetObj->addSheet($detailWorksheet, 1);

        // put some headings in
        $detailWorksheet->setCellValue('A1', "Assessment detail: {$exam->name} {$exam->start_datetime}");
        $detailWorksheet->getStyle('A1')->getFont()->setSize(16);
        $detailWorksheet->getColumnDimension('A1')->setAutoSize(true);
        $detailWorksheet->setCellValue('A2', "Student Number");
        $detailWorksheet->getStyle('A2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension('A2')->setAutoSize(true);
        $detailWorksheet->setCellValue('B2', "Student Name");
        $detailWorksheet->getStyle('B2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension('B2')->setAutoSize(true);
        $detailWorksheet->setCellValue('C2', "Group");
        $detailWorksheet->getStyle('C2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension('C2')->setAutoSize(true);
        $detailWorksheet->setCellValue('D2', "Assessment Date/Time");
        $detailWorksheet->getStyle('D2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension('D2')->setAutoSize(true);
        $detailWorksheet->setCellValue('E2', "Assessor");
        $detailWorksheet->getStyle('E2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension('E2')->setAutoSize(true);

        //results
        $k = 5;
        // get all criteria labels
        foreach ($exam->exam_instance_items as $question) {
            if ($question->heading != '1') {
                // the question label
                $label = $question->label;
                if ($question->exclude_from_total == '1') {
                    $label .= '(Formative)';
                }
                $detailWorksheet->setCellValue($alphabetArr[$k] . '2', $label);
                $detailWorksheet->getStyle($alphabetArr[$k] . '2')->getFont()->setBold(true);
                $detailWorksheet->getColumnDimension($alphabetArr[$k])->setAutoSize(true);

                // the value
                $detailWorksheet->setCellValue($alphabetArr[$k + 1] . '2', 'Value');
                $detailWorksheet->getStyle($alphabetArr[$k + 1] . '2')->getFont()->setBold(true);
                $detailWorksheet->getColumnDimension($alphabetArr[$k + 1])->setAutoSize(true);

                // any comments
                if ($question->no_comment != '1') {
                    $detailWorksheet->setCellValue($alphabetArr[$k + 2] . '2', 'Comment');
                    $detailWorksheet->getStyle($alphabetArr[$k + 2] . '2')->getFont()->setBold(true);
                    $detailWorksheet->getColumnDimension($alphabetArr[$k + 2])->setAutoSize(true);
                    $k += 3;
                } else {
                    $k += 2;
                }
            }
        }

        // write out the results
        $currentrow = 3;
        foreach ($exam->student_exam_submissions as $student_exam_submission) {
            $detailWorksheet->setCellValue('A' . $currentrow, $student_exam_submission->student->studentid);
            $detailWorksheet->setCellValue('B' . $currentrow, $student_exam_submission->student->fname . ' ' . $student_exam_submission->student->lname);
            $detailWorksheet->setCellValue('C' . $currentrow, Group::find($student_exam_submission->exam_instance->students->where('id', $student_exam_submission->student->id)->first()->pivot->group_id)->code);
            $detailWorksheet->setCellValue('D' . $currentrow, date_format($student_exam_submission->created_at, 'd/m/Y H:i:s A'));
            $detailWorksheet->setCellValue('E' . $currentrow, $student_exam_submission->examiner->name);
            $currentcolumn = 5;

            foreach ($exam->exam_instance_items as $submission_instance_item) {
                if ($submission_instance_item->heading != '1') {
                    //    dd($student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->selecteditem);
                    if ($student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->selecteditem) {
                        $label = $student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->selecteditem->label;
                        $detailWorksheet->setCellValue($alphabetArr[$currentcolumn] . strval($currentrow), $label);
                        $currentcolumn++;
                        $value = $student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->selecteditem->value;
                        $detailWorksheet->setCellValue($alphabetArr[$currentcolumn] . strval($currentrow), $value);
                        $currentcolumn++;
                        if ($student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->item->no_comment != '1') {
                            $comment = $student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->comments;
                            $detailWorksheet->setCellValue($alphabetArr[$currentcolumn] . strval($currentrow), $comment);
                            $currentcolumn++;
                        }
                    } else {
                        $detailWorksheet->setCellValue($alphabetArr[$currentcolumn] . strval($currentrow), '(not shown)');
                        if ($student_exam_submission->student_exam_submission_items->where('exam_instance_items_id', $submission_instance_item->id)->first()->item->no_comment != '1') {
                            $currentcolumn += 3;
                        } else {
                            $currentcolumn += 2;
                        }
                    }
                }

            }
            $detailWorksheet->setCellValue($alphabetArr[$currentcolumn] . $currentrow, $student_exam_submission->comments);
            $currentrow++;
        }
        $detailWorksheet->setCellValue($alphabetArr[$currentcolumn] . '2', "Final comment");
        $detailWorksheet->getStyle($alphabetArr[$currentcolumn] . '2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension($alphabetArr[$currentcolumn] . '2')->setAutoSize(true);


        ///////////////////////////////////////////////////////////////
        //
        ///////////////////////////////////////////////////////////////
        $quantitativeWorksheet = new Worksheet($phpspreadsheetObj, 'Quantitative item analysis');
        // $phpexcelObj->createSheet();
        $phpspreadsheetObj->addSheet($quantitativeWorksheet, 2);

        // set labels

        $quantitativeWorksheet->setCellValue('A1', "Quantitative item analysis for:  {$exam->name} {$exam->start_datetime}, n=" . $stats['overall']['n']);
        $quantitativeWorksheet->getStyle('A1')->getFont()->setSize(16);
//
        $questionscount = 0;
        $questionsArr = array();
        $k = 3;

        //////////////////////////////////////////////////////////
        // the quantitative data for the questions...
        //////////////////////////////////////////////////////////
//
        foreach ($exam->exam_instance_items as $question) {
            if ($question->heading != '1') {
//            //set all criteria labels
                $label = $question->label;
                if ($question->exclude_from_total == '1') {
                    $label .= '(Formative)';
                }
                $quantitativeWorksheet->setCellValue("A$k", $label);

                $currentColumn = 1;
                // get total answers
                $n_responses = Student_exam_submission_item::where('exam_instance_items_id', '=', $question->id)->get()->count();
                foreach ($question->items as $item) {
                    $n_marked = 0;
                    $n_marked = Student_exam_submission_item::where('selected_exam_instance_items_items_id', '=', $item->id)->get()->count();
                    $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn]}{$k}", "n marked {$item->label}");
                    $quantitativeWorksheet->getStyle("{$alphabetArr[$currentColumn]}{$k}")->getFont()->setBold(true);
                    $quantitativeWorksheet->getColumnDimension("{$alphabetArr[$currentColumn]}")->setAutoSize(true);
                    $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn + 1]}{$k}", "% marked {$item->label}");
                    $quantitativeWorksheet->getStyle("{$alphabetArr[$currentColumn + 1]}{$k}")->getFont()->setBold(true);
                    $quantitativeWorksheet->getColumnDimension("{$alphabetArr[$currentColumn + 1]}")->setAutoSize(true);
                    // data
                    //n
                    $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn]}" . ($k + 1), $n_marked);
                    //%
                    $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn+1]}" . ($k + 1), ($n_marked / $n_responses) * 100);
                    $currentColumn += 2;
                }
                $k += 2;
            }
        }
        $quantitativeWorksheet->getColumnDimension("A")->setAutoSize(true);

        //////////////////////////////////////////
        // Comments worksheet
        /////////////////////////////////////////

        $overallCommentsWorksheet = new Worksheet($phpspreadsheetObj, 'Comments for items');
        $phpspreadsheetObj->addSheet($overallCommentsWorksheet, 3);

        $overallCommentsWorksheet->setCellValue('A1', "Comments for items: {$exam->name} {$exam->start_datetime}, n=" . $stats['overall']['n']);
        $overallCommentsWorksheet->getStyle('A1')->getFont()->setSize(16);

        $overallCommentsWorksheet->setCellValue('B2', "Comments");
        $overallCommentsWorksheet->getStyle("B2")->getFont()->setBold(true);
        $overallCommentsWorksheet->setCellValue('A2', "Item");
        $overallCommentsWorksheet->getStyle("A2")->getFont()->setBold(true);

        $k = 3;
        foreach ($exam->exam_instance_items as $question) {
            if (($question->heading != '1')&&($question->no_comment!='1')) {
                $overallCommentsWorksheet->setCellValue('A' . $k, "{$question->label}");
                $responses = Student_exam_submission_item::where('exam_instance_items_id', '=', $question->id)->get();
                $k++;
                foreach ($responses as $response) {
                    if (strlen($response->comments) > 0) {
                        $overallCommentsWorksheet->setCellValue('B' . $k, "{$response->comments}");
                        $k++;
                    }
                }
            }
        }

        $overallCommentsWorksheet->getColumnDimension("A")->setAutoSize(true);
        $overallCommentsWorksheet->getColumnDimension("B")->setAutoSize(true);

        // set active sheet to the first
        $phpspreadsheetObj->setActiveSheetIndex(0);

        $writer = new Xlsx($phpspreadsheetObj);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"Report for {$exam->name}.xlsx\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
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
        foreach ($inputArray as $v) {
            $k = (int)ceil($v / $step_size) * $step_size;
            if (!array_key_exists($k, $histogramArray)) {
                $histogramArray[$k] = 0;
            }
            $histogramArray[$k]++;
        }
        return $histogramArray;
    }

    private function calculate_median($array)
    {
        rsort($array);
        $middle = round(count($array), 2);
        $total = $array[$middle - 1];
        return $total;
    }

    private function median($arr)
    {
        sort($arr);
        $count = count($arr); //count the number of values in array
        $middleval = floor(($count - 1) / 2); // find the middle value, or the lowest middle value
        if ($count % 2) { // odd number, middle is the median
            $median = $arr[$middleval];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleval];
            $high = $arr[$middleval + 1];
            $median = (($low + $high) / 2);
        }
        return $median;
    }

}

