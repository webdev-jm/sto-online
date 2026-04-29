<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HubAdminController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('pages.admin.hub');
    }
}
