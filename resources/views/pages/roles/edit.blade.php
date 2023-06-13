@extends('adminlte::page')

@section('title', 'Roles')

@section('css')
<style>
    .card {
        height: 95%;
    }
</style>
@endsection

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ROLES</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('role.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
            <a href="{{route('role.show', encrypt($role->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>Details</a>
        </div>
    </div>
@stop

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['role.update', encrypt($role->id)], 'id' => 'edit_role']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT ROLE</h3>
        </div>
        <div class="card-body">

            <div class="row">

                {{-- ROLE NAME --}}
                <div class="col-lg-3 col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', 'Role Name') !!}
                        {!! Form::text('name', $role->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'edit_role']) !!}
                        <p class="text-danger text-sm">{{$errors->first('name')}}</p>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-12">
                    <label>Permissions</label>
                    @if($errors->has('permissions'))
                    <span class="badge badge-danger ml-1">Required</span>
                    @endif
                </div>
                @foreach($permissions as $group => $permission_arr)
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{$group}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($permission_arr as $id => $permission)
                                    <div class="col-12">
                                        <div class="custom-control custom-switch">
                                            {!! Form::checkbox('permissions[]', $id, $role->hasPermissionTo(strtolower($permission['name'])), ['class' => 'custom-control-input', 'id' => 'permission'.$id, 'form' => 'edit_role']) !!}
                                            {!! Form::label('permission'.$id, ucwords($permission['name']), ['class' => 'custom-control-label']) !!}
                                            <small class="d-block m-0">{{$permission['description']}}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Role', ['class' => 'btn btn-primary btn-sm', 'form' => 'edit_role']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
