<div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INVENTORIES</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            
            <table class="table table-stiped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>SKU CODE</th>
                        <th>DESCRIPTION</th>
                        <th>UOM</th>
                        @foreach($location_ids as $location_id)
                            <th>{{$location_id->location->code}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $inventory)
                    <tr>
                        <td>{{$inventory->stock_code}}</td>
                        <td>{{$inventory->description}}</td>
                        <td>{{$inventory->uom}}</td>
                        @foreach($location_ids as $location_id)
                            <td>{{$inventory->{'location_'.$location_id->location_id} }}</td>
                        @endforeach
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
