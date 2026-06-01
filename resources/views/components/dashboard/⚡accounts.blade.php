<?php

use Livewire\Component;
use App\Models\Account;

new class extends Component
{
    public $date_from;
    public $date_to;
    public $accounts;
    public $selected_account;

    public function mount($year): void
    {
        $this->date_from = $year . '-01';
        $this->date_to   = $year . '-12';
        $this->accounts  = Account::where('id', '>=', '10')->get();
    }
};
?>

<div>
    {{-- FILTER --}}
    <div class="row">
        <div class="col-lg-3">
            <label class="form-label">From</label>
            <input type="month" class="stat-year-input" wire:model.live="date_from">
        </div>

        <div class="col-lg-3">
            <label class="form-label">To</label>
            <input type="month" class="stat-year-input" wire:model.live="date_to">
        </div>

        <div class="col-lg-4">
            <label class="form-label">Account</label>
            <select class="stat-year-input" wire:model.live="selected_account">
                <option value="">All Accounts</option>
                @foreach ($this->accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->short_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- REPORTS --}}
    <div class="row mt-4">
        {{-- <div class="col-lg-6">
            <livewire:dashboard.accounts.sales-per-salesman :year="$year" :account_id="$selected_account"/>
        </div> --}}
        <div class="col-lg-6">
            <livewire:dashboard.accounts.sales-per-salesman-type :date_from="$date_from" :date_to="$date_to" :account_id="$selected_account"/>
        </div>
        {{-- <div class="col-lg-6">
            <livewire:dashboard.accounts.sales-per-address :year="$year" :account_id="$selected_account"/>
        </div> --}}
        <div class="col-lg-6">
            <livewire:dashboard.accounts.productivity-calls :date_from="$date_from" :date_to="$date_to" :account_id="$selected_account"/>
        </div>
    </div>

    <hr>

    {{-- TRENDS --}}
    <div class="row mt-4">
        <div class="col-lg-6">
            <livewire:dashboard.reports.trends-sales :date_from="$date_from" :date_to="$date_to" :account_id="$selected_account"/>
        </div>
        <div class="col-lg-6">
            <livewire:dashboard.reports.trends-inventory :date_from="$date_from" :date_to="$date_to" :account_id="$selected_account"/>
        </div>
    </div>
    <livewire:dashboard.reports.trends-growth :date_from="$date_from" :date_to="$date_to" :account_id="$selected_account"/>
</div>
