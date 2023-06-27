@extends('adminlte::page')

@section('title', 'Account Branches')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>ACCOUNT BRANCHES</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('account branch create')
            <a href="{{route('account-branch.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Branch</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['account-branch.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">BRANCHES LIST</h3>
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

            <b>{{$account_branches->total()}} total result{{$account_branches->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($account_branches as $account_branch)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$account_branch->account->short_name}}</p>
                            <b>ACCOUNT</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$account_branch->code}}</p>
                            <b>CODE</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$account_branch->name}}</p>
                            <b>NAME</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">
                                <a href="{{route('account-branch.show', encrypt($account_branch->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('account-branch edit')
                                    <a href="{{route('account-branch.edit', encrypt($account_branch->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('account-branch delete')
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
            {{$account_branches->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
