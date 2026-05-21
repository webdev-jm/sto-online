@extends('adminlte::page')

@section('title', 'Salesmen - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - SALESMEN</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Menu</a>
        @can('salesman create')
            <a href="{{route('salesman.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Salesman</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['salesman.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALESMEN LIST</h3>
            @can('salesman upload')
                <div class="card-tools">
                    <button class="btn btn-info btn-sm" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</button>
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

            <b>{{$salesmen->total()}} total result{{$salesmen->total() > 1 ? 's' : ''}}</b>

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>CODE</th>
                            <th>NAME</th>
                            <th>TYPE OF SALESMAN</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesmen as $salesman)
                        <tr>
                            <td class="align-middle font-weight-bold">{{$salesman->code}}</td>
                            <td class="align-middle">{{$salesman->name}}</td>
                            <td class="align-middle">{{$salesman->type ?? '-'}}</td>
                            <td class="align-middle text-center text-nowrap">
                                @if(empty($salesman->deleted_at))
                                    <a href="{{route('salesman.show', encrypt($salesman->id))}}" class="btn btn-info btn-xs" title="View details">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('salesman edit')
                                        <a href="{{route('salesman.edit', encrypt($salesman->id))}}" class="btn btn-success btn-xs" title="Edit">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('salesman delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($salesman->id)}}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                @else
                                    @can('salesman restore')
                                        <a href="{{route('salesman.restore', encrypt($salesman->id))}}" class="btn btn-warning btn-xs" title="Restore">
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
            {{$salesmen->links(data: ['scrollTo' => false])}}
        </div>
    </div>

    @can('salesman upload')
        {{-- MODAL --}}
        <div class="modal fade" id="modal-upload">
            <div class="modal-dialog modal-lg">
                <livewire:uploads.salesman/>
            </div>
        </div>
    @endcan

    @can('salesman delete')
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
    @can('salesman upload')
        <script>
            $(function() {
                $('#btn-upload').on('click', function(e) {
                    e.preventDefault();
                    $('#modal-upload').modal('show');
                });
            });
        </script>
    @endcan

    @can('salesman delete')
        <script>
            $(function() {
                $('body').on('click', '.btn-delete', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    Livewire.dispatch('setDeleteModel', { type: 'Salesman', model_id: id });
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
