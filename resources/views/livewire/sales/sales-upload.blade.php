<div>

    @if(!empty($err_msg))
    <div class="alert alert-danger">
        {{$err_msg}}
    </div>
    @endif

    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">UPLOAD SALES DATA</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">Upload File</label>
                        <input type="file" class="form-control{{!empty($err_msg) ? ' is-invalid' : ''}}" wire:model="file">
                        @if(!empty($err_msg))
                            <small class="text-danger">{{$err_msg}}</small>
                        @endif
                    </div>
                </div>

                {{-- UPLOAD COLUMNS --}}
                <div class="col-lg-12">
                    <ul>
                        <li>
                            <b>POSTING DATE</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>DOCUMENT NO.</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>CUSTOMER CODE</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>TYPE</b> - <span>Optional</span>
                        </li>
                        <li>
                            <b>LOCATION CODE</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>SKU CODE</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>DESCRIPTION</b> - <span>Optional</span>
                        </li>
                        <li>
                            <b>DESCRIPTION 2</b> - <span>Optional</span>
                        </li>
                        <li>
                            <b>ITEM CATEGORY CODE</b> - <span>Optional</span>
                        </li>
                        <li>
                            <b>QUANTITY</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>UNIT OF MEASURE CODE</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>UNIT PRICE INCL. VAT</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>AMOUNT</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>AMOUNT INCLUDING VAT</b> - <span>Required</span>
                        </li>
                        <li>
                            <b>LINE DISCOUNT</b> - <span>Optional</span>
                        </li>
                    </ul>

                    <p>
                        <a href="{{asset('/templates/sales-upload-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading sales data.
                    </p>
                </div>
            </div>

        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">NOTE:</h3>
            <div class="card-tools" wire:loading.remove >
                <button class="btn btn-primary btn-sm" wire:click.prevent="saveUpload"><i class="fa fa-save mr-1"></i>Save Data</button>
            </div>
            <div class="card-tools" wire:loading>
                <button class="btn btn-primary btn-sm"><i class="fa fa-spinner fa-spin mr-1"></i>Loading..</button>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-0">
                All lines marked with <i class="fa fa-times-circle fa-sm text-danger"></i> will not be uploaded and only lines marked with <i class="fa fa-check-circle fa-sm text-success"></i> will be uploaded.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UPLOAD PREVIEW <span wire:loading><i class="fa fa-spinner fa-spin ml-1"></i> Loading</span></h3>
            @if(!empty($sales_data))
                <div class="card-tools">
                    <b>COUNT: {{count($sales_data)}}</b>
                </div>
            @endif
        </div>
        <div class="card-body table-responsive py-0 px-1">
            @if(!empty($sales_data))
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-1"></th>
                            <th class="align-middle p-1">DATE</th>
                            <th class="align-middle">DOCUMENT NO.</th>
                            <th class="align-middle">CUSTOMER CODE</th>
                            <th class="align-middle">LOCATION</th>
                            <th class="align-middle">SKU CODE</th>
                            <th class="align-middle">DESCRIPTION</th>
                            <th class="align-middle">QUANTITY</th>
                            <th class="align-middle">UOM</th>
                            <th class="align-middle">PRICE INCL. VAT</th>
                            <th class="align-middle">AMOUNT</th>
                            <th class="align-middle">AMOUNT INCL. VAT</th>
                            <th class="align-middle">LINE DISCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedData as $key => $data)
                            <tr class="text-center">
                                {{-- CHECK --}}
                                <td class="align-middle p-1">
                                    @if($data['check'] == 0)
                                        <i class="fa fa-check-circle fa-sm text-success"></i>
                                    @else
                                        <i class="fa fa-times-circle fa-sm text-danger"></i>
                                        @switch($data['check'])
                                            @case(1)
                                                {{-- Customer --}}
                                                <small class="text-danger">Customer</small>
                                                @break
                                            @case(2)
                                                {{-- Location --}}
                                                <small class="text-danger">Location</small>
                                                @break
                                            @case(3)
                                                {{-- Product --}}
                                                <small class="text-danger">Product</small>
                                                @break
                                            @case(4)
                                                {{-- Existed --}}
                                                <small class="text-danger">Exists</small>
                                                @break
                                            @default
                                                
                                        @endswitch
                                    @endif
                                </td>
                                {{-- DATE --}}
                                <td class="align-middle p-1">
                                    {{date('Y-m-d', strtotime($data['date'])) ?? '-'}}
                                </td>
                                {{-- DOCUMENT --}}
                                <td class="align-middle">
                                    {{$data['document'] ?? '-'}}
                                </td>
                                {{-- CUSTOMER --}}
                                <td class="align-middle">
                                    @if($data['check'] == '1')
                                        <a href="#" wire:click.prevent="maintainCustomer('{{$data['customer_code'] ?? ''}}')">{{$data['customer_code'] ?? '-'}}</a>
                                    @else
                                        {{$data['customer_code'] ?? '-'}}
                                    @endif
                                </td>
                                {{-- LOCATION --}}
                                <td class="align-middle">
                                    @if($data['check'] == 2)
                                        <a href="#" wire:click.prevent="maintainLocation('{{$data['location_code'] ?? ''}}')">{{$data['location_code'] ?? '-'}}</a>
                                    @else
                                        {{$data['location_code'] ?? '-'}}
                                    @endif
                                </td>
                                {{-- SKU CODE --}}
                                <td class="align-middle">
                                    {{$data['sku_code'] ?? '-'}}
                                </td>
                                {{-- SKU DESCRIPTION --}}
                                <td class="align-middle">
                                    {{$data['description'] ?? '-'}} {{$data['size'] ?? '-'}}
                                </td>
                                {{-- QUANTITY --}}
                                <td class="align-middle text-right">
                                    {{number_format($data['quantity']) ?? '-'}}
                                </td>
                                {{-- UOM --}}
                                <td class="align-middle">
                                    {{$data['uom'] ?? '-'}}
                                </td>
                                {{-- PRICE INC VAT --}}
                                <td class="align-middle text-right">
                                    {{number_format($data['price_inc_vat'], 2) ?? '-'}}
                                </td>
                                {{-- AMOUNT --}}
                                <td class="align-middle text-right">
                                    {{number_format($data['amount'], 2) ?? '-'}}
                                </td>
                                {{-- AMOUNT INC VAT --}}
                                <td class="align-middle text-right">
                                    {{number_format($data['amount_inc_vat'], 2) ?? '-'}}
                                </td>
                                {{-- LINE DISCOUNT --}}
                                <td class="align-middle text-right">
                                    {{number_format($data['line_discount'], 2) ?? '-'}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @if(!empty($sales_data))
            <div class="card-footer">
                {{$paginatedData->links()}}
            </div>
        @endif
    </div>
    
    <div class="modal fade" id="customer-modal">
        <div class="modal-dialog modal-lg">
            <livewire:sales.customer-maintenance/>
        </div>
    </div>

    <div class="modal fade" id="location-modal">
        <div class="modal-dialog modal-lg">
            <livewire:sales.location-maintenance/>
        </div>
    </div>

    <script>
        window.addEventListener('maintainCustomer', event => {
            $('#customer-modal').modal('show');
        });

        window.addEventListener('closeCustomerModal', event => {
            $('#customer-modal').modal('hide');
        });

        window.addEventListener('maintainLocation', event => {
            $('#location-modal').modal('show');
        });

        window.addEventListener('closeLocationModal', event => {
            $('#location-modal').modal('hide');
        });
    </script>
</div>
