@extends('adminlte::page')

@section('title', 'Profile')

@section('content_header')
    <h1>PROFILE</h1>
@stop

@section('content')

    <div class="row">

        <div class="col-lg-4">
            <!-- User Details -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{auth()->user()->adminlte_image()}}"
                            alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{auth()->user()->name}}</h3>

                    <p class="text-muted text-center">{{implode(', ', auth()->user()->getRoleNames()->toArray())}}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item p-1">
                            <b>Username</b>
                            <span class="float-right">{{auth()->user()->username ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Email</b>
                            <span class="float-right">{{auth()->user()->email ?? '-'}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">

            <div class="card card-primary card-outline">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                      <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Profile Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                    <li class="nav-item"><a class="nav-link" href="#change-password" data-toggle="tab">Change Password</a></li>
                  </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="settings">
                            <livewire:user.profile-settings/>
                        </div>
                        <div class="tab-pane" id="activity">
                            <livewire:activity-logs.user-logs/>
                        </div>
                        <div class="tab-pane" id="change-password">
                            
                        </div>
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
