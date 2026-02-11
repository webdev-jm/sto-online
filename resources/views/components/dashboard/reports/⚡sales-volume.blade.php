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
            <h3 class="card-title">MONTHLY SALES VOLUME</h3>
        </div>
        <div class="card-body">
            <div id="container2"></div>
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
    Highcharts.chart('container2', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Monthly Sales Volume'
        },
        xAxis: {
            categories: [
                'January', 'February', 'March', 'April', 'May',
                'June', 'July', 'August', 'September', 'October',
                'November', 'December'
            ],
            title: {
                text: 'Months'
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Total Quantity Sold'
            }
        },
        series: [{
            name: 'Sales Volume',
            data: [150, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650, 700] // Sample data
        }]
    });
</script>
@endscript
