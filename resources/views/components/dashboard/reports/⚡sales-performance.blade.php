<?php

use Livewire\Component;
use App\Models\Account;

new class extends Component
{
    public $year;
    public $chart_data = [];
    public function mount() {
        $this->year = date('Y');

        $this->chart_data = $this->consolidateSalesData()[$this->year]['data'];
    }

    public function getSalesData($account) {
        $sales_monthly = null;
        $jsonPath = storage_path('app/reports/consolidated_account_data-'.$account->account_code.'.json');

        if (file_exists($jsonPath)) {
            $raw = json_decode(file_get_contents($jsonPath), true);
            $sales_monthly[] = collect($raw['sales_data'])
            ->groupBy('year') // This sets the keys to the year initially
            ->map(function ($months, $year) {
                return [
                    'name' => "Year $year",
                    'data' => $months->groupBy('month') // Group inner data by month
                        ->map(function ($items, $month) {
                            return [
                                'name' => DateTime::createFromFormat('!m', $month)->format('M'),
                                'y' => $items->sum(fn($i) => (float) $i['sales']),
                                'month_num' => (int) $month
                            ];
                        })
                        ->sortBy('month_num')
                        ->values() // We keep values() here so the 'data' array is a clean list for Highcharts
                        ->toArray()
                ];
            })
            ->toArray();

        }

        return $sales_monthly;
    }

    public function consolidateSalesData() {
        $account_data = [];
        $accounts = Account::get();
        foreach($accounts as $account) {
            $account_data[$account->account_code] = $this->getSalesData($account);
        }

        // consolidate all accounts
        $temp_consolidated = [];

        foreach ($account_data as $acc_code => $years) {
            foreach ($years[0] as $year => $year_info) {

                // Loop through the months in this year
                foreach ($year_info['data'] as $month_item) {
                    $month_num = $month_item['month_num'];

                    // Initialize if this year/month combo doesn't exist yet
                    if (!isset($temp_consolidated[$year][$month_num])) {
                        $temp_consolidated[$year][$month_num] = [
                            'name'      => $month_item['name'],
                            'y'         => 0, // Start at 0
                            'month_num' => $month_num
                        ];
                    }

                    // Sum the value
                    $temp_consolidated[$year][$month_num]['y'] += $month_item['y'];
                }
            }
        }


        // The chart library likely expects 'data' to be a list (indexed array), not a map.
        $final_output = [];

        foreach ($temp_consolidated as $year => $months) {
            // Optional: Sort by month number so the chart lines draw correctly from Jan -> Dec
            ksort($months);

            $final_output[$year] = [
                'name' => "Year " . $year,
                'data' => array_values($months) // Reset keys to 0, 1, 2...
            ];
        }

        return $final_output;
    }

    public function updated($property) {
        if($property === 'year') {
            $this->chartUpdated();
        }
    }

    public function chartUpdated() {
        $consolidated = $this->consolidateSalesData();

        // Safety check: ensure the year exists in the data, otherwise empty array
        $this->chart_data = isset($consolidated[$this->year])
            ? $consolidated[$this->year]['data']
            : [];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES PERPORMANCE</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container1"></div>
        </div>
    </div>
</div>
@assets
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
@endassets

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container1', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Browser market shares. January, 2022'
            },
            subtitle: {
                text: 'Click the columns to view versions. Source: <a href="http://statcounter.com" target="_blank">statcounter.com</a>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                }
            },
            xAxis: {
                type: 'category'
            },
            yAxis: {
                title: {
                    text: 'Total percent market share'
                }

            },
            legend: {
                enabled: false
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '₱ {point.y:,.2f}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: ' +
                    '<b>{point.y:,.2f}</b> total<br/>'
            },

            series: [
                {
                    name: 'MONTH SALES',
                    colorByPoint: true,
                    data: $wire.chart_data
                }
            ],
            drilldown: {
                breadcrumbs: {
                    position: {
                        align: 'right'
                    }
                },
                series: [
                    {
                        name: 'Chrome',
                        id: 'Chrome',
                        data: [
                            [
                                'v65.0',
                                0.1
                            ],
                            [
                                'v64.0',
                                1.3
                            ],
                            [
                                'v63.0',
                                53.02
                            ],
                            [
                                'v62.0',
                                1.4
                            ],
                            [
                                'v61.0',
                                0.88
                            ],
                            [
                                'v60.0',
                                0.56
                            ],
                            [
                                'v59.0',
                                0.45
                            ],
                            [
                                'v58.0',
                                0.49
                            ],
                            [
                                'v57.0',
                                0.32
                            ],
                            [
                                'v56.0',
                                0.29
                            ],
                            [
                                'v55.0',
                                0.79
                            ],
                            [
                                'v54.0',
                                0.18
                            ],
                            [
                                'v51.0',
                                0.13
                            ],
                            [
                                'v49.0',
                                2.16
                            ],
                            [
                                'v48.0',
                                0.13
                            ],
                            [
                                'v47.0',
                                0.11
                            ],
                            [
                                'v43.0',
                                0.17
                            ],
                            [
                                'v29.0',
                                0.26
                            ]
                        ]
                    },
                    {
                        name: 'Firefox',
                        id: 'Firefox',
                        data: [
                            [
                                'v58.0',
                                1.02
                            ],
                            [
                                'v57.0',
                                7.36
                            ],
                            [
                                'v56.0',
                                0.35
                            ],
                            [
                                'v55.0',
                                0.11
                            ],
                            [
                                'v54.0',
                                0.1
                            ],
                            [
                                'v52.0',
                                0.95
                            ],
                            [
                                'v51.0',
                                0.15
                            ],
                            [
                                'v50.0',
                                0.1
                            ],
                            [
                                'v48.0',
                                0.31
                            ],
                            [
                                'v47.0',
                                0.12
                            ]
                        ]
                    },
                    {
                        name: 'Internet Explorer',
                        id: 'Internet Explorer',
                        data: [
                            [
                                'v11.0',
                                6.2
                            ],
                            [
                                'v10.0',
                                0.29
                            ],
                            [
                                'v9.0',
                                0.27
                            ],
                            [
                                'v8.0',
                                0.47
                            ]
                        ]
                    },
                    {
                        name: 'Safari',
                        id: 'Safari',
                        data: [
                            [
                                'v11.0',
                                3.39
                            ],
                            [
                                'v10.1',
                                0.96
                            ],
                            [
                                'v10.0',
                                0.36
                            ],
                            [
                                'v9.1',
                                0.54
                            ],
                            [
                                'v9.0',
                                0.13
                            ],
                            [
                                'v5.1',
                                0.2
                            ]
                        ]
                    },
                    {
                        name: 'Edge',
                        id: 'Edge',
                        data: [
                            [
                                'v16',
                                2.6
                            ],
                            [
                                'v15',
                                0.92
                            ],
                            [
                                'v14',
                                0.4
                            ],
                            [
                                'v13',
                                0.1
                            ]
                        ]
                    },
                    {
                        name: 'Opera',
                        id: 'Opera',
                        data: [
                            [
                                'v50.0',
                                0.96
                            ],
                            [
                                'v49.0',
                                0.82
                            ],
                            [
                                'v12.1',
                                0.14
                            ]
                        ]
                    }
                ]
            }
        });
    }

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data);
    });
</script>
@endscript
