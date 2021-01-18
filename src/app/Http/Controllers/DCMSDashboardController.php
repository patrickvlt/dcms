<?php

namespace App\Http\Controllers;

class DCMSDashboardController extends Controller
{
    public function generate()
    {
        return view('dcms::generate');
    }

    public function index()
    {
        return view('dcms::index');
    }
}
