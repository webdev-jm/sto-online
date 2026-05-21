@extends('adminlte::page')

@section('title', 'Locations - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - LOCATIONS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Menu</a>
        @can('location create')
            <a href="{{route('location.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Location</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['location.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">LOCATION LIST</h3>
            @can('location upload')
            <div class="card-tools">
                <button class="btn btn-info btn-sm" type="button" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</button>
            </div>
            @endcan
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

            <b>{{$locations->total()}} total result{{$locations->total() > 1 ? 's' : ''}}</b>

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
                        @foreach($locations as $location)
                        <tr>
                            <td class="align-middle font-weight-bold">{{$location->code}}</td>
                            <td class="align-middle">{{$location->name}}</td>
                            <td class="align-middle text-center text-nowrap">
                                @if(empty($location->deleted_at))
                                    <a href="{{route('location.show', encrypt($location->id))}}" class="btn btn-info btn-xs" title="View details">
                                        <i class="fa fa-list"></i>
                                    </a>
                                    @can('location edit')
                                        <a href="{{route('location.edit', encrypt($location->id))}}" class="btn btn-success btn-xs" title="Edit">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                    @endcan
                                    @can('location delete')
                                        <a href="" class="btn btn-danger btn-xs btn-delete" data-id="{{encrypt($location->id)}}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                @else
                                    @can('location restore')
                                        <a href="{{route('location.restore', encrypt($location->id))}}" class="btn btn-warning btn-xs" title="Restore">
                                            <i class="fa fa-recycle"></i>
                                        </a>
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
            {{$locations->links(data: ['scrollTo' => false])}}
        </div>
    </div>

    @can('location upload')
        {{-- MODAL --}}
        <div class="modal fade" id="modal-upload">
            <div class="modal-dialog modal-lg">
                <livewire:uploads.location/>
            </div>
        </div>
    @endcan

    @can('location delete')
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
    @can('location upload')
        <script>
            $(function() {
                $('#btn-upload').on('click', function(e) {
                    e.preventDefault();
                    $('#modal-upload').modal('show');
                });
            });
        </script>
    @endcan

    @can('location delete')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', { type: 'Location', model_id: id });
                $('#modal-delete').modal('show');
            });
        });
    </script>
    @endcan
@stop
