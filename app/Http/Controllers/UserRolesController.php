<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class UserRolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {


        $roles = \App\Roles::all();

        return view("setup.roles.index")->with('roles', $roles);
    }


    public function create(Request $request)
    {
        $input = $request->all();
        $newentry = \App\Roles::create($input);
        return $newentry->id;
    }

    /**
     * Updates the record
     * @param $id
     * @param Request $request
     * @return $this
     */
    public function update(Request $request)
    {

        $input = $request->all();
        $contact = \App\Roles::findOrNew($input['id']);

        $status = strval($contact->update($input));
        //dd($input);
        $response = array(
            'status' => $status,
        );
        return $response;
    }


    public function store(Request $request)
    {
        $input = $request->all();
        return \App\Roles::create($input)->id;
    }

    public function show($id)
    {
        return \App\Roles::findOrFail($id);
    }


    public function destroy(\Illuminate\Http\Request $request)
    {
        $input = $request->all();
        return \App\Roles::destroy($input['id']);
    }
}
