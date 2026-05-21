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
                    @can('customer parked')
                    <a href="{{route('customer.parked')}}" class="btn btn-warning btn-sm"><i class="fa fa-handshake-slash mr-1"></i>Parked Customers</a>
                    @endcan
                    <a href="#" class="btn btn-info btn-sm" type="button" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</a>
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

            @if(!empty(session('upload_data')))
            <div class="row">
                <div class="col-12">
                    <livewire:customer.upload-progress :upload_data="session('upload_data')"/>
                </div>
            </div>
            @endif

            <b>{{$customers->total()}} total result{{$customers->total() > 1 ? 's' : ''}}</b>

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>CODE</th>
                            <th>NAME</th>
                            <th>ADDRESS</th>
                            <th>SALESMAN</th>
                            <th>CHANNEL</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td class="align-middle font-weight-bold p-0">{{$customer->code}}</td>
                            <td class="align-middle p-0">{{$customer->name}}</td>
                            <td class="align-middle p-0">{{$customer->address}}</td>
                            <td class="align-middle p-0">{{$customer->salesman->code ?? '-'}}</td>
                            <td class="align-middle p-0">{{$customer->channel->code ?? '-'}}</td>
                            <td class="align-middle text-center text-nowrap p-0">
                                @if(empty($customer->deleted_at))
                                    <a href="{{route('customer.show', encrypt($customer->id))}}" class="btn btn-info btn-xs" title="View details">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('customer edit')
                                        <a href="{{route('customer.edit', encrypt($customer->id))}}" class="btn btn-success btn-xs" title="Edit">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('customer delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($customer->id)}}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                @else
                                    @can('customer restore')
                                        <a href="{{route('customer.restore', encrypt($customer->id))}}" class="btn btn-warning btn-xs" title="Restore">
                                            <i class="fa fa-recycle"></i>
                                        </a>
                                    @endcan
                                @endif
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

    @can('customer upload')
        {{-- MODAL --}}
        <div class="modal fade" id="modal-upload">
            <div class="modal-dialog modal-xl">
                <livewire:uploads.customer/>
            </div>
        </div>
    @endcan

    @can('customer delete')
    <div class="modal fade" id="modal-delete">
        <div class="modal-dialog modal-dialog-centered">
            <livewire:confirm-delete/>
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

    @can('customer delete')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', { type: 'Customer', model_id: id });
                $('#modal-delete').modal('show');
            });
        });
    </script>
    @endcan
@stop
