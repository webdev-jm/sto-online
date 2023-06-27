@extends('adminlte::page')

@section('title', 'Customers - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CUSTOMERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
        @can('customer create')
            <a href="{{route('customer.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Customer</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['customer.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CUSTOMER LIST</h3>
            @can('customer upload')
                <div class="card-tools">
                    <button class="btn btn-info btn-sm" type="button" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</button>
                </div>
            @endcan
        </div>
        <div class="card-body">

            <div class="row mb-1">
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('search', 'Search') !!}
                        {!! Form::text('search', $search, ['class' => 'form-control', 'form' => 'search_form', 'placeholder' => 'Search']) !!}
                    </div>
                </div>
            </div>

            <b>{{$customers->total()}} total result{{$customers->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($customers as $customer)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-2 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->code}}</p>
                            <small class="font-weight-bold text-muted">CODE</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->name}}</p>
                            <small class="font-weight-bold text-muted">NAME</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->address}}</p>
                            <small class="font-weight-bold text-muted">ADDRESS</small>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->salesman->code ?? '-'}}</p>
                            <small class="font-weight-bold text-muted">SALESMAN</small>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                <a href="{{route('customer.show', encrypt($customer->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('customer edit')
                                    <a href="{{route('customer.edit', encrypt($customer->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('customer delete')
                                    <a href="" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                @endcan
                            </p>
                            <small class="font-weight-bold text-muted">ACTION</small>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            
        </div>
        <div class="card-footer">
            {{$customers->links()}}
        </div>
    </div>

    @can('customer upload')
        {{-- MODAL --}}
        <div class="modal fade" id="modal-upload">
            <div class="modal-dialog modal-lg">
                <livewire:uploads.customer/>
            </div>
        </div>
    @endcan
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    @can('customer upload')
        <script>
            $(function() {
                $('#btn-upload').on('click', function(e) {
                    e.preventDefault();
                    $('#modal-upload').modal('show');
                });
            });
        </script>
    @endcan
@stop
