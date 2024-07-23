<div>
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">UPLOAD STOCK TRANSFER</h3>
            <div class="card-tools">
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">YEAR</label>
                        <input type="number" class="form-control{{$errors->has('year') ? ' is-invalid' : ''}}" wire:model="year">
                        <small class="text-danger">{{$errors->first('year')}}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">MONTH</label>
                        <input type="number" class="form-control{{$errors->has('month') ? ' is-invalid' : ''}}" wire:model="month">
                        <small class="text-danger">{{$errors->first('month')}}</small>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label for="">UPLOAD FILE</label>
                        <input type="file" class="form-control{{$errors->has('upload_file') ? ' is-invalid' : ''}}" wire:model="upload_file">
                        <small class="text-danger">{{$errors->first('upload_file')}}</small>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary btn-sm" wire:click.prevent="checkFile" wire:target="checkFile" wire:loading.attr="disabled">
                        <i class="fa fa-check fa-sm" wire:loading.remove  wire:target="checkFile"></i>
                        <i class="fa fa-spinner fa-spin fa-sm" wire:loading  wire:target="checkFile"></i>
                        CHECK
                    </button>
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            @if(!empty($data))
            <button class="btn btn-info btn-sm" wire:click.prevent="saveData" wire:loading.attr="disabled">
                <i class="fa fa-upload fa-sm"></i>
                UPLOAD
            </button>
            @endif
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
                <h3 class="card-title">
                    UPLOAD DATA PREVIEW
                    <i class="fa fa-spinner fa-spin fa-sm ml-2" wire:loading></i>
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>CUSTOMER CODE</th>
                            <th>CUSTOMER NAME</th>
                            <th>SKU CODE</th>
                            <th>SKU CODE OTHER</th>
                            <th>PRODUCT DESCRIPTION</th>
                            <th>TRANSFER IN UNITS TY</th>
                            <th>TRANSFER IN UNITS LY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedData as $val)
                        <tr>
                            <td>{{$val['customer_code']}}</td>
                            <td>{{$val['customer_name']}}</td>
                            <td>{{$val['sku_code']}}</td>
                            <td>{{$val['sku_code_other']}}</td>
                            <td>{{$val['product_description']}}</td>
                            <td>{{$val['transfer_ty']}}</td>
                            <td>{{$val['transfer_ly']}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $paginatedData->links() }}
            </div>
        </div>
    @endif

</div>
