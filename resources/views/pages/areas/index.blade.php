@extends('adminlte::page')

@section('title', 'Areas - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - AREAS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
        @can('area create')
            <a href="{{route('area.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Area</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['area.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">AREA LIST</h3>
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

            <b>{{$areas->total()}} total result{{$areas->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($areas as $area)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-4 text-center">
                            <p class="m-0 font-weight-bold">{{$area->code}}</p>
                            <small class="font-weight-bold text-muted">CODE</small>
                        </div>
                        <div class="col-lg-4 text-center">
                            <p class="m-0 font-weight-bold">{{$area->name}}</p>
                            <small class="font-weight-bold text-muted">NAME</small>
                        </div>
                        <div class="col-lg-4 text-center">
                            <p class="m-0">
                                @if(empty($area->deleted_at))
                                    <a href="{{route('area.show', encrypt($area->id))}}" class="btn btn-info btn-xs">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('area edit')
                                        <a href="{{route('area.edit', encrypt($area->id))}}" class="btn btn-success btn-xs">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('area delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($area->id)}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                @else
                                    @can('area restore')
                                        <a href="{{route('area.restore', encrypt($area->id))}}" class="btn btn-warning btn-xs"  title="restore"><i class="fa fa-recycle"></i></a>
                                    @endcan
                                @endif
                            </p>
                            <b>ACTION</b>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            
        </div>
        <div class="card-footer">
            {{$areas->links()}}
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
