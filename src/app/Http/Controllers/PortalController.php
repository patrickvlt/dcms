<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Http\Controllers\Controller;

class PortalController extends Controller
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
