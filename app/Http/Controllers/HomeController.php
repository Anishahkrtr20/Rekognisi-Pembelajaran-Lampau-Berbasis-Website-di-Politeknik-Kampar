<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index(){
        $title = "SIRPL Politeknik Kampar";
        return view('Home/index', compact('title'));
    }
}
