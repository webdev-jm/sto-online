@extends('adminlte::page')

@section('title', 'APP MENU - '.$account->short_name.' - ['.$account_branch->code.'] '.$account_branch->name)

@section('content_header')
    <div class="page-header-bar">
        <div class="page-header-left">
            <div class="page-header-badge">{{ $account->account_code }}</div>
            <div class="page-header-info">
                <h1 class="page-header-title">{{ $account->short_name }}</h1>
                <span class="page-header-sub">
                    <i class="fa fa-code-branch mr-1"></i>
                    {{ '['.$account_branch->code.'] '.$account_branch->name }}
                </span>
            </div>
        </div>
        <a href="{{ route('branches', encrypt($account->id)) }}" class="btn-change-branch">
            <i class="fa fa-store mr-2"></i>Change Branch
        </a>
    </div>
@stop

@section('content')

    {{-- REPORTS --}}
    <div class="menu-section">
        <div class="section-label">
            <span class="section-label-text">Reports</span>
            <div class="section-label-line"></div>
        </div>
        <div class="menu-grid">
            <a href="{{ route('report.index') }}" class="menu-tile tile-maroon">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-chart-line"></i></div>
                <div class="tile-content">
                    <span class="tile-title">REPORTS</span>
                    <span class="tile-desc">View sales & inventory reports</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
        </div>
    </div>

    {{-- UPLOADS --}}
    <div class="menu-section">
        <div class="section-label">
            <span class="section-label-text">Uploads</span>
            <div class="section-label-line"></div>
        </div>
        <div class="menu-grid">

            @can('sales access')
            <a href="{{ route('sales.index') }}" class="menu-tile tile-olive">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-money-check-alt"></i></div>
                <div class="tile-content">
                    <span class="tile-title">SALES</span>
                    <span class="tile-desc">Upload sales data</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('inventory access')
            <a href="{{ route('inventory.index') }}" class="menu-tile tile-indigo">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-warehouse"></i></div>
                <div class="tile-content">
                    <span class="tile-title">INVENTORY</span>
                    <span class="tile-desc">Upload inventory data</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('purchase order access')
            <a href="{{ route('purchase-order.index') }}" class="menu-tile tile-orange">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-shopping-cart"></i></div>
                <div class="tile-content">
                    <span class="tile-title">PURCHASE ORDERS</span>
                    <span class="tile-desc">Track & upload POs</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

        </div>
    </div>

    {{-- MAINTENANCE --}}
    <div class="menu-section">
        <div class="section-label">
            <span class="section-label-text">Maintenance</span>
            <div class="section-label-line"></div>
        </div>
        <div class="menu-grid">

            @can('location access')
            <a href="{{ route('location.index') }}" class="menu-tile tile-cyan">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-truck-loading"></i></div>
                <div class="tile-content">
                    <span class="tile-title">LOCATION</span>
                    <span class="tile-desc">Manage warehouse locations</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('area access')
            <a href="{{ route('area.index') }}" class="menu-tile tile-green">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-map-marked-alt"></i></div>
                <div class="tile-content">
                    <span class="tile-title">AREAS</span>
                    <span class="tile-desc">Manage areas</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('district access')
            <a href="{{ route('district.index') }}" class="menu-tile tile-yellow">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-map"></i></div>
                <div class="tile-content">
                    <span class="tile-title">DISTRICT</span>
                    <span class="tile-desc">Configure district groups</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('salesman access')
            <a href="{{ route('salesman.index') }}" class="menu-tile tile-red">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-user-tie"></i></div>
                <div class="tile-content">
                    <span class="tile-title">SALESMAN</span>
                    <span class="tile-desc">Salesman management</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('customer access')
            <a href="{{ route('customer.index') }}" class="menu-tile tile-blue">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-people-carry"></i></div>
                <div class="tile-content">
                    <span class="tile-title">CUSTOMERS</span>
                    <span class="tile-desc">Customer records</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

            @can('channel access')
            <a href="{{ route('channel.index') }}" class="menu-tile tile-navy">
                <div class="tile-glow"></div>
                <div class="tile-icon"><i class="fa fa-route"></i></div>
                <div class="tile-content">
                    <span class="tile-title">CHANNELS</span>
                    <span class="tile-desc">Customer channels</span>
                </div>
                <i class="fa fa-chevron-right tile-arrow"></i>
            </a>
            @endcan

        </div>
    </div>

    {{-- DASHBOARD / CHART --}}
    <div class="menu-section">
        <div class="section-label">
            <span class="section-label-text">Dashboard</span>
            <div class="section-label-line"></div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div class="dashboard-card-title">
                    <i class="fa fa-chart-bar mr-2"></i>Sales to Outlet Overview
                </div>
                {{ Form::open(['method' => 'GET', 'route' => ['menu', encrypt($account_branch->id)], 'id' => 'chart-form', 'class' => 'chart-filter-form']) }}
                    <div class="chart-filter-group">
                        {{ Form::number('year', $year, ['class' => 'chart-year-input', 'form' => 'chart-form', 'placeholder' => 'Year']) }}
                        <button type="submit" class="chart-filter-btn" form="chart-form">
                            <i class="fa fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                {{ Form::close() }}
            </div>

            <div class="dashboard-card-body">
                <figure class="highcharts-figure">
                    <div id="container"></div>
                    <p class="chart-caption">Sales to outlet data based on uploaded records.</p>
                </figure>
            </div>
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

@stop

@section('js')
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/export-data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>

<script>
    $(function () {
        Highcharts.chart('container', {
            chart: {
                type: 'column',
                borderRadius: 8,
                style: { fontFamily: "'DM Sans', sans-serif" }
            },
            title: {
                text: 'Sales to Outlet (STO)',
                align: 'left',
                style: { fontFamily: "'Syne', sans-serif", fontWeight: '700', fontSize: '16px' }
            },
            subtitle: {
                text: 'Monthly breakdown from uploaded sales data',
                align: 'left',
                style: { fontSize: '12px' }
            },
            xAxis: { type: 'category' },
            yAxis: {
                min: 0,
                title: { text: 'Amount' },
                gridLineDashStyle: 'Dash'
            },
            tooltip: {
                backgroundColor: '#1a202c',
                borderColor: 'transparent',
                borderRadius: 8,
                style: { color: '#fff' },
                valueSuffix: ''
            },
            plotOptions: {
                column: {
                    pointPadding: 0.15,
                    borderWidth: 0,
                    borderRadius: 4
                }
            },
            legend: { align: 'right', verticalAlign: 'top' },
            credits: { enabled: false },
            series: @php echo json_encode($chart_data); @endphp,
            drilldown: {
                series: @php echo json_encode($drilldown); @endphp
            }
        });
    });
</script>
@stop
