<?php

// php7.2 compatibiliy fix
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
// Ignores notices and reports all other kinds... and warnings
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
// error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
}

Route::get('/', 'HomeController@index');

Route::get('/home', 'HomeController@home')->name('home');

// stuff for the public web. Probably won't need this for a private deployment
Route::get('/contact', 'ContactController@contactUS');
Route::post('/contact', ['as'=>'contact.store','uses'=>'ContactController@contactUSPost']);

Route::get('/blog', 'HomeController@blog');

// authentication
Auth::routes();

// hack to get logout working as GET
Route::get('/logout', 'Auth\LoginController@logout');

// LDAP testing


// Users
Route::resource('user', 'UserController');

Route::get('user/{}', 'UserController@show')->name('user.show');

Route::get('/my', 'UserController@my')->name('user.my');

Route::post('user/ajaxupdate', 'UserController@ajaxupdate');
Route::post('user/ajaxstore', 'UserController@ajaxstore');
Route::post('user/ajaxdestroy', 'UserController@ajaxdestroy');
Route::post('user/activate', 'UserController@activate');

//search for a user support for select2
Route::post('user/select2search', 'UserController@select2search');

// User images
Route::get('user/{id}/image', 'UserImageController@display');
Route::get('user/thumb/{id}/{size}', 'UserImageController@thumb');

// make the user image controller
Route::resource('userimage', 'UserImageController');

// update a media to a user
Route::post('userimage/update', 'UserImageController@update');

/////////////////////////////////////////////////
// User switching
/////////////////////////////////////////////////
Route::post('/sudosu/login-as-user', 'UserController@loginAsUser')
    ->name('sudosu.login_as_user');

Route::post('/sudosu/return', 'UserController@returnToUser')
    ->name('sudosu.return');


//////////////////////////////////////////////////
//Setup pages/ routes
/////////////////////////////////////////////////

Route::get('setup', 'SetupController@view')->name('setup.index');
Route::get('setup/configqrimage', 'SetupController@showConfigQR');


/////////////////////////////////////////////////
//students
/////////////////////////////////////////////////
Route::resource('student', 'StudentController');

Route::get('student/show/{id}', 'StudentController@show')->name('student.show');;
Route::post('student/ajaxupdate', 'StudentController@ajaxupdate');
Route::post('student/ajaxstore', 'StudentController@ajaxstore');
Route::post('student/ajaxdestroy', 'StudentController@ajaxdestroy');

//search for a student support for select2
Route::post('student/select2search', 'StudentController@select2search');

// student photos
// add a photo to a student
Route::post('student_image/create', 'StudentImageController@createmedia');
// update a image to a student
Route::post('student_image/update', 'StudentImageController@update');
// make the media controller a resource
Route::resource('student_image', 'StudentImageController');
// show the actual image (or an icon if a document)
Route::get('/student_image/show/{id}', 'StudentImageController@display');
// force a download
Route::get('/student_image/download/{id}', 'StudentImageController@download');
// show a thumbnail
Route::get('/student_image/thumb/{id}/{size}', 'StudentImageController@thumb');

// Examination Preparation and Administration
// show exams
Route::resource('exam', 'ExamInstanceController');
Route::post('exam/ajaxupdate', 'ExamInstanceController@ajaxupdate');
Route::post('exam/ajaxstore', 'ExamInstanceController@ajaxstore');
Route::post('exam/ajaxdestroy', 'ExamInstanceController@ajaxdestroy');
Route::get('exam/{id}/preview', 'PublicExamInstanceController@show');

// Assessment assessor management
Route::post('exam/addassessors', 'ExamInstanceController@addassessors');
Route::post('exam/removeassessors', 'ExamInstanceController@removeassessors');

// Assessment candidate management
Route::post('exam/addcandidates', 'ExamInstanceController@addcandidates');
Route::post('exam/addcandidatesbycsv', 'ExamInstanceController@addcandidatesbycsv');
Route::post('exam/updatecandidategroup', 'ExamInstanceController@updatecandidategroup');
Route::post('exam/removecandidate', 'ExamInstanceController@removecandidates');

// templates
Route::get('examtemplates', 'ExamInstanceController@templateindex')->name('examtemplates.index');
Route::get('examtemplates/{id}', 'ExamInstanceController@templateshow')->name('examtemplates.show');
Route::post('examtemplates/ajaxstore', 'ExamInstanceController@templatestore');
Route::post('examtemplates/{id}/ajaxupdate', 'ExamInstanceController@templateajaxupdate');

Route::get('examitemtemplates', 'ExamInstanceItemController@templateindex')->name('examitemtemplates.index');
Route::post('examitemtemplates/ajaxstore', 'ExamInstanceItemController@templatestore');
Route::post('examitemtemplates/{id}/ajaxupdate', 'ExamInstanceItemController@update');


// Assessment item management
Route::resource('examitem', 'ExamInstanceItemController');
Route::post('examitem', 'ExamInstanceItemController@update');
Route::post('examitem/store', 'ExamInstanceItemController@store');
Route::post('examitem/update', 'ExamInstanceItemController@update');
Route::post('examitem/ajaxdestroy', 'ExamInstanceItemController@ajaxdestroy');
Route::post('examitem/reorder', 'ExamInstanceItemController@reorder');
Route::post('examitem/{id}/getitemitemsasarray', 'ExamInstanceItemController@getitemitemsasarray');

// media for examinations
// make the media controller a resource
Route::resource('exammedia', 'ExaminationInstancesMediaController');
// add a media to a exam
Route::post('exammedia/create', 'ExaminationInstancesController@createmedia');
// update a media to a exam
Route::post('examinationmedia/update', 'ExaminationInstancesMediaController@update');
// show the actual image (or an icon if a document)
Route::get('examinationmedia/show/{id}', 'ExaminationInstancesMediaController@display');
// force a download
Route::get('examinationmedia/download/{id}', 'ExaminationInstancesMediaController@download');
// show a thumbnail
Route::get('examinationmedia/thumb/{id}', 'ExaminationInstancesMediaController@thumb');


// Reporting
Route::resource('report', 'ExamReportsController');
// show an individual session
Route::get('report/session/{sessionid}', 'ExamReportsController@detail')->name('report.session');
// send an individual email
Route::get('report/session/{submissionid}/email', 'ExamReportsController@sendemail');
// download a report in Excel format
Route::get('report/{id}/excelsummary', 'ExamReportsController@getReportAsExcel');

//feedback emails
// templates
Route::get('reportemails', 'ReportEmailsController@index')->name('reportemails.index');
Route::get('reportemails/{id}', 'ReportEmailsController@show');

Route::post('reportemails/create', 'ReportEmailsController@store');
Route::post('reportemails/{id}/update', 'ReportEmailsController@update');
Route::post('reportemails/destroy', 'ReportEmailsController@destroy');

// setup email feedback parameters
Route::post('report/{id}/setfeedbacksetup', 'ExamReportsController@feedbacksetup');



// updating
Route::resource('submissionitem', 'StudentExamSubmissionItemController');
Route::post('submissionitem/update', 'StudentExamSubmissionItemController@update');

// item changelog
Route::get('submissionitem/changelog/{id}', 'StudentExamSubmissionItemController@changelog');

// Conducting assessments in-site
Route::get('assess/{id}', 'ExamInstanceController@showInternalExam')->name('assess');