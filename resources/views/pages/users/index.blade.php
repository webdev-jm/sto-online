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
{!! Form::open(['method' => 'GET', 'route' => ['user.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">USERS LIST</h3>
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

            <b>{{$users->total()}} total result{{$users->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($users as $user)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-2 text-center align-middle">
                            <p class="m-0 align-middle">
                                <img class="img-circle elevation-2" src="{{asset(!empty($user->profile_picture_url) ? $user->profile_picture_url.'-small.jpg': '/images/Windows_10_Default_Profile_Picture.svg')}}" alt="User Avatar" width="30px" height="30px">

                                @if(Cache::has('user-is-online-' . $user->id))
                                    <span class="text-success ml-1">Online</span>
                                @else
                                    <span class="text-muted ml-1">Offline</span>
                                @endif
                            </p>
                            <b>USER STATUS</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                {{$user->name}}
                            </p>
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
                                    <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($user->id)}}">
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

    @can('user delete')
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
    @can('user delete')
        <script>
            $(function() {
                $('body').on('click', '.btn-delete', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    Livewire.emit('setDeleteModel', 'User', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
