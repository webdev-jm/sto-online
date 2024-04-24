@extends('adminlte::page')

@section('title', 'District - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - DISTRICT</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
        @can('district create')
            <a href="{{route('district.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add District</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['district.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DISTRICT LIST</h3>
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

            <b>{{$districts->total()}} total result{{$districts->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($districts as $district)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-6 text-center">
                            <p class="m-0 font-weight-bold">{{$district->district_code}}</p>
                            <small class="font-weight-bold text-muted">DISTRICT CODE</small>
                        </div>
                        <div class="col-lg-6 text-center">
                            <p class="m-0">
                                @if(empty($district->deleted_at))
                                    <a href="{{route('district.show', encrypt($district->id))}}" class="btn btn-info btn-xs">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('district edit')
                                        <a href="{{route('district.edit', encrypt($district->id))}}" class="btn btn-success btn-xs">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('district delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($district->id)}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                @else
                                    @can('district restore')
                                        <a href="{{route('district.restore', encrypt($district->id))}}" class="btn btn-warning btn-xs"  title="restore"><i class="fa fa-recycle"></i></a>
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
            {{$districts->links()}}
        </div>
    </div>

    @can('district delete')
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
    @can('district delete')
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
