@extends('adminlte::page')

@section('title', 'Parked Customers - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CUSTOMERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-primary btn-sm"><i class="fa fa-people-carry mr-1"></i>Customers</a>
    </div>
</div>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PARKED CUSTOMER LIST</h3>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>CODE</th>
                            <th>NAME</th>
                            <th>ADDRESS</th>
                            <th>SALESMAN</th>
                            <th>STATUS</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td class="align-middle font-weight-bold">{{$customer->code}}</td>
                            <td class="align-middle">{{$customer->name}}</td>
                            <td class="align-middle">{{$customer->address}}</td>
                            <td class="align-middle">{{$customer->salesman->code ?? '-'}}</td>
                            <td class="align-middle"><span class="badge badge-warning">PARKED</span></td>
                            <td class="align-middle text-center text-nowrap">
                                <a href="{{route('customer.show', encrypt($customer->id))}}" class="btn btn-info btn-xs" title="View details">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('customer parked validation')
                                    <a href="{{route('customer.validate', encrypt($customer->id))}}" class="btn btn-success btn-xs" data-id="{{encrypt($customer->id)}}" title="Validate">
                                        <i class="fa fa-user-check"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$customers->links(data: ['scrollTo' => false])}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
