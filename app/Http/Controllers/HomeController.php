<?php

namespace App\Http\Controllers;

use App\Exam_instance;
use App\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome');
    }

    public function home()
    {
        $users = User::all();
        $user = Auth::user();
        $activeexams = Exam_instance::where('status', '=', 'active')
            ->whereNull('archived_at')->get();
        return view('home')->with('users', $users)->with('user', $user)->with('exams', $activeexams);
    }

    public function blog()
    {
        return view('welcome');
    }

    public function contact()
    {
        return view('welcome');
    }
}
