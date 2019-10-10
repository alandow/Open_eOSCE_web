<?php
/**
 * Created by PhpStorm.
 * User: alandow
 * Date: 17/06/2019
 * Time: 10:52 AM

 Adapted from https://stephenreescarter.net/using-laravel-5-middleware-for-parameter-persistence/*/

namespace app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class ParameterPersistence
{

    public function handle(Request $request, Closure $next)
    {
        //dd('Im running');
        // Unique session key based on class name and route name
        $key = __CLASS__.'|'.$request->route()->getName();

        // Retrieve all request parameters
        $parameters = $request->all();

        // IF  no request parameters found
        // AND the session key exists (i.e. an previous URL has been saved)
        if (!count($parameters) && Session::has($key)) {
            // THEN redirect to the saved URL
            return redirect(Session::get($key));
        }

        // IF there are request parameters
        if (count($parameters)) {
            // THEN save them in the session
            Session::put($key, $request->fullUrl());
        }

        // Process and return the request
        return $next($request);
    }
}