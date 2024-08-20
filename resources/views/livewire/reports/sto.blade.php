<div>
    <div class="card">
        <div class="card-body">

            <div class="mb-2">
                <ul class="pagination pagination-month justify-content-center">
                    <li class="page-item"><a class="page-link" href="#" wire:click.prevent="selectDate({{$month - 1}})">«</a></li>
                    @for($i = 1; $i <= 12; $i++)
                        <li class="page-item {{$month == $i ? 'active' : ''}}">
                            <a class="page-link" href="#" wire:click.prevent="selectDate({{$i}})">
                                <p class="page-month">{{date('M', strtotime($year.'-'.$i.'-01'))}}</p>
                                <p class="page-year">{{$year}}</p>
                            </a>
                        </li>
                    @endfor
                    <li class="page-item"><a class="page-link" href="#" wire:click.prevent="selectDate({{$month + 1}})">»</a></li>
                </ul>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INVENTORY REPORTS</h3>
        </div>
        <div class="card-body">

            <div class="row">

                <div class="col-lg-3">
                    <button class="btn {{$report == 'account' ? 'btn-primary' : 'btn-default'}} btn-block" wire:click.prevent="selectReport('account')">
                        PER ACCOUNT
                    </button>
                </div>

                <div class="col-lg-3">
                    <button class="btn {{$report == 'channel' ? 'btn-primary' : 'btn-default'}} btn-block" wire:click.prevent="selectReport('channel')">
                        PER CHANNEL
                    </button>
                </div>

                <div class="col-lg-3">
                    <button class="btn {{$report == 'brand' ? 'btn-primary' : 'btn-default'}} btn-block" wire:click.prevent="selectReport('brand')">
                        PER BRAND
                    </button>
                </div>

                <div class="col-lg-3">
                    <button class="btn {{$report == 'ubo' ? 'btn-primary' : 'btn-default'}} btn-block" wire:click.prevent="selectReport('ubo')">
                        UBO
                    </button>
                </div>

            </div>
            
        </div>
    </div>
    
    @switch($report)
        @case('account')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PER ACCOUNT</h3>
                    <div class="card-tools">
                        
                    </div>
                </div>
                <div class="card-body">
                    
                    @if(empty($drilldown_data) || (!empty($drilldown_data['account']) && $drilldown_data['account']['status'] == 'closed'))
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ACCOUNT</th>
                                        <th class="text-right">INVENTORY QTY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($account_data as $code => $data)
                                        <tr>
                                            <td>
                                                <a href="#" wire:click.prevent="loadDrillDown('account', {{$data['account_id']}})">
                                                    [{{$code}}] {{$data['short_name']}}
                                                </a>
                                            </td>
                                            <td class="text-right">
                                                {{number_format($data['total_inventory'])}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <button class="btn btn-xs btn-default mb-2" wire:click.prevent="closeDrillDown('account')">
                            <i class="fa fa-arrow-left"></i>
                            BACK
                        </button>

                        <div class="card card-outline card-primary mb-0">
                            <div class="card-header">
                                <h3 class="card-title">{{$drilldown_data['account']['account']['account_code']}} {{$drilldown_data['account']['account']['short_name']}}</h3>
                            </div>
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>BRAND</th>
                                                <th>TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($drilldown_data['account']['data'] as $brand => $val)
                                                <tr>
                                                    <td>{{$brand}}</td>
                                                    <td>{{number_format($val['total'])}}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <h3></h3>
                    @endif

                </div>
            </div>
        @break

        @case('channel')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PER CHANNEL</h3>
                </div>
                <div class="card-body">
                    
                </div>
            </div>
        @break

        @case('brand')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PER BRAND</h3>
                </div>
                <div class="card-body">

                    
                    <figure class="highcharts-figure">
                        <div id="container"></div>
                        <p class="highcharts-description">
                            Pie charts are very popular for showing a compact overview of a
                            composition or comparison. While they can be harder to read than
                            column charts, they remain a popular choice for small datasets.
                        </p>
                    </figure>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>BRAND</th>
                                    <th>TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($brand_data))
                                    @foreach($brand_data as $brand => $data)
                                        <tr>
                                            <td>{{$brand}}</td>
                                            <td>{{number_format($data['total'])}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        @break

        @case('ubo')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PER UBO</h3>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ACCOUNT CODE</th>
                                    <th>ACCOUNT NAME</th>
                                    <th>UBO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ubo_data as $code => $data)
                                    <tr>
                                        <td>{{$code}}</td>
                                        <td>{{$data['short_name']}}</td>
                                        <td>{{number_format($data['ubo'])}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        @break

    @endswitch

    <script>
        document.addEventListener('livewire:load', function() {

            window.addEventListener('update-chart', e => {
                Highcharts.chart('container', {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: 'BRAND SOB'
                    },
                    tooltip: {
                        valueSuffix: '%'
                    },
                    subtitle: {
                        text:
                        ''
                    },
                    plotOptions: {
                        series: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: [{
                                enabled: true,
                                distance: 20
                            }, {
                                enabled: true,
                                distance: -40,
                                format: '{point.percentage:.1f}%',
                                style: {
                                    fontSize: '1.2em',
                                    textOutline: 'none',
                                    opacity: 0.7
                                },
                                filter: {
                                    operator: '>',
                                    property: 'percentage',
                                    value: 10
                                }
                            }]
                        }
                    },
                    series: [
                        {
                            name: 'Quantity',
                            colorByPoint: true,
                            data: e.detail.data
                        }
                    ]
                });
            });
        });
    </script>

</div>
