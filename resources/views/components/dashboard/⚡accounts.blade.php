<?php

use Livewire\Component;
use App\Models\Account;

new class extends Component
{
    public $year;
    public $accounts;
    public $selected_account;

    public function mount($year)
    {
        $this->year = $year;
        $this->accounts = Account::where('id', '>=', '10')
            ->get();

        $this->selected_account = $this->accounts->first()->id ?? null;
    }
};
?>

<div>
    {{-- FILTER --}}
    <div class="row">
        <div class="col-lg-4">
            <label for="year" class="form-label">Year</label>
            <select id="year" class="stat-year-input" wire:model.live="year">
                @foreach (range(date('Y'), date('Y') - 5) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4">
            <label for="month" class="form-label">Account</label>
            <select id="month" class="stat-year-input" wire:model.live="selected_account">
                <option value="">All Accounts</option>
                @foreach ($this->accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->short_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- REPORTS --}}
    <div class="row mt-4">
        <div class="col-lg-6">
            <livewire:dashboard.accounts.sales-per-salesman :year="$year" :account_id="$selected_account"/>
        </div>
    </div>
</div>
