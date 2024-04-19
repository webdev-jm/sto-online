@extends('adminlte::page')

@section('title', 'User - Edit')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>USERS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('user.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            <a href="{{route('user.show', encrypt($user->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>DETAILS</a>
        </div>
    </div>
@stop

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['user.update', encrypt($user->id)], 'id' => 'edit_user', 'autocomplete' => 'off']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT USER</h3>
        </div>
        <div class="card-body">

            <label class="mb-0 text-primary">GENERAL INFORMATION</label>
            <hr class="mt-0 bg-primary">

            <div class="row">

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Name') !!}
                        {!! Form::text('name', $user->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'edit_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('name')}}</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('email', 'Email') !!}
                        {!! Form::email('email', $user->email, ['class' => 'form-control'.($errors->has('email') ? ' is-invalid' : ''), 'form' => 'edit_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('email')}}</p>
                    </div>
                </div>

            </div>

            <label class="mb-0 text-primary">PASSWORD</label>
            <hr class="mt-0 bg-primary">
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('password', 'New Password') !!}
                        {!! Form::password('password', ['class' => 'form-control'.($errors->has('password') ? ' is-invalid' : ''), 'form' => 'edit_user']) !!}
                        <p class="text-danger mt-1">{{$errors->first('password')}}</p>
                    </div>
                </div>
            </div>

            <label class="mb-0 text-primary">ROLES</label>
            @if($errors->has('role_ids'))
                <span class="badge badge-danger">required</span>
            @endif
            <hr class="mt-0 bg-primary">
            {!! Form::hidden('role_ids', implode(',', $user_roles), ['form' => 'edit_user', 'id' => 'role_ids']) !!}

            <div class="row">
                <div class="col-12">
                    @foreach($roles as $role)
                    <button class="btn {{in_array($role->id, $user_roles) ? 'btn-success' : 'btn-default'}} btn-role" data-id="{{$role->id}}">{{$role->name}}</button>
                    @endforeach
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit User', ['class' => 'btn btn-primary btn-sm', 'form' => 'edit_user']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('Select2', true)

@section('js')
<script>
    $(function() {
        $('.select2').select2();

        // ROLES
        $('body').on('click', '.btn-role', function(e) {
            e.preventDefault();
            $(this).toggleClass('btn-success').toggleClass('btn-default');

            // get all selected
            var role_ids = [];
            $('body').find('.btn-role').each(function() {
                var id = $(this).data('id');
                if($(this).hasClass('btn-success')) {
                    role_ids.push(id);
                }
            });

            var roles = role_ids.join(',');
            $('#role_ids').val(roles);
        });

    });
</script>
@stop
