@extends('adminlte::page')

@section('title', 'Area - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - AREA</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('area.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('area edit')
            <a href="{{route('area.edit', encrypt($area->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit Area</a>
        @endcan
        @can('area delete')
            <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($area->id)}}"><i class="fa fa-trash-alt mr-1"></i>Delete</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">AREA DETAILS</h3>
                </div>
                <div class="card-body py-0">
        
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>Area Code</b>
                            <span class="float-right">{{$area->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Area Name</b>
                            <span class="float-right">{{$area->name ?? '-'}}</span>
                        </li>
                    </ul>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SALESMEN</h3>
                </div>
                <div class="card-body py-0">
                    <ul class="list-group list-group-unbordered">
                        @if(!empty($area->salesmen->count()))
                            @foreach($area->salesmen as $salesman)
                            <li class="list-group-item p-1">
                                <b>{{$salesman->code}}</b>
                                <span class="float-right">{{$salesman->name}}</span>
                            </li>
                            @endforeach
                        @else
                            <li class="list-group-item text-center">No available data.</li>
                        @endif
                    </ul>
                </div>
                <div class="card-footer">
                    
                </div>
            </div>
        </div>
    </div>

    @can('area delete')
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
    @can('area delete')
        <script>
            $(function() {
                $('body').on('click', '.btn-delete', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    Livewire.emit('setDeleteModel', 'Area', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
