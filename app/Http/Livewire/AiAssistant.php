<?php

namespace App\Http\Livewire;

use App\Exceptions\AiUnavailableException;
use App\Services\OllamaService;
use App\Services\RagService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AiAssistant extends Component
{
    public bool $isOpen = false;
    public bool $isLoading = false;
    public bool $insightsGenerated = false;
    public string $userInput = '';
    public string $error = '';

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    private string $systemPrompt = '';

    public function mount(): void
    {
        $user          = auth()->user();
        $accountBranch = session('account_branch');
        $account       = session('account') ?? $accountBranch?->account;

        $context  = "You are an AI assistant embedded in (Beauty Elements Ventures) BEV Portal — a sales and inventory management system used by FMCG distributors in the Philippines.\n\n";

        $context .= "## Your role\n"
            . "- Answer questions about sales performance, inventory levels, purchase orders, stock transfers, customers, and salesmen.\n"
            . "- Help users navigate and understand the data shown in BEV Portal.\n"
            . "- When business data is provided to you, use it directly in your answer.\n"
            . "- If you do not have enough data to answer accurately, say so clearly instead of guessing.\n"
            . "- Do NOT answer questions unrelated to the business (e.g. general knowledge, coding, other topics).\n\n";

        $context .= "## BEV Portal modules\n"
            . "Sales, Inventory, Purchase Orders, Stock Transfers, Return to Vendor (RTV), "
            . "Customers, Salesmen, Channels, Locations, Reports (VMI, STO), Upload Templates.\n\n";

        $context .= "## REST API\n"
            . "BEV Portal exposes a REST API (auth: Sanctum Bearer token). Resources: "
            . "sales, inventory, customer, salesman, location, channel, area, district, branches. "
            . "Each resource follows the pattern: GET /api/{resource} (list), POST /api/{resource}/create, "
            . "GET /api/{resource}/{id}/get, POST /api/{resource}/{id}/update. "
            . "Login: POST /api/login. Full endpoint details are in the knowledge base — retrieve them when asked.\n\n";

        $context .= "## Current session\n"
            . "User: {$user->name}\n";

        if ($accountBranch) {
            $context .= "Account: [{$account->account_code}] {$account->short_name}\n"
                . "Branch: [{$accountBranch->code}] {$accountBranch->name}\n";
        } elseif ($account) {
            $context .= "Account: [{$account->account_code}] {$account->short_name}\n";
        }

        if ($account) {
            $context .= $this->buildDataSummary($account->account_code);
        }

        $context .= "\n## Instructions\n"
            . "Be concise. Use bullet points for lists. Format currency as PHP #,###.##. "
            . "Always base answers on the data provided — do not fabricate numbers.";

        $this->systemPrompt = $context;
    }

    private function buildDataSummary(string $accountCode): string
    {
        $summary = "\n## Live data snapshot\n";

        try {
            $sales = DB::connection('sqlite_reports')
                ->table('sales_data')
                ->where('account_code', $accountCode)
                ->selectRaw('year, month, SUM(sales) as total_sales, SUM(quantity) as total_qty, COUNT(DISTINCT customer_code) as customers, COUNT(DISTINCT stock_code) as skus')
                ->groupBy('year', 'month')
                ->orderByDesc('year')->orderByDesc('month')
                ->first();

            if ($sales) {
                $summary .= "Latest sales period: {$sales->year}-" . str_pad($sales->month, 2, '0', STR_PAD_LEFT) . "\n"
                    . "- Total sales: PHP " . number_format($sales->total_sales, 2) . "\n"
                    . "- Total quantity sold: " . number_format($sales->total_qty, 2) . "\n"
                    . "- Active customers: {$sales->customers}\n"
                    . "- SKUs sold: {$sales->skus}\n";
            }

            $inventory = DB::connection('sqlite_reports')
                ->table('inventory_data')
                ->where('account_code', $accountCode)
                ->selectRaw('COUNT(DISTINCT stock_code) as skus, COUNT(DISTINCT location_code) as locations, SUM(total) as total_units')
                ->first();

            if ($inventory && $inventory->skus) {
                $summary .= "Current inventory: {$inventory->skus} SKUs across {$inventory->locations} location(s), "
                    . number_format($inventory->total_units, 2) . " total units\n";
            }

            $aging = DB::connection('sqlite_reports')
                ->table('inventory_aging')
                ->where('account_code', $accountCode)
                ->whereRaw("expiry_date <= date('now', '+90 days')")
                ->count();

            if ($aging > 0) {
                $summary .= "Near-expiry items (within 90 days): {$aging} SKU(s)\n";
            }
        } catch (\Throwable) {
            // sqlite_reports may not have data for this account yet
        }

        return $summary;
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen && !$this->insightsGenerated) {
            $this->generateInsights();
        }
    }

    public function generateInsights(): void
    {
        $this->isLoading = true;

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            ['role' => 'user', 'content' => 'Briefly introduce yourself and let me know how you can help me with (Beauty Elements Ventures) BEV Portal.'],
        ];

        try {
            $reply = app(OllamaService::class)->chat($messages);
        } catch (AiUnavailableException) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'I\'m unable to connect to the AI service right now. Please ensure Ollama is running and try again.'];
            $this->isLoading = false;
            return;
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $reply];
        $this->insightsGenerated = true;
        $this->isLoading = false;
    }

    public function sendMessage(): void
    {
        $input = trim($this->userInput);

        if (empty($input)) {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $input];
        $this->userInput  = '';
        $this->isLoading  = true;

        // RAG: retrieve relevant chunks from the reporting SQLite
        $systemPrompt = $this->systemPrompt;
        $account      = session('account');
        if ($account) {
            $chunks = app(RagService::class)->retrieve($input, $account->account_code);
            if (!empty($chunks)) {
                $systemPrompt .= "\n\nRelevant business data:\n" . implode("\n---\n", $chunks);
            }
        }

        $history = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $this->messages
        );

        try {
            $aiReply = app(OllamaService::class)->chat($history);
        } catch (AiUnavailableException) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'I\'m unable to connect to the AI service right now. Please ensure Ollama is running and try again.'];
            $this->isLoading  = false;
            return;
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $aiReply];
        $this->isLoading  = false;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.ai-assistant');
    }
}
