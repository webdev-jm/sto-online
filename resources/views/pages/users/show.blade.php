@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>USERS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('user.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            @can('user edit')
                <a href="{{route('user.edit', encrypt($user->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit</a>
            @endcan
            @can('user delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete" data-id="{{encrypt($user->id)}}"><i class="fa fa-trash-alt mr-1"></i>Delete</a>
            @endcan
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

            @if(!empty($user->accounts))
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">ACCOUNTS</h3>
                </div>
                <div class="card-body py-0 px-2">
                    <ul class="list-group list-group-unbordered">
                        @foreach($user->accounts as $account)
                            <li class="list-group-item py-1">
                                <b>{{$account->account_code ?? '-'}}</b>
                                <span class="float-right">{{$account->short_name ?? '-'}}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if(!empty($user->account_branches))
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">BRANCHES</h3>
                </div>
                <div class="card-body py-0 px-2">
                    <ul class="list-group list-group-unbordered">
                        @foreach($user->account_branches as $branch)
                            <li class="list-group-item py-1">
                                <b>{{$branch->code ?? '-'}}</b>
                                <span class="float-right">{{$branch->name ?? '-'}}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-9">
            @can('user assign account')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Assign Accounts</h3>
                </div>
                <div class="card-body p-1">
                    <livewire:user.assigned-accounts :user="$user" />
                </div>
            </div>
            @endcan

            @can('user assign branch')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Assign Branches</h3>
                </div>
                <div class="card-body p-1">
                    <livewire:user.assigned-branches :user="$user"/>
                </div>
            </div>
            @endcan
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
