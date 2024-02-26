<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\AccountBranch;
use App\Models\Salesman;

class SalesmanController extends Controller
{
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'BRANCH_KEY' => [
                'required'
            ],
            'code' => [
                'required'
            ],
            'name' => [
                'required'
            ]
        ]);
    }
}
