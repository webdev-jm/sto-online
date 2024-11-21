<div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered table-sm mb-1">
                <thead>
                    <tr class="text-center">
                        <th>SKU CODE</th>
                        <th>OTHER SKU CODE</th>
                        <th>DESCRIPTION</th>
                        <th>UOM</th>
                        <th>QUANTITY</th>
                        <th>COST</th>
                        <th>REMARKS</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                        <tr>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.sku_code">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.other_sku_code">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.description">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.uom">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.quantity">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.cost">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control border-0" wire:model="products.{{$key}}.remarks">
                            </td>
                            <td class="p-0 text-center align-middle">
                                <button class="btn btn-danger btn-xs" wire:click.prevent="removeLine({{$key}})">
                                    <i class="fa fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-lg-12 text-right">
            <button class="btn btn-xs btn-info" wire:click.prevent="addLine">
                <i class="fa fa-plus mr-1"></i>
                ADD LINE
            </button>
        </div>
    </div>
    
</div>
