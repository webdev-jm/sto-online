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
                        <input type="number" class="form-control" wire:model="year">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="month">MONTH</label>
                        <input type="number" class="form-control" wire:model="month">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="parameter">PARAMETER</label>
                        <input type="number" class="form-control" wire:model="parameter" max="12" min="1">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="months">MONTHS (AVG.)</label>
                        <select id="months" class="form-control" wire:model="month_param">
                            @foreach($months_arr as $key => $val)
                                <option value="{{$key}}">{{$val}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>  
            </div>

            <strong class="text-lg">FILTERS</strong>
            <hr class="mt-0 mb-1">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="search">SEARCH</label>
                        <input type="text" class="form-control" wire:model="search" placeholder="Search">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>STOCK CODE</th>
                                <th>DESCRIPTION</th>
                                <th>UOM</th>
                                <th class="text-center">INV TOTAL</th>
                                <th class="text-center">INV TOTAL CS</th>
                                <th class="text-center">STO CS</th>
                                <th class="text-center">WEEK COV</th>
                                <th class="text-center">PARAMETER</th>
                                <th class="text-center">WEEKS COV NEEDED</th>
                                <th class="text-center">VMI CS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $val)
                                <tr>
                                    <td>{{$val['stock_code']}}</td>
                                    <td>{{$val['description']}}</td>
                                    <td>{{$val['uom']}}</td>
                                    <td class="text-right">{{number_format($val['total'], 2)}}</td>
                                    <td class="text-right">{{number_format($val['cs_total'], 2)}}</td>
                                    <td class="text-right">{{number_format($val['sto'], 2)}}</td>
                                    <td class="text-right">{{number_format($val['w_cov'], 2)}}</td>
                                    <td class="text-right">{{$parameter}}</td>
                                    <td class="text-right">{{number_format($val['w_cov_needed'], 2)}}</td>
                                    <td class="text-right font-weight-bold{{$val['vmi'] < 1 ? ' text-danger' : ' text-success'}}">
                                        {{number_format($val['vmi'], 2)}}
                                    </td>
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
