@extends('adminlte::page')

@section('title', 'Account Branch - Details')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ACCOUNT BRANCHES</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('account-branch.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            @can('account branch edit')
                <a href="{{route('account-branch.edit', encrypt($account_branch->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="row">

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">BRANCH DETAILS</h3>
                </div>
                <div class="card-body py-0">

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>ACCOUNT</b>
                            <span class="float-right">{{$account_branch->account->short_name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>CODE</b>
                            <span class="float-right">{{$account_branch->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>NAME</b>
                            <span class="float-right">{{$account_branch->name ?? '-'}}</span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>

    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function() {
    });
</script>
@stop
