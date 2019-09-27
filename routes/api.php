<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// do a quick check of the config from the app
Route::post('/check', function (Request $request) {
    $client = DB::table('oauth_clients')->where('id', 3)->first();
    $returnVal = (($request->input('client_id') == $client->id)
        && ($request->input('client_secret')== $client->secret))
        ? ['status' => true, 'sysname' => \Illuminate\Support\Facades\Config::get('app.name')] : ['status' => false];
    return ($returnVal);
});

// logout
Route::middleware('auth:api')->post('/logout', function (Request $request) {
    $request->user()->token()->revoke();
    $json = [
        'success' => true,
        'code' => 200,
        'message' => 'You are Logged out.',
    ];
    return response()->json($json, '200');
});

// return user details
Route::middleware('auth:api')->post('/user', function (Request $request) {
    return $request->user();
});


// get list of assessments for this logged in user
Route::middleware('auth:api')->post('/getassessments', function (Request $request) {
    return $request->user()->exam_instances;
});

Route::middleware('auth:api')->post('/getassessmentdetails/{id}', function (Request $request, $id) {
    // @TODO check that the assessor can assess this assessment.
    return \App\Exam_instance::with('exam_instance_items.items')->where('id', $id)->get()->first();
});

// get students of an assessment
Route::middleware('auth:api')->post('/getassessmentstudents/{id}', function (Request $request, $id) {
    // @TODO check that the assessor can assess this assessment.
    // return \App\Exam_instance::find($id)->students;
    //dd(\App\Student::studentsforexam($id));
    return \App\Student::studentsforexam($id)->get();

});

//Route::middleware('auth:api')->post('/submitassessment', function (Request $request) {
//    $input = $request->all();
//    $answerdata = $input['answerdata'];
////    foreach ($answerdata as $answer) {
////        print_r($answer);
////            if (strval(\App\Exam_instance_item::find($answer->id)->heading) != '1') {
////                $answers[] = new \App\Student_exam_submission_item(['exam_instance_items_id' => $answer->id, 'selected_exam_instance_items_items_id' => $answer->selected_id, 'comments' => ((isset($answer->comment) ? $answer->comment : ''))]);
////            }
////    }
//    $json = json_encode($answerdata[0]['id']);
////    $json = [
////        'success' => true,
////        'code' => 200,
////        'message' => 'We did the thing.',
////    ];
//    return response()->json($json, '200');
//});
Route::middleware('auth:api')->post('/submitassessment', 'StudentExamSubmissionController@submitAssessment');


Route::post('exam/{id}/getexamdefinition', 'PublicExamInstanceController@getexamdefinition');

