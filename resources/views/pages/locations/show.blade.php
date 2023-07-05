@extends('adminlte::page')

@section('title', 'Location Details - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - LOCATION DETAILS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('location.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('location edit')
            <a href="{{route('location.edit', encrypt($location->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit</a>
        @endcan
        @can('location delete')
            <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($location->id)}}"><i class="fa fa-trash-alt mr-1"></i>Delete</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">LOCATION DETAILS</h3>
                </div>
                <div class="card-body py-0">
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Code</b>
                            <span class="float-right">{{$location->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Name</b>
                            <span class="float-right">{{$location->name ?? '-'}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            <livewire:location.inventories :location="$location"/>
        </div>
    </div>

    @can('location delete')
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
    @can('location delete')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.emit('setDeleteModel', 'Location', id);
                $('#modal-delete').modal('show');
            });
        });
    </script>
    @endcan
@stop
