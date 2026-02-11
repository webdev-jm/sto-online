<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-lg">VMI REPORT</h3>
        </div>
        <div class="card-body">

            <strong class="text-lg">PARAMETERS</strong>
            <hr class="mt-0 mb-1">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="year">YEAR</label>
                        <input type="number" class="form-control" wire:model.live="year">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="month">MONTH</label>
                        <input type="number" class="form-control" wire:model.live="month">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="parameter">PARAMETER</label>
                        <input type="number" class="form-control" wire:model.live="parameter" max="12" min="1">
                    </div>
                </div>

                {{-- <div class="col-lg-3">
                    <div class="form-group">
                        <label for="months">MONTHS (AVG.)</label>
                        <select id="months" class="form-control" wire:model.live="month_param">
                            @foreach($months_arr as $key => $val)
                                <option value="{{$key}}">{{$val}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>   --}}
            </div>

            <strong class="text-lg">FILTERS</strong>
            <hr class="mt-0 mb-1">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="search">SEARCH</label>
                        <input type="text" class="form-control" wire:model.live="search" placeholder="Search">
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    {{$inventories->links()}}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center align-middle p-0 px-1">STOCK CODE</th>
                                <th rowspan="2" class="text-center align-middle p-0 px-1">DESCRIPTION</th>
                                <th rowspan="2" class="text-center align-middle p-0 px-1">INV TOTAL CS</th>
                                <th colspan="4" class="text-center align-middle p-0 bg-info">1 MONTH</th>
                                <th colspan="4" class="text-center align-middle p-0 bg-info">2 MONTHS</th>
                                <th colspan="4" class="text-center align-middle p-0 bg-info">3 MONTHS</th>
                            </tr>
                            <tr>
                                <th class="text-center p-0">STO CS</th>
                                <th class="text-center p-0">WEEK COV</th>
                                <th class="text-center p-0">WEEKS COV NEEDED</th>
                                <th class="text-center p-0">TO ORDER</th>
                                <th class="text-center p-0">STO CS</th>
                                <th class="text-center p-0">WEEK COV</th>
                                <th class="text-center p-0">WEEKS COV NEEDED</th>
                                <th class="text-center p-0">TO ORDER</th>
                                <th class="text-center p-0">STO CS</th>
                                <th class="text-center p-0">WEEK COV</th>
                                <th class="text-center p-0">WEEKS COV NEEDED</th>
                                <th class="text-center p-0">TO ORDER</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventories as $inventory)
                                <tr>
                                    <td class="text-left p-0 pl-1 align-middle">
                                        {{$data[$inventory->product_id]['stock_code']}}
                                    </td>
                                    <td class="text-left p-0 pl-1 align-middle">
                                        {{$data[$inventory->product_id]['description']}}
                                    </td>
                                    <td class="text-right p-0 pr-1 align-middle">
                                        {{number_format($data[$inventory->product_id]['cs_total'], 2)}}
                                    </td>
                                    @foreach($data[$inventory->product_id]['months_data'] as $key => $val)
                                        <td class="text-right p-0 pr-1 align-middle">
                                            {{number_format($val['sto'], 2)}}
                                        </td>
                                        <td class="text-right p-0 pr-1 align-middle">
                                            {{number_format($val['w_cov'], 2)}}
                                        </td>
                                        <td class="text-right p-0 pr-1 align-middle">
                                            {{number_format($val['w_cov_needed'], 2)}}
                                        </td>
                                        <td class="text-right p-0 pr-1 align-middle font-weight-bold{{$val['vmi'] < 1 ? ' text-danger' : ' text-success'}}">
                                            {{number_format($val['vmi'], 2)}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        <div class="card-footer">
            {{$inventories->links()}}
        </div>
    </div>
</div>
