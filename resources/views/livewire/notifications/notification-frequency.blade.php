<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">FREQUENCY LIST</h3>
            <div class="card-tools">
                @if(!$showAdd)
                    <button class="btn btn-primary btn-xs" wire:click.prevent="addFrequency">
                        <i class="fa fa-plus mr-1"></i>
                        ADD FREQUENCY
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">

            @if($showAdd)
                <div class="card card-outline card-secondary">
                    <div class="card-header py-2">
                        <h3 class="card-title">ADD FREQUENCY</h3>
                    </div>
                    <div class="card-body py-1">
                        <div class="row">

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">TYPE</label>
                                    <select class="form-control form-control-sm{{$errors->has('type') ? ' is-invalid' : ''}}" wire:model="type">
                                        <option value="daily">DAILY</option>
                                        <option value="weekly">WEEKLY</option>
                                        <option value="monthly">MONTHLY</option>
                                    </select>
                                    <small class="text-danger">{{$errors->first('type')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">TIME</label>
                                    <input type="time" class="form-control form-control-sm{{$errors->has('time') ? ' is-invalid' : ''}}" wire:model="time">
                                    <small class="text-danger">{{$errors->first('time')}}</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="mb-0">DAY</label>
                                    <input type="number" class="form-control form-control-sm{{$errors->has('day') ? ' is-invalid' : ''}}" wire:model="day">
                                </div>
                                <small class="text-danger">{{$errors->first('day')}}</small>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer text-right p-1">
                        <button class="btn btn-secondary btn-xs" wire:click.prevent="addFrequency">
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
                            <th class="p-0 align-middle">TYPE</th>
                            <th class="p-0 align-middle">TIME</th>
                            <th class="p-0 align-middle">DAY</th>
                            <th class="p-0 align-middle"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($frequencies as $frequency)
                            <tr class="text-center">
                                <td class="p-0 align-middle">
                                    {{$frequency->type}}
                                </td>
                                <td class="p-0 align-middle">
                                    {{$frequency->time}}
                                </td>
                                <td class="p-0 align-middle">
                                    {{$frequency->day}}
                                </td>
                                <td class="p-0 align-middle">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$frequencies->links(data: ['scrollTo' => false])}}
        </div>
    </div>
</div>
