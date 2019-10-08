<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function contactUS()
    {
        return view('contact.view');
    }
    /**
     *      *
     * @return \Illuminate\Http\Response
     */
    public function contactUSPost(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ]);
        ContactUS::create($request->all());
        return back()->with('success', 'Thanks for contacting us!');
    }
}
