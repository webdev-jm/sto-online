@extends('adminlte::page')

@section('title', 'RTV - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - RTV</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">RTV LIST</h3>
            <div class="card-tools">
                @can('rtv create')
                    <a href="{{route('rtv.create')}}" class="btn btn-xs btn-primary">
                        <i class="fa fa-plus mr-1"></i>
                        ADD RTV
                    </a>
                @endcan
                @can('rtv upload')
                    <a href="{{route('rtv.upload')}}" class="btn btn-xs btn-success">
                        <i class="fa fa-upload mr-1"></i>
                        UPLOAD RTV
                    </a>
               @endcan
            </div>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle">RTV NUMBER</th>
                            <th class="align-middle">DOCUMENT NUMBER</th>
                            <th class="align-middle">SHIP DATE</th>
                            <th class="align-middle">ENTRY DATE</th>
                            <th class="align-middle">REASON</th>
                            <th class="align-middle">SHIP TO NAME</th>
                            <th class="align-middle">SHIP TO ADDRESS</th>
                            <th class="align-middle"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return_to_vendors as $rtv)
                            <tr class="text-center">
                                <td class="align-middle">{{$rtv->rtv_number}}</td>
                                <td class="align-middle">{{$rtv->document_number}}</td>
                                <td class="align-middle">{{$rtv->ship_date}}</td>
                                <td class="align-middle">{{$rtv->entry_date}}</td>
                                <td class="align-middle">{{$rtv->reason}}</td>
                                <td class="align-middle">{{$rtv->ship_to_name}}</td>
                                <td class="align-middle">{{$rtv->ship_to_address}}</td>
                                <td class="align-middle text-center p-0 px-1">
                                    <a href="{{route('rtv.show', encrypt($rtv->id))}}">
                                        <i class="fa fa-list-alt fa-lg text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
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
