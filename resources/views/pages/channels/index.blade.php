@extends('adminlte::page')

@section('title', 'Channel - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CHANNELS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
        @can('channel create')
            {{-- <a href="{{route('channel.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Channel</a> --}}
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['channel.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CHANNEL LIST</h3>
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

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>CODE</th>
                            <th>NAME</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($channels as $channel)
                        <tr>
                            <td class="align-middle font-weight-bold">{{$channel->code}}</td>
                            <td class="align-middle">{{$channel->name}}</td>
                            <td class="align-middle text-center text-nowrap">
                                @if(empty($channel->deleted_at))
                                    <a href="{{route('channel.show', encrypt($channel->id))}}" class="btn btn-info btn-xs" title="View details">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('channel edit')
                                        {{-- <a href="{{route('channel.edit', encrypt($channel->id))}}" class="btn btn-success btn-xs"><i class="fa fa-pen"></i></a> --}}
                                    @endcan
                                    @can('channel delete')
                                        {{-- <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($channel->id)}}"><i class="fa fa-trash"></i></a> --}}
                                    @endcan
                                @else
                                    @can('channel restore')
                                        {{-- <a href="{{route('channel.restore', encrypt($channel->id))}}" class="btn btn-warning btn-xs" title="Restore"><i class="fa fa-recycle"></i></a> --}}
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$channels->links(data: ['scrollTo' => false])}}
        </div>
    </div>

    @can('channel delete')
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
    @can('area delete')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', { type: 'Channel', model_id: id });
                $('#modal-delete').modal('show');
            });
        });
    </script>
    @endcan
@stop
