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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ROLES LIST</h3>
        </div>
        <div class="card-body">
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
                                    <a href="" class="btn btn-info btn-xs">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    <a href="{{route('role.edit', encrypt($role->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    <a href="" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i>
                                    </a>
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
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
