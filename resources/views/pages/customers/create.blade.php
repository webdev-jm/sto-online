@extends('adminlte::page')

@section('title', 'Add Customer - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - ADD CUSTOMER</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['customer.store'], 'id' => 'add_customer', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ADD CUSTOMER</h3>
        </div>
        <div class="card-body">

            <div class="row">

                {{-- CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Customer Code') !!}
                        {!! Form::text('code', '', ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Customer Name') !!}
                        {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

                {{-- SALESMAN --}}
                <div class="col-lg-3">
                    <div class="form-group">
                        {!! Form::label('salesman_id', 'Salesman') !!}
                        {!! Form::select('salesman_id', $salesmen, NULL, ['id' => 'salesman_id', 'class' => 'form-control'.($errors->has('salesman_id') ? ' is-invalid' : ''), 'form' => 'add_customer', 'placeholder' => '- select salesman -']) !!}
                        <p class="text-danger">{{$errors->first('salesman_id')}}</p>
                    </div>
                </div>
                @can('salesman create')
                <div class="col-lg-1 d-flex align-items-center" style="padding-top: 4px;">
                    <button type="button" class="btn btn-outline-primary btn-sm btn-block" id="btn-new-salesman" title="Create new salesman">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                @endcan

                {{-- CHANNEL --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('channel_id', 'Channel') !!}
                        {!! Form::select('channel_id', $channels, NULL, ['class' => 'form-control'.($errors->has('channel_id') ? ' is-invalid' : ''), 'form' => 'add_customer', 'placeholder' => '- select channel -']) !!}
                        <p class="text-danger">{{$errors->first('channel_id')}}</p>
                    </div>
                </div>

            </div>

            {{-- DISTRICT / AREA (read-only, auto-populated from salesman) --}}
            <div class="row" id="salesman-territory-row" style="display: none !important;">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>District</label>
                        <input type="text" id="salesman_district" class="form-control" disabled placeholder="—">
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Areas</label>
                        <input type="text" id="salesman_areas" class="form-control" disabled placeholder="—">
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                {{-- ADDRESS --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('address', 'Address') !!}
                        {!! Form::text('address', '', ['class' => 'form-control'.($errors->has('address') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('address')}}</p>
                    </div>
                </div>
                {{-- STREET --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('street', 'Street') !!}
                        {!! Form::text('street', '', ['class' => 'form-control'.($errors->has('street') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('street')}}</p>
                    </div>
                </div>
                {{-- REGION (filter only, not stored) --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Region</label>
                        <select id="region_id" class="form-control">
                            <option value="">— select region —</option>
                            @foreach($regions as $r)
                                <option value="{{ $r->id }}">{{ $r->region_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- PROVINCE --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Province <span class="text-danger">*</span></label>
                        <select id="province_id" name="province_id" form="add_customer"
                            class="form-control {{ $errors->has('province_id') ? 'is-invalid' : '' }}" disabled>
                            <option value="">— select province —</option>
                        </select>
                        <p class="text-danger">{{ $errors->first('province_id') }}</p>
                    </div>
                </div>
                {{-- CITY/TOWN --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>City/Town <span class="text-danger">*</span></label>
                        <select id="municipality_id" name="municipality_id" form="add_customer"
                            class="form-control {{ $errors->has('municipality_id') ? 'is-invalid' : '' }}" disabled>
                            <option value="">— select city/town —</option>
                        </select>
                        <p class="text-danger">{{ $errors->first('municipality_id') }}</p>
                    </div>
                </div>
                {{-- BARANGAY --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Barangay <span class="text-danger">*</span></label>
                        <select id="barangay_id" name="barangay_id" form="add_customer"
                            class="form-control {{ $errors->has('barangay_id') ? 'is-invalid' : '' }}" disabled>
                            <option value="">— select barangay —</option>
                        </select>
                        <p class="text-danger">{{ $errors->first('barangay_id') }}</p>
                    </div>
                </div>
                {{-- POSTAL CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('postal_code', 'Postal Code') !!}
                        {!! Form::text('postal_code', '', ['class' => 'form-control'.($errors->has('postal_code') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('postal_code')}}</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Add Customer', ['class' => 'btn btn-primary', 'form' => 'add_customer']) !!}
        </div>
    </div>

    @livewire('customer.salesman-quick-create')
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function() {
        function loadSalesmanTerritory(salesmanId) {
            if (!salesmanId) {
                $('#salesman-territory-row').hide();
                return;
            }
            $.get('{{ route("customer.salesman.info", ":id") }}'.replace(':id', salesmanId), function(data) {
                if (data.district_code) {
                    $('#salesman_district').val(data.district_code);
                    var areas = data.areas.map(function(a) { return '[' + a.code + '] ' + a.name; }).join(', ');
                    $('#salesman_areas').val(areas || '—');
                    $('#salesman-territory-row').show();
                } else {
                    $('#salesman_district').val('—');
                    $('#salesman_areas').val('—');
                    $('#salesman-territory-row').show();
                }
            });
        }

        $('#salesman_id').on('change', function() {
            loadSalesmanTerritory($(this).val());
        });

        $('#btn-new-salesman').on('click', function() {
            Livewire.dispatch('openSalesmanModal');
        });

        window.addEventListener('salesmanCreated', function(event) {
            var id = event.detail.id;
            var label = event.detail.label;
            var $select = $('#salesman_id');
            if ($select.find('option[value="' + id + '"]').length === 0) {
                $select.append(new Option(label, id));
            }
            $select.val(id).trigger('change');
        });

        // Cascading address dropdowns
        $('#region_id').on('change', function () {
            var rid = $(this).val();
            $('#province_id, #municipality_id, #barangay_id').val('').prop('disabled', true);
            if (!rid) { return; }
            $.getJSON('{{ route("customer.location.provinces", ":r") }}'.replace(':r', rid), function (data) {
                var opts = data.map(function(p) { return '<option value="'+p.id+'">'+p.province_name+'</option>'; }).join('');
                $('#province_id').html('<option value="">— select province —</option>' + opts).prop('disabled', false);
            });
        });

        $('#province_id').on('change', function () {
            var pid = $(this).val();
            $('#municipality_id, #barangay_id').val('').prop('disabled', true);
            if (!pid) { return; }
            $.getJSON('{{ route("customer.location.municipalities", ":p") }}'.replace(':p', pid), function (data) {
                var opts = data.map(function(m) { return '<option value="'+m.id+'">'+m.municipality_name+'</option>'; }).join('');
                $('#municipality_id').html('<option value="">— select city/town —</option>' + opts).prop('disabled', false);
            });
        });

        $('#municipality_id').on('change', function () {
            var mid = $(this).val();
            $('#barangay_id').val('').prop('disabled', true);
            if (!mid) { return; }
            $.getJSON('{{ route("customer.location.barangays", ":m") }}'.replace(':m', mid), function (data) {
                var opts = data.map(function(b) { return '<option value="'+b.id+'">'+b.barangay_name+'</option>'; }).join('');
                $('#barangay_id').html('<option value="">— select barangay —</option>' + opts).prop('disabled', false);
            });
        });
    });
</script>
@stop
