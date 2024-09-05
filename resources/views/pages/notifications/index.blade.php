@extends('adminlte::page')

@section('title', 'Notifications')

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>NOTIFICATIONS</h1>
    </div>
</div>
@stop

@section('content')
    <div class="row">
        {{-- NOTIFICATIONS --}}
        <div class="col-lg-6">
            <livewire:notifications.notification-list/>
        </div>

        {{-- FREQUENCIES --}}
        <div class="col-lg-6">
            <livewire:notifications.notification-frequency/>
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
                    Livewire.emit('setDeleteModel', 'Notification', id);
                    $('#modal-delete').modal('show');
                });
            });
        </script>
    @endcan
@stop
