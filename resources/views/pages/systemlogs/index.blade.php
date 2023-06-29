@extends('adminlte::page')

@section('title', 'System Logs')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>SYSTEM LOGS</h1>
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['systemlog'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SYSTEM LOGS</h3>
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

            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>LOG NAME</th>
                                <th>DESCRIPTION</th>
                                <th>USER</th>
                                <th>CHANGES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                            <tr>
                                <td>{{$activity->log_name}}</td>
                                <td>{{$activity->description}}</td>
                                <td>{{$activity->causer->name}}</td>
                                <td class="p-1">
                                    @if($activity->log_name == 'update' && !empty($updates[$activity->id]))
                                    <ul class="list-group">
                                        @foreach($updates[$activity->id] as $column => $data)
                                        <li class="list-group-item p-1">
                                            <b>{{$column}}:</b> {{$data['old']}}
                                            <p class="mb-0">
                                                <b>to:</b> {{$data['new']}}
                                            </p>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            {{$activities->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
