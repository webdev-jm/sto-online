@extends('adminlte::page')

@section('title', 'District - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - DISTRICT</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('district.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back to List</a>
        @can('district edit')
            <a href="{{route('district.edit', encrypt($district->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit District</a>
        @endcan
        @can('district delete')
            <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($district->id)}}"><i class="fa fa-trash-alt mr-1"></i>Delete</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">DISTRICT DETAILS</h3>
                </div>
                <div class="card-body py-0">
        
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>District Code</b>
                            <span class="float-right">{{$district->district_code ?? '-'}}</span>
                        </li>
                    </ul>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">AREAS</h3>
                </div>
                <div class="card-body py-0">
                    <ul class="list-group list-group-unbordered">
                        @if(!empty($district->areas->count()))
                            @foreach($district->areas as $area)
                            <li class="list-group-item p-1">
                                <b>
                                    <a href="{{route('area.show', encrypt($area->id))}}">
                                        {{$area->code}}
                                    </a>
                                </b>
                                <span class="float-right">
                                        {{$area->name}}
                                </span>
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
                    Livewire.emit('setDeleteModel', 'District', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
