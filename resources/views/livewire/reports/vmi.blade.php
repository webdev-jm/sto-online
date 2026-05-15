<div>
    <style>
        /* ── Month group headers ─────────────────────────── */
        .bg-month-1 {
            background: linear-gradient(135deg, #0a84ff 0%, #5e5ce6 100%) !important;
            color: #fff !important;
        }
        .bg-month-2 {
            background: linear-gradient(135deg, #5856d6 0%, #32318c 100%) !important;
            color: #fff !important;
        }
        .bg-month-3 {
            background: linear-gradient(135deg, #af52de 0%, #5e2490 100%) !important;
            color: #fff !important;
        }
        .bg-month-6 {
            background: linear-gradient(135deg, #ff375f 0%, #a2003e 100%) !important;
            color: #fff !important;
        }

        /* ── AI recommendation column ────────────────────── */
        .bg-ai {
            background: linear-gradient(135deg, #ffd60a 0%, #ff9f0a 100%) !important;
            color: #1a1a1a !important;
        }
        .col-ai {
            background: rgba(255, 214, 10, 0.07) !important;
            font-weight: 700 !important;
        }
        .dark-mode .col-ai {
            background: rgba(255, 214, 10, 0.1) !important;
        }

        /* ── Table base ──────────────────────────────────── */
        .vmi-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: max-content !important;
            min-width: 100% !important;
        }

        /* ── Primary headers (Month 1 / 2 / 3 / 6) ──────── */
        .vmi-table thead tr:first-child th {
            font-family: 'Syne', sans-serif !important;
            font-weight: 800 !important;
            font-size: 0.56rem !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            padding: 6px 5px !important;
            border: none !important;
            white-space: nowrap !important;
        }

        /* Rowspan static columns in first header row */
        .vmi-table thead tr:first-child th[rowspan="2"] {
            background: rgba(0, 0, 0, 0.45) !important;
            backdrop-filter: blur(8px) !important;
            color: rgba(255, 255, 255, 0.75) !important;
            font-size: 0.52rem !important;
            vertical-align: middle !important;
            border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
        }

        /* ── Sub-headers (STO / WEEK COV / COV NEED / TO ORDER) */
        .vmi-table thead tr:last-child th {
            font-family: 'Syne', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.49rem !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            background: rgba(0, 0, 0, 0.28) !important;
            color: rgba(255, 255, 255, 0.918) !important;
            padding: 3px 5px !important;
            border: none !important;
            white-space: nowrap !important;
        }

        /* ── TO ORDER highlight column ───────────────────── */
        .col-highlight {
            background: rgba(255, 255, 255, 0.1) !important;
            color: rgba(255, 255, 255, 0.85) !important;
        }

        /* ── Group separator ─────────────────────────────── */
        .group-sep {
            border-left: 1px solid rgba(255, 255, 255, 0.12) !important;
        }

        /* ── Body rows ───────────────────────────────────── */
        .vmi-table tbody tr {
            transition: background 0.15s !important;
        }

        .vmi-table tbody tr:hover td {
            background: rgba(10, 132, 255, 0.05) !important;
        }

        .vmi-table tbody td {
            font-family: 'Figtree', sans-serif !important;
            font-size: 0.71rem !important;
            padding: 5px 7px !important;
            border-color: rgba(0, 0, 0, 0.04) !important;
            vertical-align: middle !important;
            color: var(--col-dark) !important;
        }

        /* Stock code accent */
        .vmi-table tbody td.text-accent {
            font-family: 'Syne', sans-serif !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em !important;
            color: var(--col-accent) !important;
        }

        /* TO ORDER cell highlight in body */
        .vmi-table tbody td.col-highlight {
            background: rgba(10, 132, 255, 0.04) !important;
            font-weight: 700 !important;
            font-family: 'Syne', sans-serif !important;
            font-size: 0.66rem !important;
        }

        .vmi-table tbody td.col-highlight.text-danger,
        .vmi-table tbody td.col-ai.text-danger {
            color: #dc3545 !important;
        }

        .vmi-table tbody td.col-highlight.text-success,
        .vmi-table tbody td.col-ai.text-success {
            color: #28a745 !important;
        }

        /* Separator on body cells too */
        .vmi-table tbody td.group-sep {
            border-left: 1px solid rgba(0, 0, 0, 0.06) !important;
        }

        /* ── AI reason cell ──────────────────────────────── */
        .ai-reason-cell {
            font-size: 0.62rem !important;
            color: var(--col-subtle) !important;
            max-width: 260px !important;
            min-width: 180px !important;
            white-space: normal !important;
            line-height: 1.35 !important;
        }

        /* ── Filter inputs ───────────────────────────────── */
        .vmi-filter-bar {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .vmi-filter-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .vmi-filter-label {
            font-family: 'Syne', sans-serif;
            font-size: 0.52rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--col-subtle);
        }

        .vmi-filter-input {
            width: 72px;
            padding: 5px 8px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.7);
            color: var(--col-dark);
            font-family: 'Figtree', sans-serif;
            font-size: 0.78rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .vmi-filter-input:focus {
            border-color: var(--col-accent);
            box-shadow: 0 0 0 3px rgba(10, 132, 255, 0.15);
        }

        .vmi-search-wrap {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            padding: 0 8px;
            gap: 5px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .vmi-search-wrap:focus-within {
            border-color: var(--col-accent);
            box-shadow: 0 0 0 3px rgba(10, 132, 255, 0.15);
        }

        .vmi-search-wrap i {
            color: var(--col-subtle);
            font-size: 0.75rem;
        }

        .vmi-search-input {
            border: none;
            outline: none;
            background: transparent;
            font-family: 'Figtree', sans-serif;
            font-size: 0.78rem;
            color: var(--col-dark);
            padding: 5px 0;
            width: 160px;
        }

        .vmi-search-input::placeholder {
            color: var(--col-subtle);
        }

        /* ── Footer meta ─────────────────────────────────── */
        .vmi-footer-meta {
            font-family: 'Syne', sans-serif;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--col-subtle);
        }

        /* ── Dark mode ───────────────────────────────────── */
        .dark-mode .vmi-table thead tr:first-child th[rowspan="2"] {
            background: rgba(0, 0, 0, 0.55) !important;
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .dark-mode .vmi-table thead tr:last-child th {
            background: rgba(255, 255, 255, 0.04) !important;
            color: rgba(255, 255, 255, 0.35) !important;
        }

        .dark-mode .vmi-table tbody td {
            color: rgba(255, 255, 255, 0.82) !important;
            border-color: rgba(255, 255, 255, 0.04) !important;
        }

        .dark-mode .vmi-table tbody tr:hover td {
            background: rgba(10, 132, 255, 0.08) !important;
        }

        .dark-mode .vmi-table tbody td.col-highlight {
            background: rgba(10, 132, 255, 0.08) !important;
        }

        .dark-mode .vmi-table tbody td.group-sep {
            border-left-color: rgba(255, 255, 255, 0.06) !important;
        }

        .dark-mode .vmi-filter-input {
            background: rgba(255, 255, 255, 0.06) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .dark-mode .vmi-search-wrap {
            background: rgba(255, 255, 255, 0.06) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .dark-mode .vmi-search-input {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .dark-mode .vmi-search-input::placeholder {
            color: rgba(255, 255, 255, 0.25) !important;
        }

        .dark-mode .vmi-footer-meta {
            color: rgba(255, 255, 255, 0.3) !important;
        }

        .dark-mode .ai-reason-cell {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        /* ── Wire loading ────────────────────────────────── */
        .vmi-table-wrap {
            position: relative;
        }

        .vmi-loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(242, 242, 247, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            z-index: 10;
            font-family: 'Syne', sans-serif;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--col-accent);
        }

        .vmi-loading-overlay i {
            font-size: 0.9rem;
        }

        /* Dim inputs while loading */
        .vmi-filter-bar.loading .vmi-filter-input,
        .vmi-filter-bar.loading .vmi-search-input {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Dark mode overlay */
        .dark-mode .vmi-loading-overlay {
            background: rgba(10, 10, 18, 0.65);
        }

        .text-danger {
            color: var(--col-danger) !important;
        }

        .text-success {
            color: var(--col-success) !important;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">VMI REPORT</h3>
            <div wire:loading class="ml-2" style="color: var(--col-accent); font-size: 0.78rem;">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>

        <div class="card-body p-2">

            {{-- Filter bar --}}
            <div class="vmi-filter-bar" wire:loading.class="loading">
                <div class="vmi-filter-group">
                    <span class="vmi-filter-label">Year</span>
                    <input type="number" class="vmi-filter-input" wire:model.live="year">
                </div>
                <div class="vmi-filter-group">
                    <span class="vmi-filter-label">Month</span>
                    <input type="number" class="vmi-filter-input" wire:model.live="month">
                </div>
                <div class="vmi-filter-group">
                    <span class="vmi-filter-label">Param</span>
                    <input type="number" class="vmi-filter-input" wire:model.live="parameter" max="12" min="1">
                </div>
                <div class="vmi-search-wrap">
                    <i class="fa fa-search" wire:loading.remove></i>
                    <i class="fa fa-spinner fa-spin" wire:loading style="color: var(--col-accent);"></i>
                    <input type="text" class="vmi-search-input" wire:model.live.blur="search" placeholder="Quick search..." wire:loading.attr="disabled">
                </div>
                <button wire:click="getAiRecommendations"
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-warning ml-auto"
                        style="font-family:'Syne',sans-serif; font-size:0.62rem; font-weight:700; letter-spacing:0.08em; border-radius:8px; padding:5px 11px;">
                    <span wire:loading.remove wire:target="getAiRecommendations">
                        <i class="fa fa-robot mr-1"></i> AI ANALYZE
                    </span>
                    <span wire:loading wire:target="getAiRecommendations">
                        <i class="fa fa-spinner fa-spin mr-1"></i> ANALYZING...
                    </span>
                </button>

                <button wire:click.prevent="exportData"
                        wire:loading.attr="disabled"
                        wire:target="exportData"
                        class="btn btn-sm btn-success"
                        style="font-family:'Syne',sans-serif; font-size:0.62rem; font-weight:700; letter-spacing:0.08em; border-radius:8px; padding:5px 11px;">
                    <span wire:loading.remove wire:target="exportData">
                        <i class="fa fa-download mr-1"></i> EXPORT
                    </span>
                    <span wire:loading wire:target="exportData">
                        <i class="fa fa-spinner fa-spin mr-1"></i> EXPORTING...
                    </span>
                </button>
            </div>

            {{-- AI error alert --}}
            @if($ai_error)
                <div class="alert alert-warning alert-dismissible" style="font-size:0.8rem;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> {{ $ai_error }}
                    <button type="button" class="close" wire:click="$set('ai_error', null)"><span>&times;</span></button>
                </div>
            @endif

            <div class="vmi-table-wrap">
                <div wire:loading class="vmi-loading-overlay">
                    <i class="fa fa-spinner fa-spin"></i>
                    Updating...
                </div>

                {{-- Table --}}
                <div class="table-responsive" style="border-radius: var(--radius-sm); overflow-x: auto; overflow-y: visible; border: 1px solid var(--glass-border); -webkit-overflow-scrolling: touch;">
                    <table class="table vmi-table m-0" wire:loading.class="opacity-50">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center align-middle px-2">STOCK CODE</th>
                                <th rowspan="2" class="text-center align-middle px-2">DESCRIPTION</th>
                                <th rowspan="2" class="text-center align-middle px-2">INV TOTAL CS</th>

                                @foreach($months_arr as $n => $label)
                                    <th colspan="4" class="text-center bg-month-{{ $n }}">{{ $label }} AVG</th>
                                @endforeach

                                <th colspan="1" class="text-center bg-ai">AI ANALYSIS</th>
                            </tr>
                            <tr>
                                @foreach($months_arr as $n => $label)
                                    <th class="text-center group-sep">STO CS</th>
                                    <th class="text-center">WEEK COV</th>
                                    <th class="text-center">COV NEED</th>
                                    <th class="text-center col-highlight">TO ORDER</th>
                                @endforeach

                                <th class="text-center group-sep" style="background:rgba(255,214,10,0.18)!important; color:#7a5a00!important;">ANALYSIS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventories as $inventory)
                                @php $p_id = $inventory->product_id; @endphp
                                <tr>
                                    <td class="text-accent">{{ $data[$p_id]['stock_code'] }}</td>
                                    <td class="text-truncate" style="max-width: 140px;">{{ $data[$p_id]['description'] }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($data[$p_id]['cs_total'], 1) }}</td>

                                    @foreach($data[$p_id]['months_data'] as $val)
                                        <td class="text-right group-sep">{{ number_format($val['sto'], 1) }}</td>
                                        <td class="text-right">{{ number_format($val['w_cov'], 1) }}</td>
                                        <td class="text-right text-muted">{{ number_format($val['w_cov_needed'], 1) }}</td>
                                        <td class="text-right col-highlight {{ $val['vmi'] < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($val['vmi'], 1) }}
                                        </td>
                                    @endforeach

                                    <td class="ai-reason-cell group-sep">
                                        {{ $ai_recommendations[$p_id]['analysis'] ?? '' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

        <div class="card-footer">
            {{ $inventories->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</div>
