@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ROLES</h1>
        </div>
        <div class="col-lg-6 text-right">
            @can('role create')
                <a href="{{route('role.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Role</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['role.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ROLES LIST</h3>
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

            <b>{{$roles->total()}} total result{{$roles->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($roles as $role)
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-lg-4 text-center">
                                <p class="m-0">{{$role->name}}</p>
                                <b>NAME</b>
                            </div>
                            <div class="col-lg-4 text-center">
                                <p class="m-0">{{$role->users()->count()}}</p>
                                <b>NO OF USERS</b>
                            </div>
                            <div class="col-lg-4 text-center">
                                <p class="m-0">
                                    <a href="{{route('role.show', encrypt($role->id))}}" class="btn btn-info btn-xs">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('role edit')
                                        <a href="{{route('role.edit', encrypt($role->id))}}" class="btn btn-success btn-xs">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('role delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($role->id)}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                </p>
                                <b>ACTION</b>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

        </div>
        <div class="card-footer">
            {{$roles->links()}}
        </div>
    </div>

    @can('role delete')
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
    @can('role delete')
        <script>
            $(function() {
                $('body').on('click', '.btn-delete', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    Livewire.emit('setDeleteModel', 'Role', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
