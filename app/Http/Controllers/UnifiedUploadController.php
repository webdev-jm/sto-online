<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use App\Http\Traits\AccountChecker;

class UnifiedUploadController extends Controller
{
    use AccountChecker;

    public function index(): View|RedirectResponse
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof RedirectResponse) {
            return $account_branch;
        }

        return view('pages.uploads.index')->with([
            'account'        => Session::get('account'),
            'account_branch' => $account_branch,
        ]);
    }
}
