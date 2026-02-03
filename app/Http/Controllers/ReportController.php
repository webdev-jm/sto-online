<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Content;
use Illuminate\Support\Facades\File;

use App\Http\Traits\ConsolidateAccountData;

class ReportController extends Controller
{
    use ConsolidateAccountData;
    use AccountChecker;

    public function index() {

        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.reports.index')->with([
            'account_branch' => $account_branch,
            'account' => $account
        ]);
    }

    public function generateReport() {

        // $this->setConsolidatedAccountData();

        // 1. Get the data from your trait
        $json_path = storage_path('app/reports/consolidated_account_data.json');
        $jsonContext = File::get($json_path);

        // 2. Define the System Instruction (The 'Role')
        $systemInstruction = "
            You are an expert Business Intelligence Analyst.
            Your task is to analyze multi-tenant data and identify cross-account trends.
            Always look for anomalies like sudden revenue drops or top-performing products.
            Output your findings in professional Markdown.
        ";

        // 3. Send to Gemini
        $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
            ->withSystemInstruction(Content::parse($systemInstruction))
            ->generateContent([
                "Consolidated Tenant Data:",
                json_encode($jsonContext),
                "Generate the performance summary and global overview now."
            ]);

        return $response->text();
    }

    public function vmi_report() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.reports.vmi')->with([
            'account_branch' => $account_branch,
            'account' => $account
        ]);
    }

    public function sto_report() {
        $result = $this->generateReport();

        return view('pages.reports.sto')->with([
            'response' => $result
        ]);
    }
}
