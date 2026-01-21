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
                <a href="{{asset('/templates/product-mapping-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading product mapping data.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Product Mapping<i class="ml-2 fa fa-spinner fa-spin" wire:loading></i></h5>
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
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>External Stock Code</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mapping_arr as $key => $mapping)
                        <tr>
                            <td class="p-0 text-center align-middle">
                                <select class="form-control form-contol-sm border-0" wire:model.live="mapping_arr.{{ $key }}.product_id">
                                    <option value="">- select product -</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->stock_code }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-0 text-center align-middle">
                                {{ $products->firstWhere('id', $mapping['product_id'])->description ?? '' }} - {{ $products->firstWhere('id', $mapping['product_id'])->size ?? '' }}
                            </td>
                            <td class="p-0 text-center align-middle">
                                <input type="text" class="form-control border-0" wire:model.live="mapping_arr.{{ $key }}.external_stock_code">
                            </td>
                            <td>
                                <select class="form-control form-control-sm border-0" wire:model.live="mapping_arr.{{ $key }}.type">
                                    <option value="1">REGULAR</option>
                                    <option value="2">FREE GOODS</option>
                                    <option value="3">PROMO</option>
                                </select>
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
