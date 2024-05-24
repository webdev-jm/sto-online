<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">VMI REPORT</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">YEAR</label>
                        <input type="number" class="form-control" wire:model="year">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">MONTH</label>
                        <input type="number" class="form-control" wire:model="month">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="">PARAMETER</label>
                        <input type="number" class="form-control" wire:model="parameter">
                    </div>
                </div>

                <div class="col-lg-12 table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>STOCK CODE</th>
                                <th>DESCRIPTION</th>
                                <th>UOM</th>
                                <th class="text-center">INV TOTAL</th>
                                <th class="text-center">INV TOTAL CS</th>
                                <th class="text-center">STO</th>
                                <th class="text-center">WEEK COV</th>
                                <th class="text-center">PARAMETER</th>
                                <th class="text-center">WEEKS COV NEEDED</th>
                                <th class="text-center">VMI</th>
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
                                    <td class="text-right">{{number_format($val['vmi'], 2)}}</td>
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
