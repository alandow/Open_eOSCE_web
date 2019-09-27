<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;


class StudentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $params = $request::all();
        if (isset($params['search'])) {
            $student = Student::where('studentid', 'like', "%{$params['search']}%")
                ->orWhere(DB::raw('CONCAT(fname, lname)'), 'LIKE', "%{$params['search']}%");
            $students = $student->sortable()->paginate(20);
        } else {
            $students = Student::sortable()->paginate(20);
        }


        return view("student.list")->with('students', $students);
    }

    public function show($id)
    {

        $student = \App\Student::findOrFail($id);
        // dd($student->examinations_sessions[0]->responses);
        if ($student->exists) {
            return view('student.view')->with('student', $student);
        } else {
            return redirect('home');
        }
    }

    public function ajaxstore(Request $request)
    {
        if (Gate::denies('update_student')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request::all();
        //   dd($file);
        // find out if there's an existing user
        $student = \App\Student::where('studentid', $input['studentid'])->first();
        if (isset($student)) {
            return array(
                'status' => '-1',
                'statusText' => 'a student with that ID already exists',
            );
        } else {
            $newstudent = new Student($input);
            $status = strval($newstudent->save());
            $response = array(
                'status' => $status,
            );
        }

        $file = Input::file('userfile');
        //dd($file);
        if (Input::hasFile('userfile')) {
            // update file

            // check it's not too big
            if ($file->getMaxFilesize() > $file->getSize()) {
                // get type
                $type = $file->getClientOriginalExtension();
                // @todo is this an allowed type?
                if (in_array($type, ['jpg', 'jpeg', 'png'])) {

                    // get the md5 hash of the contents. This allows for different files with the same name in teh same directory...
                    $md5name = md5(file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()));

                    // store it to disk
                    if (!(Storage::disk('local')->put('media' . DIRECTORY_SEPARATOR . $md5name, \Illuminate\Support\Facades\File::get($file)))) {
                        return '-1';
                    }
                    // save the record
                    $media = \App\Student_image::firstOrNew(['student_id' => $newstudent->id]);

                    $media->student_id = $newstudent->id;
                    $media->filename = $file->getClientOriginalName();
                    $media->path = 'media' . DIRECTORY_SEPARATOR . $md5name;
                    $mediastatus = strval($media->save());
                    $response['mediastatus'] = $mediastatus;
                } else {
                    $response['statusText'] = 'not a jpg or png';
                }

            } else {
                $response['statusText'] = 'file too big';
            }
        }

        return $response;
    }

    public function ajaxupdate(Request $request)
    {
        if (Gate::denies('update_student')) {
            abort(403, 'Unauthorized action.');
        }
//update student data
        $input = $request::all();
        $student = \App\Student::findOrNew($input['id']);

        $response = array(
            'status' => $student->update($input) ? '0' : '-1'
        );
        $file = Input::file('userfile');

        if (Input::hasFile('userfile')) {
            // update file

            // check it's not too big
            if ($file->getMaxFilesize() > $file->getSize()) {
                // get type
                $type = strtolower($file->getClientOriginalExtension());
//                $type = $file->getClientOriginalExtension();
                // @todo is this an allowed type?
                if (in_array($type, ['jpg', 'jpeg', 'png'])) {

                    // get the md5 hash of the contents. This allows for different files with the same name in teh same directory...
                    $md5name = md5(file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()));

                    // store it to disk
                    if (!(Storage::disk('local')->put('media' . DIRECTORY_SEPARATOR . $md5name, \Illuminate\Support\Facades\File::get($file)))) {
                        return '-1';
                    }
                    // save the record
                    $media = \App\Student_image::firstOrNew(['student_id' => $input['id']]);

                    $media->student_id = $input['id'];
                    $media->filename = $file->getClientOriginalName();
                    $media->path = 'media' . DIRECTORY_SEPARATOR . $md5name;
                    $media->save();
                    //$response['mediastatus'] = $mediastatus;
                } else {
                    $response['mediastatus'] = 'not a jpg or png';
                }

            } else {
                $response['mediastatus'] = 'file too big';
            }
        }

        return $response;
    }

    public function ajaxdestroy(\Illuminate\Http\Request $request)
    {

        if (Gate::denies('update_student')) {
            abort(403, 'Unauthorized action.');
        }
        $input = $request->all();
        //dd($input);
        return \App\Student::destroy($input['id']);
    }

    public function select2search(Request $request)
    {
        $input = $request::all();
        $dontshow = [];
        if (isset($input['exam_instance_id'])) {
            $dontshow = Exam_instance::find($input['exam_instance_id'])->students->pluck('id')->toArray();
            $results = Student::where('studentid', 'like', "%{$input['q']}%")
                ->orWhere('fname', 'like', "%{$input['q']}%")
                ->orWhere('lname', 'like', "%{$input['q']}%")
                ->orderBy('fname')
                ->get();
        } else {
            $results = Student::where('studentid', 'like', "%{$input['q']}%")
                ->orWhere('fname', 'like', "%{$input['q']}%")
                ->orWhere('lname', 'like', "%{$input['q']}%")
                ->orderBy('fname')
                ->get();
        }

        $returnStr = '{
  "results": [';
        foreach ($results as $result) {
            // could probably do this better, as part of the query. But, this works for now
            if (!(in_array($result['id'], $dontshow))) {
                $returnStr .= '{"id": "' . $result['id'] . '", "text": "' . $result['fname'] . ' ' . $result['lname'] . ' (' . $result['studentid'] . ')"},';
            }
        }
        $returnStr = rtrim($returnStr, ',');
        //array_map(function($n){return (object) ['id' => $n->id, 'text'=>$n->name ];},);
        return ($returnStr . ']
}');

    }

}
