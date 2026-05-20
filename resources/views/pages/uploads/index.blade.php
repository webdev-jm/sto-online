@extends('adminlte::page')

@section('title', 'Uploads - '.$account->short_name)

@section('content_header')
<div class="page-header-bar">
    <div class="page-header-left">
        <div class="page-header-badge">{{ $account->account_code }}</div>
        <div class="page-header-info">
            <h1 class="page-header-title">UPLOADS</h1>
            <span class="page-header-sub">
                <i class="fa fa-code-branch mr-1"></i>
                {{ '['.$account_branch->code.'] '.$account_branch->name }}
            </span>
        </div>
    </div>
    <a href="{{ route('menu', encrypt($account_branch->id)) }}" class="btn btn-secondary btn-sm">
        <i class="fa fa-home mr-1"></i>Menu
    </a>
</div>
@stop

@section('content')

@if(session('message_success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fa fa-check"></i> {{ session('message_success') }}
</div>
@endif

<div class="card card-default">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="uploads-tabs" role="tablist">

            @can('location upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-location-link" data-toggle="tab" href="#tab-location" role="tab">
                    <i class="fa fa-truck-loading mr-1"></i>Location
                </a>
            </li>
            @endcan

            @can('area upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-area-link" data-toggle="tab" href="#tab-area" role="tab">
                    <i class="fa fa-map-marked-alt mr-1"></i>Area
                </a>
            </li>
            @endcan

            @can('district upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-district-link" data-toggle="tab" href="#tab-district" role="tab">
                    <i class="fa fa-map mr-1"></i>District
                </a>
            </li>
            @endcan

            @can('salesman upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-salesman-link" data-toggle="tab" href="#tab-salesman" role="tab">
                    <i class="fa fa-user-tie mr-1"></i>Salesman
                </a>
            </li>
            @endcan

            @can('customer upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-customer-link" data-toggle="tab" href="#tab-customer" role="tab">
                    <i class="fa fa-people-carry mr-1"></i>Customers
                </a>
            </li>
            @endcan

            @can('sales upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-sales-link" data-toggle="tab" href="#tab-sales" role="tab">
                    <i class="fa fa-money-check-alt mr-1"></i>Sales
                </a>
            </li>
            @endcan

            @can('inventory upload')
            <li class="nav-item">
                <a class="nav-link" id="tab-inventory-link" data-toggle="tab" href="#tab-inventory" role="tab">
                    <i class="fa fa-warehouse mr-1"></i>Inventories
                </a>
            </li>
            @endcan

        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">

            @can('location upload')
            <div class="tab-pane fade" id="tab-location" role="tabpanel">
                <livewire:uploads.location mode="card" redirect-route="uploads.index" active-tab="location"/>
            </div>
            @endcan

            @can('area upload')
            <div class="tab-pane fade" id="tab-area" role="tabpanel">
                <livewire:uploads.area redirect-route="uploads.index" active-tab="area"/>
            </div>
            @endcan

            @can('district upload')
            <div class="tab-pane fade" id="tab-district" role="tabpanel">
                <livewire:uploads.district redirect-route="uploads.index" active-tab="district"/>
            </div>
            @endcan

            @can('salesman upload')
            <div class="tab-pane fade" id="tab-salesman" role="tabpanel">
                <livewire:uploads.salesman mode="card" redirect-route="uploads.index" active-tab="salesman"/>
            </div>
            @endcan

            @can('customer upload')
            <div class="tab-pane fade" id="tab-customer" role="tabpanel">
                <livewire:uploads.customer mode="card" redirect-route="uploads.index" active-tab="customer"/>
            </div>
            @endcan

            @can('sales upload')
            <div class="tab-pane fade" id="tab-sales" role="tabpanel">
                <div class="card card-default mb-0">
                    <div class="card-header">
                        <h3 class="card-title">SALES UPLOAD</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">Sales upload includes inline customer and location maintenance tools.</p>
                        <a href="{{ route('sales.create') }}" class="btn btn-olive">
                            <i class="fa fa-upload mr-1"></i>Go to Sales Upload
                        </a>
                    </div>
                </div>
            </div>
            @endcan

            @can('inventory upload')
            <div class="tab-pane fade" id="tab-inventory" role="tabpanel">
                <div class="card card-default mb-0">
                    <div class="card-header">
                        <h3 class="card-title">INVENTORY UPLOAD</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">Inventory upload includes product and location validation tools.</p>
                        <a href="{{ route('inventory.create') }}" class="btn btn-indigo">
                            <i class="fa fa-upload mr-1"></i>Go to Inventory Upload
                        </a>
                    </div>
                </div>
            </div>
            @endcan

        </div>
    </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        #uploads-tabs .nav-link {
            border-radius: 0;
            padding: .5rem 1rem;
        }
        #uploads-tabs .nav-link.active {
            font-weight: 600;
        }
    </style>
@stop

@section('js')
<script>
$(function() {
    var params = new URLSearchParams(window.location.search);
    var tab = params.get('tab');

    if (tab) {
        var $target = $('#uploads-tabs a[href="#tab-' + tab + '"]');
        if ($target.length) {
            $target.tab('show');
        } else {
            $('#uploads-tabs .nav-link:first').tab('show');
        }
    } else {
        $('#uploads-tabs .nav-link:first').tab('show');
    }
});
</script>
@stop
