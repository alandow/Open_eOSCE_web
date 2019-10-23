<?php
/**
 * Created by PhpStorm.
 * User: alandow
 * Date: 29/02/2016
 * Time: 2:20 PM
 */
// Home
Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', route('home'));
});


Breadcrumbs::register('user.my', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("My Profile", route('user.my'));
});

Breadcrumbs::register('user.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("User Management", route('user.index'));
});

Breadcrumbs::register('user.show', function ($breadcrumbs, $user) {
    $breadcrumbs->parent('user.index');
    $breadcrumbs->push("{$user->name}", route('user.show', $user->id));
});

Breadcrumbs::register('systemlookups.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("System Lookups", route('systemlookups.index'));
});

Breadcrumbs::register('student.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("Student Management", route('student.index'));
});

Breadcrumbs::register('student.show', function ($breadcrumbs, $student) {
    $breadcrumbs->parent('student.index');
    $breadcrumbs->push("{$student->name}", route('student.show', $student->id));
});

Breadcrumbs::register('exam.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("Exams", route('exam.index'));
});
Breadcrumbs::register('exam.show', function ($breadcrumbs, $exam) {
    $breadcrumbs->parent('exam.index');
    $breadcrumbs->push("{$exam->name}", route('exam.show', $exam->id));
});

Breadcrumbs::register('examtemplates.index', function ($breadcrumbs) {
    $breadcrumbs->parent('exam.index');
    $breadcrumbs->push("Exam Templates", route('examtemplates.index'));
});
Breadcrumbs::register('examtemplates.show', function ($breadcrumbs, $exam) {
    $breadcrumbs->parent('examtemplates.index');
    $breadcrumbs->push("{$exam->name}", route('examtemplates.show', $exam->id));
});

Breadcrumbs::register('examitemtemplates.index', function ($breadcrumbs) {
    $breadcrumbs->parent('exam.index');
    $breadcrumbs->push("Exam Item Templates", route('examitemtemplates.index'));
});

Breadcrumbs::register('report.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("Reports", route('report.index'));
});
Breadcrumbs::register('report.show', function ($breadcrumbs, $exam) {
    $breadcrumbs->parent('report.index');
    $breadcrumbs->push("{$exam->name}", route('report.show', $exam->id));
});

Breadcrumbs::register('report.session', function ($breadcrumbs, $session) {
    $breadcrumbs->parent('report.show', $session->exam_instance);
    $breadcrumbs->push("{$session->student->fname} {$session->student->lname}", route('report.session', $session));
});

Breadcrumbs::register('reportemails.index', function ($breadcrumbs) {
    $breadcrumbs->parent('report.index');
    $breadcrumbs->push("Feedback email templates", route('reportemails.index' ));
});

// setups
Breadcrumbs::register('setup.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("Setup", route('setup.index'));
});

Breadcrumbs::register('setup.mobile', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("Mobile client setup", route('setup.mobile'));
});


Breadcrumbs::register('assess', function ($breadcrumbs, $exam) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push("Assess {$exam->name}", route('assess', $exam->id));
});