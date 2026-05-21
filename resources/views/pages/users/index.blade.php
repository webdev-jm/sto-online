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

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>USER STATUS</th>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>ROLE/S</th>
                            <th>STATUS</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="align-middle text-nowrap">
                                <img class="img-circle elevation-2 mr-1" src="{{asset(!empty($user->profile_picture_url) ? $user->profile_picture_url.'-small.jpg': '/images/Windows_10_Default_Profile_Picture.svg')}}" alt="User Avatar" width="25px" height="25px">
                                @if(Cache::has('user-is-online-' . $user->id))
                                    <span class="text-success">Online</span>
                                @else
                                    <span class="text-muted">Offline</span>
                                @endif
                            </td>
                            <td class="align-middle">{{$user->name}}</td>
                            <td class="align-middle">{{$user->email}}</td>
                            <td class="align-middle">{{implode(', ', $user->getRoleNames()->toArray())}}</td>
                            <td class="align-middle">
                                @if($user->status == 0)
                                    <span class="text-success">Active</span>
                                @else
                                    <span class="text-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="align-middle text-center text-nowrap">
                                <a href="{{route('user.show', encrypt($user->id))}}" class="btn btn-info btn-xs" title="View details">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('user edit')
                                    <a href="{{route('user.edit', encrypt($user->id))}}" class="btn btn-success btn-xs" title="Edit">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('user delete')
                                    <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($user->id)}}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{$users->links(data: ['scrollTo' => false])}}
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
                    Livewire.dispatch('setDeleteModel', { type: 'User', model_id: id });
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
