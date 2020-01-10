<?php

namespace App\Http\Controllers;

use Request;

class ReportEmailsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        //dd('hi');
        $input = $request::all();

        $users = \App\User::all();
        $emails = \App\Emails_template::where('context', 'reports')->get();
        return view('examinstance.templates.emails_templates.list')
            ->with('emails', $emails)
            ->with('users', $users);
    }

    public function update($id, Request $request)
    {

        $input = $request::all();
        $email = Emails_template::findOrNew($id);
// force the context
        $email->context = 'reports';
        $status = strval($email->update($input));
        //dd($input);
        $response = array(
            'status' => $status,
        );
        return $response;
    }


    public function store(Request $request)
    {
        $input = $request::all();
        $input['context'] = 'reports';
        $response = array(
            'id' => strval(\App\Emails_template::create($input)->id),
        );

        return $response;
    }


    public function show($id)
    {
        return \App\Emails_template::findOrFail($id);
    }


    public function destroy(Request $request)
    {
        $input = $request::all();
        return array(
            'status' => \App\Emails_template::destroy($input['id'])
        );
    }


}
