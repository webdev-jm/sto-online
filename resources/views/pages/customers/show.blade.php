@extends('adminlte::page')

@section('title', 'Customer - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CUSTOMER</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back to List</a>
        @can('customer edit')
            <a href="{{route('customer.edit', encrypt($customer->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit Customer</a>
        @endcan
        @can('customer delete')
            <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($customer->id)}}"><i class="fa fa-trash-alt mr-1"></i>Delete</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CUSTOMER DETAILS</h3>
                </div>
                <div class="card-body">
        
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>Customer Code</b>
                            <span class="float-right">{{$customer->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Customer Name</b>
                            <span class="float-right">{{$customer->name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Salesman</b>
                            <span class="float-right">
                                <a href="{{route('salesman.show', encrypt($customer->salesman_id))}}">
                                    [{{$customer->salesman->code ?? '-'}}] {{$customer->salesman->name ?? '-'}}
                                </a>
                            </span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Address</b>
                            <span class="float-right">{{$customer->address ?? '-'}}</span>
                        </li>
                    </ul>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CUSTOMER SALESMAN HISTORY</h3>
                </div>
                <div class="card-body py-0">
                    <ul class="list-group list-group-unbordered">
                        @empty($customer->customer_salesmen)
                            <li class="list-group-item">No history.</li>
                        @else
                            @foreach($customer->customer_salesmen()->orderBy('created_at', 'DESC')->get() as $cust_salesman)
                            <li class="list-group-item p-1">
                                <b>[{{$cust_salesman->salesman->code ?? '-'}}] {{$cust_salesman->salesman->name ?? '-'}}</b>
                                <span class="float-right">{{$cust_salesman->start_date ?? '-'}} to {{$cust_salesman->end_date ?? 'current date'}}</span>
                            </li>
                            @endforeach
                        @endempty
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <livewire:customer.sales-details :customer="$customer"/>
        </div>
    </div>

    @can('customer delete')
    <div class="modal fade" id="modal-delete">
        <div class="modal-dialog">
            <livewire:confirm-delete/>
        </div>
    </div>
    @endcan
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    @can('customer delete')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.emit('setDeleteModel', 'Customer', id);
                $('#modal-delete').modal('show');
            });
        });
    </script>
    @endcan
@stop
