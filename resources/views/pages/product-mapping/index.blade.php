@extends('adminlte::page')

@section('title', 'Product Mapping')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>PRODUCT MAPPING</h1>
    </div>
    <div class="col-lg-6 text-right">
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PRODUCT MAPPING LIST</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($accounts as $account)
                    <div class="col-lg-3">
                        <a href="{{ route('product-mapping.entry', encrypt($account->id)) }}" class="btn btn-default btn-block mb-2">
                            {{$account->account_code}} - {{ $account->short_name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-foot">
            {{ $accounts->links() }}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')

@stop
