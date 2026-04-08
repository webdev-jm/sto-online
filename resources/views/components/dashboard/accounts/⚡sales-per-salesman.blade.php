<?php

use Livewire\Component;

new class extends Component
{
    public $year;
    public $account_id;

    public function mount($year, $account_id)
    {
        $this->year = $year;
        $this->account_id = $account_id;
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY SALESMAN {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-account-salesmen"></div>
        </div>
    </div>
</div>

@script
<script>

    Highcharts.chart('container-account-salesmen', {
        credits: { enabled: false },
        chart: {
            type: 'pie'
        },
        title: { text: null },
        legend: { enabled: false },
        accessibility: { announceNewData: { enabled: true } },
        tooltip: {
            headerFormat: '',
            pointFormat:
                '<span style="color:{point.color}">\u25cf</span> ' +
                '{point.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                borderWidth: 2,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b><br>{point.percentage}%',
                    distance: 20
                }
            }
        },
        series: [{
            // Disable mouse tracking on load, enable after custom animation
            enableMouseTracking: false,
            animation: {
                duration: 2000
            },
            colorByPoint: true,
            data: [{
                name: 'Customer Support',
                y: 21.3
            }, {
                name: 'Development',
                y: 18.7
            }, {
                name: 'Sales',
                y: 20.2
            }, {
                name: 'Marketing',
                y: 14.2
            }, {
                name: 'Other',
                y: 25.6
            }]
        }]
    });
</script>
@endscript
