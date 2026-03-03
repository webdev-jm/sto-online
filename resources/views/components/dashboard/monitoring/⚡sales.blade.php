<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Livewire\Attributes\Computed;
use App\Models\Account;
use App\Models\AccountDatabase;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination, WithoutUrlPagination;
    protected $paginationTheme = 'bootstrap';

    public $month;
    public $year;

    public function mount() {
        $this->month = date('m');
        $this->year = date('Y');
    }

    #[Computed]
    public function accounts() {
        return Account::where('id', '>=', 10)
            ->paginate(10);
    }

    public function checkSalesStatus($account_id) {
        $db_connection = AccountDatabase::where('account_id', $account_id)->first();

        try {
            $check = DB::connection($db_connection->connection_name)
            ->table('sales')
            ->where(DB::raw('MONTH(date)'), $this->month)
            ->where(DB::raw('YEAR(date)'), $this->year)
            ->first();

            return $check ? 'Has Data' : 'No Data';

        } catch (\Exception $e) {
            return 'No Data';
        }

    }

    public function checkInventoryStatus($account_id) {
        $db_connection = AccountDatabase::where('account_id', $account_id)->first();

        try {
            $check = DB::connection($db_connection->connection_name)
            ->table('inventory_uploads')
            ->where(DB::raw('MONTH(date)'), $this->month)
            ->where(DB::raw('YEAR(date)'), $this->year)
            ->first();

            return $check ? 'Has Data' : 'No Data';

        } catch (\Exception $e) {
            return 'No Data';
        }

    }

    // public function updated($property) {
    //     if (in_array($property, ['month', 'year'])) {
    //     }
    // }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES MONITORING<i class="fa fa-spinner fa-spin ml-1" wire:loading></i></h3>
            <div class="card-tools">
                <span class="badge badge-info">
                    {{ date('F Y', strtotime($year.'-'.$month.'-01')) }}
                </span>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="year">YEAR</label>
                        <input type="number" id="year" class="form-control form-control-sm" wire:model.live="year" class="form-contol form-control-sm">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="month">MONTH</label>
                        <input type="number" id="month" class="form-control form-control-sm" wire:model.live="month" class="form-contol form-control-sm">
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>ACCOUNT CODE</th>
                        <th>NAME</th>
                        <th>SHORT NAME</th>
                        <th>SALES</th>
                        <th>INVENTORY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->accounts as $account)
                        <tr>
                            <td>{{ $account->account_code }}</td>
                            <td>{{ $account->acount_name }}</td>
                            <td>{{ $account->short_name }}</td>
                            <td>
                                @if($this->checkSalesStatus($account->id) == 'Has Data')
                                    <span class="badge bg-success">Has Data</span>
                                @else
                                    <span class="badge bg-danger">No Data</span>
                                @endif
                            </td>
                            <td>
                                @if($this->checkInventoryStatus($account->id) == 'Has Data')
                                    <span class="badge bg-success">Has Data</span>
                                @else
                                    <span class="badge bg-danger">No Data</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $this->accounts->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</div>
