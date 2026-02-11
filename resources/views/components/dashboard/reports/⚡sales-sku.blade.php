<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY SKU</h3>
        </div>
        <div class="card-body">
            <div id="container3"></div>
        </div>
    </div>
</div>

@assets
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
@endassets

@script
<script>
    Highcharts.chart('container3', {
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Ferry passengers by vehicle type 2024',
            align: 'left'
        },
        xAxis: {
            categories: [
                'January', 'February', 'March', 'April', 'May'
            ]
        },
        yAxis: {
            min: 0,
            title: {
                text: ''
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
            name: 'Motorcycles',
            data: [74, 27, 52, 93, 1272]
        }, {
            name: 'Null-emission vehicles',
            data: [2106, 2398, 3046, 3195, 4916]
        }, {
            name: 'Conventional vehicles',
            data: [12213, 12721, 15242, 16518, 25037]
        }]
    });

</script>
@endscript
