<div>
    @if(!empty($form_message))
        <div class="alert alert-success">
            {{$form_message}}
        </div>
    @endif

    <div class="card mb-0">
        <div class="card-header">
            <h3 class="card-title">NOTIFICATIONS</h3>
            <div class="card-tools">
                @if(!$showAdd)
                    <button class="btn btn-primary btn-xs" wire:click.prevent="showAdd">
                        <i class="fa fa-plus mr-1"></i>
                        ADD NOTIFICATION
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">

            @if($showAdd)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ADD NOTIFICATION</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>NOTIFICATION</label>
                                    <select class="form-control form-control-sm{{$errors->has('notification_id') ? ' is-invalid' : ''}}" wire:model="notification_id">
                                        <option value="">SELECT NOTIFICATION</option>
                                        @foreach($notifications as $notification)
                                            <option value="{{$notification->id}}">{{$notification->subject}}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger">{{$errors->first('notification_id')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>FREQUENCY</label>
                                    <select class="form-control form-control-sm{{$errors->has('frequency_id') ? ' is-invalid' : ''}}" wire:model="frequency_id">
                                        <option value="">SELECT FREQUENCY</option>
                                        @foreach($frequencies as $frequency)
                                            <option value="{{$frequency->id}}">{{$frequency->type}} - {{$frequency->time}} - {{$frequency->day}}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger">{{$errors->first('frequency_id')}}</small>
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
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>SUBJECT</th>
                            <th>MESSAGE</th>
                            <th>FREQUENCY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user_notifications as $user_notif)
                            <tr>
                                <td>{{$user_notif->notification->subject}}</td>
                                <td>{{$user_notif->notification->message}}</td>
                                <td>
                                    {{$user_notif->frequency->type}},
                                    {{$user_notif->frequency->day}},
                                    {{$user_notif->frequency->time}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
        <div class="card-footer">
            {{$user_notifications->links(data: ['scrollTo' => false])}}
        </div>
    </div>
</div>
