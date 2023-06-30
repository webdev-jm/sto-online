@extends('adminlte::page')

@section('title', 'Role')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ROLE</h1>
        </div>
        <div class="col-lg-6 text-right">
            @can('role edit')
                <a href="{{route('role.edit', encrypt($role->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit Role</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ROLE DETAILS</h3>
            </div>
            <div class="card-body py-0">
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item py-1">
                        <b>Role Name:</b>
                        <span class="float-right">{{$role->name ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>Created at:</b>
                        <span class="float-right">{{$role->created_at ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>Updated at:</b>
                        <span class="float-right">{{$role->updated_at ?? '-'}}</span>
                    </li>
                </ul>
    
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">PERMISSIONS</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($permissions_arr as $module => $permission_data)
                        <div class="col-lg-4">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">{{$module}}</h3>
                                </div>
                                <div class="card-body py-0">
                                    <ul class="list-group list-group-unbordered">
                                        @foreach($permission_data as $id => $permission)
                                        <li class="list-group-item p-1">
                                            <b class="d-block">{{ucwords($permission['name'])}}</b>
                                            <small>{{$permission['description']}}</small>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
    
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
