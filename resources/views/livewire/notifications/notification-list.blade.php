<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">NOTIFICATION LIST</h3>
            <div class="card-tools">
                @can('notification create')
                    @if(!$showAdd)
                        <button class="btn btn-primary btn-xs" wire:click.prevent="showAdd">
                            <i class="fa fa-plus mr-1"></i>
                            ADD NOTIFICATION
                        </button>
                    @endif
                @endif
            </div>
        </div>
        <div class="card-body">

            @if($showAdd)
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">ADD NOTIFICATION</h3>
                    </div>
                    <div class="card-body pb-0">

                        <div class="row">
                            
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">SUBJECT</label>
                                    <input type="text" class="form-control form-control-sm{{$errors->has('subject') ? ' is-invalid' : ''}}" wire:model.live="subject">
                                    <small class="text-danger">{{$errors->first('subject')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">FROM EMAIL</label>
                                    <input type="text" class="form-control form-control-sm{{$errors->has('from_email') ? ' is-invalid' : ''}}" wire:model.live="from_email">
                                    <small class="text-danger">{{$errors->first('from_email')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">FROM NAME</label>
                                    <input type="text" class="form-control form-control-sm{{$errors->has('from_name') ? ' is-invalid' : ''}}" wire:model.live="from_name">
                                    <small class="text-danger">{{$errors->first('from_name')}}</small>
                                </div>
                            </div>
                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="mb-0">MESSAGE</label>
                                    <textarea class="form-control form-control-sm{{$errors->has('message') ? ' is-invalid' : ''}}" wire:model.live="message"></textarea>
                                    <small class="text-danger">{{$errors->first('message')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">LINK NAME</label>
                                    <input type="text" class="form-control form-control-sm{{$errors->has('link_name') ? ' is-invalid' : ''}}" wire:model.live="link_name">
                                    <small class="text-danger">{{$errors->first('link_name')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">LINK URL</label>
                                    <input type="text" class="form-control form-control-sm{{$errors->has('link_url') ? ' is-invalid' : ''}}" wire:model.live="link_url">
                                    <small class="text-danger">{{$errors->first('link_url')}}</small>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="card-footer text-right p-1">
                        <button class="btn btn-secondary btn-xs" wire:click.prevent="showAdd">
                            <i class="fa fa-ban mr-1"></i>
                            CANCEL
                        </button>
                        <button class="btn btn-primary btn-xs" wire:click.prevent="save">
                            <i class="fa fa-save mr-1"></i>
                            SAVE
                        </button>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr class="text-center">
                            <th class="p-0 align-middle">SUBJECT</th>
                            <th class="p-0 align-middle">FROM EMAIL</th>
                            <th class="p-0 align-middle">FROM NAME</th>
                            <th class="p-0 align-middle">MESSAGE</th>
                            <th class="p-0 align-middle">LINK NAME</th>
                            <th class="p-0 align-middle">LINK URL</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                        <tr class="text-center">
                            <td class="p-1 align-middle">
                                {{$notification->subject}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$notification->from_email}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$notification->from_name}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$notification->message}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$notification->link_name}}
                            </td>
                            <td class="p-1 align-middle">
                                {{$notification->link_url}}
                            </td>
                            <td class="p-1 align-middle">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$notifications->links(data: ['scrollTo' => false])}}
        </div>
    </div>
</div>
