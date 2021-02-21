<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class AuthorizationController extends Controller
{
    public function index()
    {
        return view('dcms::authorization.index');
    }
}
