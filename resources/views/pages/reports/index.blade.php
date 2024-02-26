@extends('adminlte::page')

@section('title', 'Roles')

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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REPORTS</h3>
        </div>
        <div class="card-body">

            <figure class="highcharts-figure">
                <div id="container"></div>
            </figure>

        </div>
        <div class="card-footer">
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
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    $(function() {
        Highcharts.setOptions({
            colors: ['rgba(5,141,199,0.5)', 'rgba(80,180,50,0.5)', 'rgba(237,86,27,0.5)']
        });

        const series = [{
            data: @php echo json_encode($data); @endphp,
            name: 'STY',
            id: 'ubo',
            marker: {
                symbol: 'circle'
            }
        }, {
            type: 'line',
            name: 'Trend Line',
            data: @php echo json_encode($line_data); @endphp,
            marker: {
                enabled: false
            },
            states: {
                hover: {
                    lineWidth: 0
                }
            },
            enableMouseTracking: true
        }];

        Highcharts.chart('container', {
            chart: {
                type: 'scatter',
                zoomType: 'xy'
            },
            title: {
                text: 'STY',
                align: 'left'
            },
            subtitle: {
                text:
            'Source:',
                align: 'left'
            },
            xAxis: {
                title: {
                    text: 'UBO'
                },
                labels: {
                    format: '{value}'
                },
                startOnTick: true,
                endOnTick: true,
                showLastLabel: true
            },
            yAxis: {
                title: {
                    text: 'STO'
                },
                labels: {
                    format: '{value}'
                }
            },
            legend: {
                enabled: true
            },
            plotOptions: {
                scatter: {
                    marker: {
                        radius: 2.5,
                        symbol: 'circle',
                        states: {
                            hover: {
                                enabled: true,
                                lineColor: 'rgb(100,100,100)'
                            }
                        }
                    },
                    states: {
                        hover: {
                            marker: {
                                enabled: false
                            }
                        }
                    },
                    jitter: {
                        x: 0.005
                    }
                }
            },
            tooltip: {
                pointFormat: 'UBO: {point.x} <br/> STO: {point.y}'
            },
            series
        });

    });
</script>
@stop
