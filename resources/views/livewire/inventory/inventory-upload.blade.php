<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UPLOAD INVENTORY DATA</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-6">
                    <label for="">Upload File</label>
                    <input type="file" class="form-control" wire:model="file">
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DETAILS<i class="fa fa-spinner fa-spin ml-1" wire:loading></i></h3>
            @if(!empty($inventory_data))
                <div wire:loading.remove class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="uploadData"><i class="fa fa-upload mr-1"></i>Upload Inventory</button>
                </div>
            @endif
        </div>
        <div class="card-body p-0 table-responsive">

            @if(!empty($inventory_data))
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr class="text-center">
                            <th></th>
                            <th class="align-middle">SKU CODE</th>
                            <th class="align-middle">DESCRIPTION</th>
                            @if(!empty($keys))
                                @foreach($keys as $key => $loc)
                                    <th class="align-middle">{{$loc['code']}}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedData as $inv_data)
                        <tr>
                            <td class="text-center p-0 align-middle">
                                @switch($inv_data['check'])
                                    @case(0)
                                        <i class="fa fa-check-circle fa-sm text-success"></i>
                                        @break
                                    @case(1)
                                        <i class="fa fa-times-circle fa-sm text-danger"></i>
                                        <small class="text-danger">Product</small>
                                        @break
                                    @default
                                @endswitch
                            </td>
                            <td class="align-middle">{{$inv_data['sku_code']}}</td>
                            <td class="align-middle">{{$inv_data['description']}}</td>
                            @if(!empty($keys))
                                @foreach($keys as $key => $loc)
                                    <td class="text-right align-middle">{{number_format($inv_data[$loc['id']])}}</td>
                                @endforeach
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
        @if(!empty($inventory_data))
        <div class="card-footer">
            {{$paginatedData->links()}}
        </div>
        @endif
    </div>
</div>
