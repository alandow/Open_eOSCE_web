<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use LaravelQRCode\Facades\QRCode;

//use QR_Code\QR_Code;

class SetupController extends Controller
{

    var $client;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->client = DB::table('oauth_clients')->where('id', 3)->first();
    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function view()
    {
//        $users = User::all();
        return view('setup/view');//->with('users', $users);;
    }

//    public function mobileview()
//    {
////        $users = User::all();
//        return view('setup/mobile');//->with('users', $users);;
//    }

    public function showConfigQR()
    {
        // disable the debugbar for the QR code
        app('debugbar')->disable();
        $configObj = ['name'=>config('app.name'),'service_url'=>((URL('/')=="http://localhost/eosce_laravel")?"http://192.168.1.2/eosce_laravel2/":URL('/')), 'client_id'=>$this->client->id, 'client_secret'=>$this->client->secret];

        return  QRCode::text(json_encode($configObj))->svg();
    }
}
