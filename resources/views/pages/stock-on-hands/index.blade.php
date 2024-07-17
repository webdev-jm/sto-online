@extends('adminlte::page')

@section('title', 'Purchase Orders - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - STOCK ON HAND</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">STOCK ON HAND PER STORE</h3>
            <div class="card-tools">
                @can('stock on hand upload')
                    <a href="{{route('stock-on-hand.upload')}}" class="btn btn-info btn-sm">
                        <i class="fa fa-upload mr-1"></i>
                        UPLOAD
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-12">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-0">CUSTOMER CODE</th>
                            <th class="align-middle p-0">CUSTOMER NAME</th>
                            <th class="align-middle p-0">YEAR</th>
                            <th class="align-middle p-0">MONTH</th>
                            <th class="align-middle p-0">UPLOAD DATE</th>
                            <th class="align-middle p-0">TOTAL INVENTORY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stock_on_hands as $soh)

                        @endforeach
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$stock_on_hands->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function() {

    });
</script>
@stop
