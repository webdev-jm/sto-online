@extends('adminlte::page')

@section('title', 'Account Dashboard')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>DASHBOARD &mdash; {{ $account->account_name }}</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{ route('branches', encrypt($account->id)) }}" class="btn-change-branch">
            <i class="fa fa-store mr-2"></i>Change Branch
        </a>
        <a href="{{ route('home') }}" class="btn-change-branch ml-2">
            <i class="fa fa-user mr-2"></i>Change Account
        </a>
    </div>
</div>
@stop

@section('content')
    <livewire:dashboard.account-report :account="$account" />
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>a
<script src="{{ asset('vendor/highcharts/modules/data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
@stop
