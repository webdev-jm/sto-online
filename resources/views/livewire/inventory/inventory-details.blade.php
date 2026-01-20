<div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">INVENTORIES</h3>
            @can('inventory export')
                <div class="card-tools">
                    <a href="{{ route('inventory.export', encrypt($inventory_upload->id)) }}" class="btn btn-success btn-xs">
                        <i class="fa fa-file-excel"></i>
                        EXPORT
                    </a>
                </div>
            @endcan
        </div>
        <div class="card-body p-0 table-responsive">

            <table class="table table-stiped table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>SKU CODE</th>
                        <th>DESCRIPTION</th>
                        <th>LOCATION</th>
                        <th>UOM</th>
                        <th>QUANTITY</th>
                        <th>EXPIRY DATE</th>
                        @if($type == 'edit')
                            <th class="p-0"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $inventory)
                    <tr>
                        <td class="align-middle">{{$inventory->stock_code}}</td>
                        <td class="align-middle">{{$inventory->description}}</td>
                        <td class="align-middle text-center">{{$inventory->location->code}}</td>
                        <td class="align-middle text-center">{{$inventory->uom}}</td>
                        <td class="align-middle text-right">{{ number_format($inventory->inventory) }}</td>
                        <td class="align-middle text-center">{{$inventory->expiry_date}}</td>
                        @if($type == 'edit' && (auth()->user()->can('inventory edit') || auth()->user()->can('inventory delete')))
                            <td class="text-center align-middle p-0">
                                @can('inventory edit')
                                    <a href="#" class="btn btn-success btn-xs" wire:click.prevent="editLine('{{encrypt($inventory->product_id)}}')"><i class="fa fa-pen-alt fa-sm"></i></a>
                                @endcan
                                @can('inventory delete')
                                    <a href="#" class="btn btn-danger btn-xs"><i class="fa fa-trash-alt fa-sm"></i></a>
                                @endcan
                            </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="card-footer">
            {{$inventories->links()}}
        </div>
    </div>
</div>
