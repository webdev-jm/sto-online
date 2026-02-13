@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REPORTS</h3>
            <div class="card-tools">
                <button class="btn btn-xs btn-success">
                    EXCEL
                </button>
                <button class="btn btn-xs btn-warning">
                    JSON
                </button>
                <button class="btn btn-xs btn-danger">
                    XML
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="year">YEAR</label>
                        <input type="number" id="year" class="form-control form-control-sm" value="{{ date('Y') }}" class="form-contol form-control-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            {{-- <livewire:dashboard.reports.sales-performance /> --}}
        </div>

        <div class="col-lg-6">
            {{-- <livewire:dashboard.reports.sales-volume /> --}}
        </div>

        <div class="col-lg-6">
            {{-- <livewire:dashboard.reports.sales-sku /> --}}
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TOP SKU BASED ON SALES</h3>
                </div>
                <div class="card-body">
                    <livewire:dashboard.reports.sales-sku-total />
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SALES BY BRAND</h3>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SALES VS TARGET</h3>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ENDING INVENTORY</h3>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">INVENTORY AGING</h3>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .dark-mode .highcharts-background {
            fill: black;
        }
        .dark-mode .highcharts-title {
            color: white !important;
            fill: white !important;
        }
        .dark-mode .highcharts-subtitle {
            color: white !important;
            fill: white !important;
        }
        .dark-mode .highcharts-axis-title,
        .dark-mode .highcharts-axis-labels > text,
        .dark-mode .highcharts-legend-item > text {
            color: white !important;
            fill: white !important;
        }

        .dark-mode .highcharts-markers > path {
            fill: rgb(255, 128, 0) !important;
            stroke:rgb(235, 148, 17) !important;
        }

        .dark-mode .highcharts-series > rect {
            fill:rgb(0, 195, 249) !important;
        }
        .dark-mode .highcharts-graph {
            stroke: rgb(235, 148, 17);
        }

        .dark-mode .highcharts-series-1 .highcharts-point {
            fill: rgb(255, 128, 0) !important;
        }
        .dark-mode .highcharts-series-0 .highcharts-point {
            fill:rgb(0, 195, 249) !important;
        }


        .dark-mode .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .dark-mode .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .dark-mode.highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
        }

        .dark-mode .highcharts-data-table td,
        .dark-mode .highcharts-data-table th,
        .dark-mode .highcharts-data-table caption {
            padding: 0.5em;
        }

        .dark-mode .highcharts-data-table thead tr,
        .dark-mode .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .dark-mode .highcharts-data-table tr:hover {
            background: #f1f7ff;
        }
</style>
@stop

@section('js')
<script>

</script>
@stop
