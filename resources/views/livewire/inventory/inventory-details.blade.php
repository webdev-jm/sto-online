<div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">INVENTORIES</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            
            <table class="table table-stiped table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>SKU CODE</th>
                        <th>DESCRIPTION</th>
                        <th>UOM</th>
                        @foreach($location_ids as $location_id)
                            <th>{{$location_id->location->code ?? '-'}}</th>
                        @endforeach
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
                        <td class="align-middle text-center">{{$inventory->uom}}</td>
                        @foreach($location_ids as $location_id)
                            <td class="align-middle text-right">{{ number_format($inventory->{'location_'.$location_id->location_id}) }}</td>
                        @endforeach
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
