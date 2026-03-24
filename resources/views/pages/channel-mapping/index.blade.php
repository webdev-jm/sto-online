@extends('adminlte::page')

@section('title', 'Product Mapping')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>CHANNEL MAPPING</h1>
    </div>
    <div class="col-lg-6 text-right">
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CHANNEL MAPPING LIST</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($accounts as $account)
                    <div class="col-lg-3">
                        <a href="{{ route('channel-mapping.entry', encrypt($account->id)) }}" class="btn btn-default btn-block mb-2">
                            {{$account->account_code}} - {{ $account->short_name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            {{ $accounts->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        /* --- Page Container --- */
        .glass-card {
            background: var(--glass-light) !important;
            backdrop-filter: var(--glass-blur) !important;
            -webkit-backdrop-filter: var(--glass-blur) !important;
            border-radius: var(--radius) !important;
            border: 1px solid var(--glass-border) !important;
            box-shadow: var(--shadow) !important;
        }

        /* --- Interactive Tiles --- */
        .glass-tile {
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-sm);
            padding: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-decoration: none !important;
            height: 100%;
            min-height: 100px;
        }

        .glass-tile:hover {
            transform: translateY(-4px);
            background: var(--col-accent-g) !important; /* Your Blue-Purple Gradient */
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        /* --- Text Inside Tiles --- */
        .account-code {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--col-dark);
            display: block;
            margin-bottom: 4px;
            transition: color 0.2s;
        }

        .account-name {
            font-family: 'Figtree', sans-serif;
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--col-muted);
            transition: color 0.2s;
        }

        .arrow-icon {
            position: absolute;
            right: 15px;
            bottom: 15px;
            font-size: 0.8rem;
            opacity: 0.3;
            color: var(--col-dark);
            transition: all 0.2s;
        }

        /* --- Hover State Overrides --- */
        .glass-tile:hover .account-code,
        .glass-tile:hover .account-name,
        .glass-tile:hover .arrow-icon {
            color: #ffffff !important;
            opacity: 1;
        }

        /* --- Dark Mode Adaptation --- */
        .dark-mode .glass-card {
            background: rgba(28, 28, 30, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .dark-mode .glass-tile {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dark-mode .account-code { color: #f5f5f7; }
        .dark-mode .account-name { color: #a1a1a6; }
        .dark-mode .arrow-icon { color: #f5f5f7; }

        /* Pagination Styling */
        .pagination .page-link {
            border-radius: var(--radius-xs) !important;
            margin: 0 3px;
            border: none;
            background: rgba(0,0,0,0.05);
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            color: var(--col-dark);
        }

        .pagination .page-item.active .page-link {
            background: var(--col-accent-g) !important;
            box-shadow: var(--shadow-btn);
        }

        /* --- APP GLASS: BTN-BLOCK REINVENTED --- */
        .btn-block {
            display: flex !important;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 12px 20px !important;

            /* Typography */
            font-family: 'Syne', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            letter-spacing: 0.02em;
            text-transform: uppercase;

            /* Glass Effect */
            background: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: var(--glass-blur-sm) !important;
            -webkit-backdrop-filter: var(--glass-blur-sm) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: var(--radius-sm) !important;
            color: var(--col-dark) !important;

            /* Transition */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: var(--shadow-btn) !important;
            margin-bottom: 12px;
        }

        /* Hover & Active States */
        .btn-block:hover {
            background: var(--col-accent-g) !important; /* Blue-Purple Gradient */
            color: #ffffff !important;
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg) !important;
            border-color: transparent !important;
        }

        .btn-block:active {
            transform: translateY(-1px) scale(0.98);
            opacity: 0.9;
        }

        /* --- DARK MODE ADAPTATION --- */
        .dark-mode .btn-block {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f5f5f7 !important;
        }

        .dark-mode .btn-block:hover {
            background: var(--col-accent-g) !important;
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.3) !important;
        }

        /* Pagination Adjustments (if applicable) */
        .pagination .page-link {
            background: var(--glass-light);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xs) !important;
            margin: 0 3px;
            color: var(--col-dark);
        }
    </style>
@stop

@section('js')

@stop
