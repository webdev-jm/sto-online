@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>USERS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('user.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{$user->adminlte_image()}}"
                            alt="User profile picture">
                    </div>
    
                    <h3 class="profile-username text-center">{{$user->name}}</h3>
    
                    <p class="text-muted text-center">{{implode(', ', $user->getRoleNames()->toArray())}}</p>
    
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item p-1">
                            <b>Username</b>
                            <span class="float-right">{{$user->username ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Email</b>
                            <span class="float-right">{{$user->email ?? '-'}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Assigned Accounts</h3>
                </div>
                <div class="card-body">
                    <livewire:user.assigned-accounts :user="$user" />
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
