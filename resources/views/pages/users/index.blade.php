@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>USERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('user create')
            <a href="{{route('user.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add User</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">USERS LIST</h3>
        </div>
        <div class="card-body">

            <ul class="list-group">
                @foreach($users as $user)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-2 text-center align-middle">
                            <p class="m-0 align-middle">
                                @if(Cache::has('user-is-online-' . $user->id))
                                    <i class="fa fa-circle text-success mr-1"></i>
                                    Online
                                @else
                                    <i class="fa fa-circle text-secondary mr-1"></i>
                                    Offline
                                @endif
                            </p>
                            <b>ACTIVE</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">{{$user->name}}</p>
                            <b>NAME</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">{{$user->email}}</p>
                            <b>EMAIL</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">{{implode(', ', $user->getRoleNames()->toArray())}}</p>
                            <b>ROLE/S</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                @if($user->status == 0)
                                    <span class="text-success">Active</span>
                                @else
                                    <span class="text-danger">Inactive</span>
                                @endif
                            </p>
                            <b>STATUS</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                <a href="{{route('user.show', encrypt($user->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('user edit')
                                    <a href="{{route('user.edit', encrypt($user->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('user delete')
                                    <a href="" class="btn btn-danger btn-xs">
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
            {{$users->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
