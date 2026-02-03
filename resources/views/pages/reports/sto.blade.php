@extends('adminlte::page')

@section('title', 'Reports')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>REPORTS</h1>
    </div>
    <div class="col-lg-6 text-right">
    </div>
</div>
@stop

@section('content')
    <livewire:reports.sto/>

    <div class="card">
        <div class="card-body">
            <pre>{{ $response ?? '' }}</pre>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    $(function() {

    });
</script>
@stop
