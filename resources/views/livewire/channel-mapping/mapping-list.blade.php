<div>
    <div class="row">
        <div class="col-lg-3">
            <div class="form-group">
                <label for="upload_file">UPLOAD</label>
                <input type="file" class="form-control" wire:model.live="upload_file">
            </div>
        </div>
        <div class="col-lg-12">
            <p>
                <a href="{{asset('/templates/channel-mapping-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading channel mapping data.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Channel Mapping<i class="ml-2 fa fa-spinner fa-spin" wire:loading></i></h5>
            <div class="card-tools">
                <button class="btn btn-info btn-xs" wire:click.prevent="addRow" wire:loading.attr="disabled">
                    <i class="fa fa-plus" wire:loading.remove></i>
                    <i class="fa fa-spinner fa-spin" wire:loading></i>
                    ADD
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>Channel Code</th>
                        <th>Channel Name</th>
                        <th>External Channel Code</th>
                        <th>External Channel Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mapping_arr as $key => $mapping)
                        <tr>
                            <td class="p-0 text-center align-middle">
                                <select class="form-control form-contol-sm border-0" wire:model.live="mapping_arr.{{ $key }}.channel_id">
                                    <option value="">- select channel -</option>
                                    @foreach($channels as $channel)
                                        <option value="{{ $channel->id }}">[{{ $channel->code ?? '' }}] {{ $channel->name ?? ''}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-0 text-center align-middle">
                                {{ $channels->firstWhere('id', $mapping['channel_id'])->name ?? '' }}
                            </td>
                            <td class="p-0 text-center align-middle">
                                <input type="text" class="form-control border-0" wire:model.live="mapping_arr.{{ $key }}.external_channel_code">
                            </td>
                            <td class="p-0 text-center align-middle">
                                <input type="text" class="form-control border-0" wire:model.live="mapping_arr.{{ $key }}.external_channel_name">
                            </td>
                            <td class="p-0 text-center align-middle">
                                <button class="btn btn-danger btn-xs" wire:click.prevent="removeRow({{ $key }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
