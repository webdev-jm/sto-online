<div>

    {{-- ── TREND CHARTS ───────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SALES TREND &mdash; Last 18 Months</h3>
                </div>
                <div class="chart-sk">
                    <div class="chart-sk-shimmer"></div>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="hc-trends-sales-report" style="min-height:300px"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">INVENTORY LEVEL TREND &mdash; Last 18 Months</h3>
                </div>
                <div class="chart-sk">
                    <div class="chart-sk-shimmer"></div>
                </div>
                <div class="card-body" wire:ignore>
                    <div id="hc-trends-inventory-report" style="min-height:300px"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── GROWTH STAT CARDS ──────────────────────────────────────────── --}}
    <div class="stat-row mt-2">

        <div class="stat-card stat-col-3">
            <div class="stat-card-icon"><i class="fa fa-calendar-alt"></i></div>
            <div class="stat-card-body">
                <span class="stat-card-label">6-Month Growth</span>
                <span class="stat-card-value" style="color: {{ ($growth_stats['six_mo'] ?? 0) >= 0 ? '#28a745' : '#dc3545' }}">
                    @if($growth_stats['six_mo'] !== null)
                        {{ $growth_stats['six_mo'] >= 0 ? '+' : '' }}{{ number_format($growth_stats['six_mo'], 1) }}%
                    @else
                        N/A
                    @endif
                </span>
            </div>
        </div>

        <div class="stat-card stat-col-3">
            <div class="stat-card-icon"><i class="fa fa-calendar-week"></i></div>
            <div class="stat-card-body">
                <span class="stat-card-label">MoM Growth</span>
                <span class="stat-card-value" style="color: {{ ($growth_stats['mom'] ?? 0) >= 0 ? '#28a745' : '#dc3545' }}">
                    @if($growth_stats['mom'] !== null)
                        {{ $growth_stats['mom'] >= 0 ? '+' : '' }}{{ number_format($growth_stats['mom'], 1) }}%
                    @else
                        N/A
                    @endif
                </span>
            </div>
        </div>

        <div class="stat-card stat-col-3">
            <div class="stat-card-icon"><i class="fa fa-peso-sign"></i></div>
            <div class="stat-card-body">
                <span class="stat-card-label">Recent 6M Sales</span>
                <span class="stat-card-value">₱ {{ number_format($growth_stats['recent_total'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="stat-card stat-col-3">
            <div class="stat-card-icon"><i class="fa fa-history"></i></div>
            <div class="stat-card-body">
                <span class="stat-card-label">Prior 6M Sales</span>
                <span class="stat-card-value text-muted">₱ {{ number_format($growth_stats['prior_total'] ?? 0, 2) }}</span>
            </div>
        </div>

    </div>

    {{-- ── SKU TRENDS TABLE ───────────────────────────────────────────── --}}
    <div class="row mt-2">

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-arrow-up text-success mr-1"></i> TOP GROWING SKUs</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Description</th>
                                    <th class="text-right" title="{{ $prior_label }}">Prior 6M</th>
                                    <th class="text-right" title="{{ $recent_label }}">Recent 6M</th>
                                    <th class="text-right">Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sku_table['growers'] ?? [] as $row)
                                    <tr>
                                        <td class="text-nowrap small">{{ $row['sku'] }}</td>
                                        <td class="small">{{ $row['name'] }}</td>
                                        <td class="text-right small">{{ number_format($row['prev'], 0) }}</td>
                                        <td class="text-right small">{{ number_format($row['curr'], 0) }}</td>
                                        <td class="text-right font-weight-bold text-success small">+{{ $row['change_pct'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted small">No data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-arrow-down text-danger mr-1"></i> TOP DECLINING SKUs</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Description</th>
                                    <th class="text-right" title="{{ $prior_label }}">Prior 6M</th>
                                    <th class="text-right" title="{{ $recent_label }}">Recent 6M</th>
                                    <th class="text-right">Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sku_table['decliners'] ?? [] as $row)
                                    <tr>
                                        <td class="text-nowrap small">{{ $row['sku'] }}</td>
                                        <td class="small">{{ $row['name'] }}</td>
                                        <td class="text-right small">{{ number_format($row['prev'], 0) }}</td>
                                        <td class="text-right small">{{ number_format($row['curr'], 0) }}</td>
                                        <td class="text-right font-weight-bold text-danger small">{{ $row['change_pct'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted small">No data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@script
<script>
    const fmtSales = (val) => {
        if (val >= 1_000_000_000) return '₱ ' + Highcharts.numberFormat(val / 1_000_000_000, 2) + 'B';
        if (val >= 1_000_000)     return '₱ ' + Highcharts.numberFormat(val / 1_000_000, 2) + 'M';
        if (val >= 1_000)         return '₱ ' + Highcharts.numberFormat(val / 1_000, 2) + 'K';
        return '₱ ' + Highcharts.numberFormat(val, 2);
    };

    const fmtUnits = (val) => {
        if (val >= 1_000_000) return Highcharts.numberFormat(val / 1_000_000, 2) + 'M units';
        if (val >= 1_000)     return Highcharts.numberFormat(val / 1_000, 2) + 'K units';
        return Highcharts.numberFormat(val, 0) + ' units';
    };

    const lineConfig = (data, yTitle, fmt) => ({
        credits: { enabled: false },
        chart: { type: 'line' },
        title: { text: null },
        accessibility: { enabled: false },
        xAxis: { categories: data.categories, crosshair: true, labels: { rotation: -45, style: { fontSize: '10px' } } },
        yAxis: { title: { text: yTitle } },
        legend: { enabled: false },
        plotOptions: { line: { marker: { enabled: true, radius: 3 } } },
        tooltip: {
            formatter: function () {
                return `<b>${this.x}</b><br>${fmt(this.y)}`;
            }
        },
        series: data.series,
    });

    let salesChart = Highcharts.chart('hc-trends-sales-report', lineConfig($wire.sales_chart_data, 'Sales (₱)', fmtSales));
    let invChart   = Highcharts.chart('hc-trends-inventory-report', lineConfig($wire.inventory_chart_data, 'Inventory (Units)', fmtUnits));

    $wire.on('update-trends-sales-report', (event) => {
        salesChart.destroy();
        salesChart = Highcharts.chart('hc-trends-sales-report', lineConfig(event.data, 'Sales (₱)', fmtSales));
    });

    $wire.on('update-trends-inventory-report', (event) => {
        invChart.destroy();
        invChart = Highcharts.chart('hc-trends-inventory-report', lineConfig(event.data, 'Inventory (Units)', fmtUnits));
    });
</script>
@endscript
