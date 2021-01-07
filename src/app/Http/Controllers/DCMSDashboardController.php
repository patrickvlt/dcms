<?php

namespace App\Http\Controllers;

class DCMSDashboardController extends Controller
{
    public function generate()
    {
        return view('dcms::generate');
    }
}
