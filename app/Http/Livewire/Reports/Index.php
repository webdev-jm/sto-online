<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Sale;

use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_branch;
    public $date_from;
    public $date_to;

    public function exportData() {
        $sales = Sale::with('customer', 'salesman', 'channel', 'location')
            ->where('account_branch_id', $this->account_branch->id)
            ->where('date', '>=', $this->date_from)
            ->where('date', '<=', $this->date_to)
            ->get();

        return Excel::download(new SalesReportExport($sales), 'STO Sales Data-'.time().'.xlsx');
    }

    public function updatedDateFrom() {
        $this->resetPage('sales-page');
    }

    public function updatedDateTo() {
        $this->resetPage('sales-page');
    }

    public function clearFilter() {
        $this->date_from = date('Y-m').'-01';
        $this->date_to = date('Y-m-t');
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;
        // get current date month
        $this->date_from = date('Y-m').'-01';
        $this->date_to = date('Y-m-t');
    }
    
    public function render()
    {
        $sales = Sale::with('customer', 'salesman', 'channel', 'location')
            ->where('account_branch_id', $this->account_branch->id)
            ->where('date', '>=', $this->date_from)
            ->where('date', '<=', $this->date_to)
            ->paginate(15, ['*'], 'sales-page')
            ->onEachSide(1);

        return view('livewire.reports.index')->with([
            'sales' => $sales
        ]);
    }
}
