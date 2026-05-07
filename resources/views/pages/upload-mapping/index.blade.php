@extends('adminlte::page')

@section('title', 'Upload Mapping')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>UPLOAD MAPPING</h1>
    </div>
    <div class="col-lg-6 text-right">
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UPLOAD MAPPING LIST</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($accounts as $account)
                    <div class="col-lg-3">
                        <a href="{{ route('upload-mapping.entry', encrypt($account->id)) }}" class="btn btn-default btn-block mb-2">
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
        .btn-block {
            display: flex !important;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 12px 20px !important;
            font-family: 'Syne', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: var(--glass-blur-sm) !important;
            -webkit-backdrop-filter: var(--glass-blur-sm) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: var(--radius-sm) !important;
            color: var(--col-dark) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: var(--shadow-btn) !important;
            margin-bottom: 12px;
        }
        .btn-block:hover {
            background: var(--col-accent-g) !important;
            color: #ffffff !important;
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg) !important;
            border-color: transparent !important;
        }
        .btn-block:active {
            transform: translateY(-1px) scale(0.98);
            opacity: 0.9;
        }
        .dark-mode .btn-block {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f5f5f7 !important;
        }
        .dark-mode .btn-block:hover {
            background: var(--col-accent-g) !important;
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.3) !important;
        }
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
    </style>
@stop

@section('js')
@stop
