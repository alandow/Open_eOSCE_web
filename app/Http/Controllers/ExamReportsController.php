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
    public function getSummaryReportAsExcel($id)
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

        // make a detailed worksheet
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
        // headings for assessment items
        $questionscount = 0;
        $questionsArr = array();
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
           // dd(Group::find($student_exam_submission->exam_instance->students->where('id', $student_exam_submission->student->id)->first()->pivot->group_id)->code);
            //print('num'.$studentexamsession->studentnum);
            $detailWorksheet->setCellValue('A' . $currentrow, $student_exam_submission->student->studentid);
            $detailWorksheet->setCellValue('B' . $currentrow, $student_exam_submission->student->fname . ' ' . $student_exam_submission->student->lname);
            $detailWorksheet->setCellValue('C' . $currentrow, Group::find($student_exam_submission->exam_instance->students->where('id', $student_exam_submission->student->id)->first()->pivot->group_id)->code);
            $detailWorksheet->setCellValue('D' . $currentrow, date_format($student_exam_submission->created_at, 'd/m/Y H:i:s A'));
            $detailWorksheet->setCellValue('E' . $currentrow, $student_exam_submission->examiner->name);
            $currentcolumn = 5;

            foreach ($exam->exam_instance_items as $submission_instance_item) {
                $label = "";
                $value = "";
                $comment = "";

                  if($submission_instance_item->heading!='1') {
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
            $detailWorksheet->setCellValue($alphabetArr[$currentcolumn].$currentrow, $student_exam_submission->comments);
            $currentrow++;
        }
        $detailWorksheet->setCellValue($alphabetArr[$currentcolumn].'2', "Final comment");
        $detailWorksheet->getStyle($alphabetArr[$currentcolumn].'2')->getFont()->setBold(true);
        $detailWorksheet->getColumnDimension($alphabetArr[$currentcolumn].'2')->setAutoSize(true);

        // set active sheet to the first
        $phpspreadsheetObj->setActiveSheetIndex(0);

        $writer = new Xlsx($phpspreadsheetObj);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"Report for {$exam->name}.xlsx\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }


    /**
     * Get a more complete report plus a data dump for analysis
     * @return PHPExcel an Excel spreadsheet containing a detailed report of the results of an assessment session
     */
    public function getFullReportAsExcel($id)
    {

        $exam = Exam_instance::findOrFail($id);
        $results = SortableExam_results::where('exam_instances_id', '=', $id)->sortable()->get();
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

        // questions labels.
        // alphabet array
        $alphabetArr = array();
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
        // There shouldn't be more than, say, 70 questions
        $j = 0;

//
//        // create excel sheet
//        $phpexcelObj = new PHPExcel();
//// make a new Excel sheet
//        $quantitativeWorksheet = new PHPExcel_Worksheet($phpexcelObj, 'Quantitative Cohort Outcomes');
//        $phpexcelObj->addSheet($quantitativeWorksheet, 0);
//
//        // set labels
//        $quantitativeWorksheet->getColumnDimension("A")->setWidth(200);
//        $quantitativeWorksheet->setCellValue('A1', "Quantitative Cohort Outcomes for:  {$exam->name} {$exam->start_datetime}, n=" . $stats['overall']['n']);
//        $quantitativeWorksheet->getStyle('A1')->getFont()->setSize(16);
//
//        $quantitativeWorksheet->setCellValue('A2', "Item");
//        $quantitativeWorksheet->getStyle("A2")->getFont()->setBold(true);
//
//
//        $questionscount = 0;
//        $questionsArr = array();
//        $k = 3;
//
//        $quantitativeWorksheet->getColumnDimension('A')->setAutoSize(true);
//
//        foreach ($exam->exam_instance_items as $question) {
//            //set all criteria labels
//            $quantitativeWorksheet->setCellValue("A$k", $question->label);
//            //    $quantitativeWorksheet->getStyle("A$k")->getFont()->setBold(true);
//
//
//
//            // get the criteria
//            $currentColumn = 1;
//
//            foreach ($question->items as $item) {
//                $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn]}2", "n marked {$item->short_description}");
//                $quantitativeWorksheet->getStyle("{$alphabetArr[$currentColumn]}2")->getFont()->setBold(true);
//                $quantitativeWorksheet->getColumnDimension("{$alphabetArr[$currentColumn]}")->setAutoSize(true);
//                $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn + 1]}2", "% marked {$item->short_description}");
//                $quantitativeWorksheet->getStyle("{$alphabetArr[$currentColumn + 1]}2")->getFont()->setBold(true);
//                $quantitativeWorksheet->getColumnDimension("{$alphabetArr[$currentColumn + 1]}")->setAutoSize(true);
//                $query = "SELECT COUNT(answer) as total FROM student_exam_sessions_responses WHERE question_ID = :id AND answer = :answer AND student_exam_sessions_responses.student_exam_session_ID IN(SELECT ID FROM student_exam_sessions WHERE student_exam_sessions.form_ID = :session_ID)";
//                $stmt = $conn->prepare($query);
//                $stmt->bindValue(':id', $question->id, PDO::PARAM_INT);
//                $stmt->bindValue(':answer', $item->value, PDO::PARAM_STR);
//                $stmt->bindValue(':session_ID', $session_ID, PDO::PARAM_INT);
//                $stmt->execute() or die('<data><error>select query failed query failed</error><detail>' . $stmt->errorCode() . '</detail></data>');
//
//                $resultArr = array();
//                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                    $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn]}$k", $row['total']);
//                    $quantitativeWorksheet->setCellValue("{$alphabetArr[$currentColumn + 1]}$k", (round($row['total'] / count($sessionsXML->session), 2) * 100) . "%");
//                }
//                $currentColumn += 2;
//                $stmt->closeCursor();
//            }
//            $k++;
//        }
//
//
//        // qualitative data
//        $criteriaWorksheet = new PHPExcel_Worksheet($phpexcelObj, 'Criteria Comments');
//        $phpexcelObj->addSheet($criteriaWorksheet, 1);
//
//        // set labels
//        $criteriaWorksheet->getColumnDimension("A")->setWidth(150);
//        $criteriaWorksheet->setCellValue('A1', "Qualitative Comments on Criteria Requiring Comments: {$overviewXML->summary->description} {$overviewXML->summary->examdate}, n=" . count($sessionsXML->session));
//        $criteriaWorksheet->getStyle('A1')->getFont()->setSize(16);
//
//        $criteriaWorksheet->setCellValue('A2', "Item");
//        $criteriaWorksheet->getStyle("A2")->getFont()->setBold(true);
//        $criteriaWorksheet->setCellValue('B2', "Comments");
//        $criteriaWorksheet->getStyle("B2")->getFont()->setBold(true);
//        $criteriaWorksheet->getColumnDimension("B")->setAutoSize(true);
//
//
//        $k = 3;
//
//        //
//        foreach ($questionsXML->question as $question) {
//            //set all criteria labels
//            // add up questions here
//            //$currentrow = 4;
//            //print('num'.$studentexamsession->studentnum);
//            $sql = "SELECT COUNT(*) FROM student_exam_sessions_responses WHERE question_ID = :id AND answer = 0 AND student_exam_sessions_responses.student_exam_session_ID IN(SELECT ID FROM student_exam_sessions WHERE student_exam_sessions.form_ID = :session_ID)";
//            $stmt = $conn->prepare($sql);
//            $stmt->bindValue(':id', $question->id, PDO::PARAM_INT);
//            $stmt->bindValue(':session_ID', $session_ID, PDO::PARAM_INT);
//            if ($stmt->execute()) {
//                if ($stmt->fetchColumn() > 0) {
//                    $stmt->closeCursor();
//                    $criteriaWorksheet->setCellValue("A$k", html_entity_decode($question->text, ENT_QUOTES, 'UTF-8'));
//                    //    $quantitativeWorksheet->getStyle("A$k")->getFont()->setBold(true);
//
//                    $criteriaWorksheet->getColumnDimension("A")->setAutoSize(true);
//                    $query = "SELECT comments FROM student_exam_sessions_responses WHERE question_ID = :id AND answer IN (SELECT value FROM assessment_criteria_scales_items WHERE assessment_criteria_scale_typeID = :criteriaid AND needs_comment ='true')";
//                    $stmt = $conn->prepare($query);
//                    $stmt->bindValue(':id', $question->id, PDO::PARAM_INT);
//                    $stmt->bindValue(':criteriaid', $overviewXML->summary->scale_id, PDO::PARAM_STR);
//
//                    $stmt->execute() or die('<data><error>select query failed query failed</error><detail>' . $stmt->errorCode() . '</detail></data>');
//
//                    $resultArr = array();
//                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                        $criteriaWorksheet->setCellValue("B$k", $row['comments']);
//                        $k++;
//                    }
//                    $stmt->closeCursor();
//                }
//            }
//        }
//
//        // qualitative data
//        $overallCommentsWorksheet = new PHPExcel_Worksheet($phpexcelObj, 'Overall Comments');
//        $phpexcelObj->addSheet($overallCommentsWorksheet, 2);
//
//        // set labels
//        $overallCommentsWorksheet->getColumnDimension("A")->setWidth(200);
//        $overallCommentsWorksheet->setCellValue('A1', "Qualitative Overall Comments: {$overviewXML->summary->description} {$overviewXML->summary->examdate}, n=" . count($sessionsXML->session));
//        $overallCommentsWorksheet->getStyle('A1')->getFont()->setSize(16);
//
//        $overallCommentsWorksheet->setCellValue('A2', "Comments");
//        $overallCommentsWorksheet->getStyle("A2")->getFont()->setBold(true);
//
//
//        $k = 3;
//
//
//        //
//        foreach ($overviewXML->session as $session) {
//
//            if (strlen($session->comments) > 0) {
//                $overallCommentsWorksheet->setCellValue("A$k", html_entity_decode($session->comments, ENT_QUOTES, 'UTF-8'));
//                //    $quantitativeWorksheet->getStyle("A$k")->getFont()->setBold(true);
//
//                $k++;
//            }
//        }
//
//        $rawWorksheet = new PHPExcel_Worksheet($phpexcelObj, 'Raw Scores Data Dump');
//        $phpexcelObj->addSheet($rawWorksheet, 3);
//// assessment raw data
//        $rawWorksheet->setCellValue('A1', "Assessment Raw Data: {$overviewXML->summary->description} {$overviewXML->summary->examdate}");
//        $rawWorksheet->getStyle('A1')->getFont()->setSize(16);
//        $rawWorksheet->setCellValue('A2', "Student Number");
//        $rawWorksheet->getStyle('A2')->getFont()->setBold(true);
//        $rawWorksheet->setCellValue('B2', "Student Name");
//        $rawWorksheet->getStyle('B2')->getFont()->setBold(true);
//        $rawWorksheet->setCellValue('C2', "Site");
//        $rawWorksheet->getStyle('C2')->getFont()->setBold(true);
//        $rawWorksheet->setCellValue('D2', "Assessment Timestamp");
//        $rawWorksheet->getStyle('D2')->getFont()->setBold(true);
////        $rawWorksheet->getColumnDimension('B')->setAutoSize(true);
////        $rawWorksheet->getColumnDimension('C')->setAutoSize(true);
//
//
//        $questionscount = 0;
//        $questionsArr = array();
//        $k = 4;
//        // get all criteria labels
//        foreach ($questionsXML->question as $question) {
//            //   $criterias = simplexml_load_string($enumlib->getCriteriaForQuestion($question->id, false));
//            // foreach ($criterias as $criteria) {
//            //  print($criteria->text . '<br/>');
//            if ($question->type != 'label') {
//                $rawWorksheet->setCellValue($alphabetArr[$k] . '2', $question->text);
//                $rawWorksheet->getStyle($alphabetArr[$k] . '2')->getFont()->setBold(true);
//                $rawWorksheet->getColumnDimension($alphabetArr[$k])->setAutoSize(true);
//                $questionsArr[] = array("id" => (string)$question->id, "column" => $alphabetArr[$k]);
//                // get student results, loop to populate all answers to this criteria
//                // can probably do this better...
//                $questionscount++;
//                $k++;
//            }
//            // }
//        }
//        // do global criteria
//
//        foreach ($overallratingdata->item as $overallratingitem) {
//            $rawWorksheet->setCellValue($alphabetArr[$k] . '2', $overallratingitem->label);
//            $rawWorksheet->getStyle($alphabetArr[$k] . '2')->getFont()->setBold(true);
//            $questionsArr[] = array("id" => (string)$overallratingitem, "column" => $alphabetArr[$k]);
//            $k++;
//        }
//
//
//        // print('questionsArr:<br/>');
//        //  print_r($questionsArr);
//        // populate student details
//        $currentrow = 3;
//        foreach ($sessionsXML->session as $studentexamsession) {
//            //print('num'.$studentexamsession->studentnum);
//            $rawWorksheet->setCellValue('A' . $currentrow, $studentexamsession->studentnum);
//            $rawWorksheet->setCellValue('B' . $currentrow, $studentexamsession->fname . ' ' . $studentexamsession->lname);
//            $rawWorksheet->setCellValue('C' . $currentrow, simplexml_load_string($enumlib->getSiteByID($studentexamsession->siteid))->code);
//            $rawWorksheet->setCellValue('D' . $currentrow, $studentexamsession->created_timedate);
//
//            $query = "SELECT * FROM student_exam_sessions_responses WHERE student_exam_session_ID = :sessionid";
//            $stmt = $conn->prepare($query);
//            $stmt->bindValue(':sessionid', $studentexamsession->id, PDO::PARAM_INT);
//            $stmt->execute() or die('<data><error>select query failed query failed</error><detail>' . $stmt->errorCode() . '</detail></data>');
//
//            $resultArr = array();
//            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                $resultArr[] = array("id" => $row['question_ID'], "answer" => $row['answer']);
//            }
//
//            for ($i = 0; $i < count($questionsArr); $i++) {
//                $rawWorksheet->setCellValue($questionsArr[$i]['column'] . $currentrow, $this->findAnswer($questionsArr[$i]['id'], $resultArr));
//            }
//            $stmt->closeCursor();
//
//            $overallratingdata = simplexml_load_string($this->getOverallRatingDataForSession($studentexamsession->id));
//            $j = count($questionsArr) - $overallratingdata->count();
//
//            foreach ($overallratingdata->item as $overallratingitem) {
//                $rawWorksheet->setCellValue($questionsArr[$j]['column'] . $currentrow, $overallratingitem->answer);
//                $j++;
//            }
//            //var_dump($overallratingdata->count());
//            // die();
//            $currentrow++;
//            $stmt->closeCursor();
//
//        }
//
//
//        $phpexcelObj->setActiveSheetIndex(0);
//
//        return $phpexcelObj;
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
