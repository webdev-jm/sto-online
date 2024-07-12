@extends('adminlte::page')

@section('title', 'Account - Details')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>ACCOUNTS</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('account.show', encrypt($account->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
            @can('account edit')
                <a href="{{route('account.edit', encrypt($account->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>EDIT</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <livewire:account.account-template-create :account="$account"/>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
