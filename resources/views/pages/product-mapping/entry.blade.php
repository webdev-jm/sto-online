@extends('adminlte::page')

@section('title', 'Product Mapping')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>PRODUCT MAPPING</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{ route('product-mapping.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i>
            Back
        </a>
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PRODUCT MAPPING MAINTENANCE</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <h4>Account: {{ $account->account_code }} - {{ $account->short_name }}</h4>
                </div>
                <div class="col-lg-12">
                    <livewire:product-mapping.mapping-list :account="$account" />
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')

@stop
