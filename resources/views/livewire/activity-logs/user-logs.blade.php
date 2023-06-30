<div>
    <div class="card">
        <div class="card-header py-2">
            <h3 class="card-title">Activities</h3>
            <div class="card-tools">
                <div class="form-group-sm">
                    <input type="text" class="form-control form-control-sm" placeholder="search" wire:model="search">
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Message</th>
                        <th>Timestamp</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{$activity->log_name}}</td>
                            <td>{{$activity->causer->name ?? '-'}}</td>
                            <td>{{$activity->description}}</td>
                            <td>{{date('F j, Y H:i:s a', strtotime($activity->created_at))}}</td>
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
        <div class="card-footer">
            {{$activities->links()}}
        </div>
    </div>
</div>
