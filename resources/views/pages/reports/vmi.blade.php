@extends('adminlte::page')

@section('title', 'Reports - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - REPORTS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('report.index')}}" class="btn btn-primary btn-sm">
            <i class="fa fa-chart-bar mr-1"></i>
            SALES REPORT
        </a>
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <livewire:reports.vmi :account_branch="$account_branch"/>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    $(function() {

    });
</script>
@stop
