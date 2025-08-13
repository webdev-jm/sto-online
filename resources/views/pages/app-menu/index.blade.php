@extends('adminlte::page')

@section('title', 'APP MENU - '.$account->short_name.' - ['.$account_branch->code.'] '.$account_branch->name)

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}}</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('branches', encrypt($account->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-store mr-1"></i>Change Branch</a>
        </div>
    </div>
@stop

@section('content')
    <strong class="text-lg">REPORTS</strong>
    <hr class="mt-0">

    <div class="row">

        <div class="col-lg-3">
            <a href="{{route('report.index')}}" class="btn btn-block btn-app bg-gradient-maroon ml-0">
                <i class="fa fa-chart-line"></i>
                REPORTS
            </a>
        </div>

    </div>

    <strong class="text-lg">UPLOADS</strong>
    <hr class="mt-0">

    <div class="row">
        @can('sales access')
        <div class="col-lg-3">
            <a href="{{route('sales.index')}}" class="btn btn-block btn-app bg-gradient-olive ml-0">
                <i class="fa fa-money-check-alt"></i>
                SALES
            </a>
        </div>
        @endcan

        @can('inventory access')
        <div class="col-lg-3">
            <a href="{{route('inventory.index')}}" class="btn btn-block btn-app bg-gradient-indigo ml-0">
                <i class="fa fa-warehouse"></i>
                INVENTORY
            </a>
        </div>
        @endcan

        @can('purchase order access')
        <div class="col-lg-3">
            <a href="{{route('purchase-order.index')}}" class="btn btn-block btn-app bg-gradient-orange ml-0">
                <i class="fa fa-shopping-cart text-white"></i>
                <span class="text-white">PURCHASE ORDERS</span>
            </a>
        </div>
        @endcan

        @can('stock on hand access')
        {{-- 
            <div class="col-lg-3">
                <a href="{{route('stock-on-hand.index')}}" class="btn btn-block btn-app bg-gradient-gray ml-0">
                    <i class="fa fa-cubes text-white"></i>
                    <span class="text-white">STOCK ON HAND</span>
                </a>
            </div>
        --}}
        @endcan
        
        {{-- 
        <div class="col-lg-3">
            <a href="{{route('stock-transfer.index')}}" class="btn btn-block btn-app bg-gradient-purple ml-0">
                <i class="fa fa-exchange-alt text-white"></i>
                <span class="text-white">STOCK TRANSFER</span>
            </a>
        </div>
        --}}

        {{--
        <div class="col-lg-3">
            <a href="{{route('rtv.index')}}" class="btn btn-block btn-app bg-gradient-red ml-0">
                <i class="fa fa-undo text-white"></i>
                <span class="text-white">RTV</span>
            </a>
        </div>
        --}}

    </div>

    <strong class="text-lg">MAINTENANCE</strong>
    <hr class="mt-0">

    <div class="row">

        @can('location access')
        <div class="col-lg-3">
            <a href="{{route('location.index')}}" class="btn btn-block btn-app bg-gradient-info ml-0">
                <i class="fa fa-truck-loading"></i>
                LOCATION
            </a>
        </div>
        @endcan

        @can('area access')
        <div class="col-lg-3">
            <a href="{{route('area.index')}}" class="btn btn-block btn-app bg-gradient-success ml-0">
                <i class="fa fa-map-marked-alt"></i>
                AREAS
            </a>
        </div>
        @endcan

        @can('district access')
        <div class="col-lg-3">
            <a href="{{route('district.index')}}" class="btn btn-block btn-app bg-gradient-warning ml-0">
                <i class="fa fa-map"></i>
                DISTRICT
            </a>
        </div>
        @endcan

        @can('salesman access')
        <div class="col-lg-3">
            <a href="{{route('salesman.index')}}" class="btn btn-block btn-app bg-gradient-danger ml-0">
                <i class="fa fa-user-tie"></i>
                SALESMAN
            </a>
        </div>
        @endcan

        @can('customer access')
        <div class="col-lg-3">
            <a href="{{route('customer.index')}}" class="btn btn-block btn-app bg-gradient-primary ml-0">
                <i class="fa fa-people-carry"></i>
                CUSTOMERS
            </a>
        </div>
        @endcan
        
        @can('channel access')
        <div class="col-lg-3">
            <a href="{{route('channel.index')}}" class="btn btn-block btn-app bg-gradient-navy ml-0">
                <i class="fa fa-route"></i>
                CHANNELS
            </a>
        </div>
        @endcan

        @can('template converter access')
        {{--
        <div class="col-lg-3">
            <a href="{{route('template-converter.index')}}" class="btn btn-block btn-app bg-gradient-navy ml-0">
                <i class="fa fa-route"></i>
                TEMPLATE CONVERTER
            </a>
        </div>
        --}}
        @endcan
        
        
    </div>

    <strong class="text-lg">DASHBOARD</strong>
    <hr class="mt-0">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dashboard</h3>
        </div>
        <div class="card-body">

            {{ Form::open(['method' => 'GET', 'route' => ['menu', encrypt($account_branch->id)], 'id' => 'chart-form']) }}

            <div class="row mb-2">
                <div class="col-lg-3 col-sm-8">
                    {{ Form::number('year', $year, ['class' => 'form-control', 'form' => 'chart-form', 'placeholder' => 'Year'])}}
                </div>
                <div class="col-lg-3">
                    <button type="submit" class="btn btn-primary" form="chart-form">
                        <i class="fa fa-filter mr-1"></i>
                        FILTER
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <figure class="highcharts-figure">
                        <div id="container"></div>
                        <p class="highcharts-description text-center">
                            Basic reports of sales data base on uploaded sales data.
                        </p>
                    </figure>
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
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    $(function() {
        Highcharts.chart('container', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'STO',
                align: 'left'
            },
            subtitle: {
                text: 'Uploaded sales to outlet',
                align: 'left'
            },
            xAxis: {
                type: 'category'
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Amount'
                }
            },
            tooltip: {
                valueSuffix: ''
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: @php echo json_encode($chart_data); @endphp,
            drilldown: {
                series: @php echo json_encode($drilldown); @endphp
            }
        });
    });
</script>
@stop
