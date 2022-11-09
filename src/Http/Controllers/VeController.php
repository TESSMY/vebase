<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class VeController extends Controller
{
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        dd(Route::currentRouteName(), Route::current(), Route::currentRouteAction());
    }

    public function index()
    {

    }

}
