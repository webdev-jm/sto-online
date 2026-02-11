<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">STOCK ON HAND UPLOAD</h3>
        </div>
        <div class="card-body">
            
            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">YEAR</label>
                        <input type="number" class="form-control" placeholder="Year" wire:model.live="year">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">MONTH</label>
                        <input type="number" class="form-control" placeholder="Month" wire:model.live="month">
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="">UPLOAD FILE</label>
                        <input type="file" class="form-control" wire:model.live="upload_file">
                    </div>
                </div>

                <div class="col-lg-12">
                    <button class="btn btn-primary btn-sm" wire:click.prevent="checkFile" wire:loading.attr="disabled">
                        <i class="fa fa-spinner fa-spin fa-sm" wire:loading wire:target="checkFile"></i>
                        <i class="fa fa-check fa-sm" wire:loading.remove wire:target="checkFile"></i>
                        CHECK
                    </button>
                </div>
                
            </div>

        </div>
    </div>

    @if(!empty($success_msg))
    <div class="alert alert-success">
        {{$success_msg}}
    </div>
    @endif

    @if(!empty($data))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UPLOAD PREVIEW <i class="fa fa-spinner fa-spin fa-sm" wire:loading></i></h3>
            <div class="card-tools">
                <button class="btn btn-info btn-sm" wire:loading.attr="disabled" wire:target="checkFile" wire:click.prevent="save">
                    <i class="fa fa-upload fa-sm mr-1"></i>
                    UPLOAD
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr class="bg-gray">
                            <th>CUSTOMER CODE</th>
                            <th>CUSTOMER NAME</th>
                            <th>SKU CODE</th>
                            <th>OTHER SKU CODE</th>
                            <th>PRODUCT DESCRIPTION</th>
                            <th class="text-right">INVENTORY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedData as $data)
                            <tr>
                                <th>{{$data['customer_code']}}</th>
                                <th>{{$data['customer_name']}}</th>
                                <td>{{$data['sku_code']}}</td>
                                <td>{{$data['sku_code_other']}}</td>
                                <td>{{$data['product_description']}}</td>
                                <td class="text-right">{{number_format((float)$data['inventory'])}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$paginatedData->links()}}
        </div>
    </div>
    @endif

</div>
