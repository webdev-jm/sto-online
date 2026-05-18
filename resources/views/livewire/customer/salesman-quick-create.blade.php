<div>
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-user-tie mr-2"></i>New Salesman</h5>
                    <button type="button" class="close text-white" wire:click="closeModal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- SALESMAN FIELDS --}}
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Salesman Code <span class="text-danger">*</span></label>
                                <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="Code">
                                @error('code') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Salesman Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Name">
                                @error('name') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-control @error('type') is-invalid @enderror">
                                    <option value="">- select type -</option>
                                    @foreach($salesmanTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- DISTRICT --}}
                    <div class="row align-items-end">
                        <div class="col-lg-8">
                            <div class="form-group mb-0">
                                <label>District</label>
                                <select wire:model="district_id" class="form-control @error('district_id') is-invalid @enderror">
                                    <option value="">- no district -</option>
                                    @foreach($districts as $id => $code)
                                        <option value="{{ $id }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                                @error('district_id') <p class="text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-lg-4 pb-3">
                            <button type="button" wire:click="toggleDistrictForm" class="btn btn-sm btn-{{ $showDistrictForm ? 'secondary' : 'outline-primary' }} btn-block">
                                <i class="fas fa-{{ $showDistrictForm ? 'times' : 'plus' }} mr-1"></i>{{ $showDistrictForm ? 'Cancel' : 'New District' }}
                            </button>
                        </div>
                    </div>

                    @if($districtCreatedMessage)
                        <small class="text-success"><i class="fas fa-check-circle mr-1"></i>{{ $districtCreatedMessage }}</small>
                    @endif

                    @if($showDistrictForm)
                    <div class="card card-outline card-primary mt-2">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0">Create District</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row align-items-end">
                                <div class="col-lg-8">
                                    <div class="form-group mb-0">
                                        <label>District Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="districtCode" class="form-control @error('districtCode') is-invalid @enderror" placeholder="e.g. D001">
                                        @error('districtCode') <p class="text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <button type="button" wire:click="saveDistrict" class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-save mr-1"></i>Create District
                                    </button>
                                </div>
                            </div>

                            {{-- Assign existing areas to this district --}}
                            @if(count($areas))
                            <div class="mt-3">
                                <label class="d-block mb-1"><small class="text-muted">Assign areas to this district:</small></label>
                                <div class="row">
                                    @foreach($areas as $area)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                wire:model="selectedAreas"
                                                value="{{ $area['id'] }}"
                                                id="area_chk_{{ $area['id'] }}">
                                            <label class="form-check-label" for="area_chk_{{ $area['id'] }}">
                                                <strong>{{ $area['code'] }}</strong> {{ $area['name'] }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <p class="text-muted mt-2 mb-0"><small>No areas found. Create one below first.</small></p>
                            @endif

                        </div>
                    </div>
                    @endif

                    <hr class="mt-3">

                    {{-- AREA --}}
                    <div class="row align-items-center mt-2">
                        <div class="col-lg-8">
                            @if($district_id)
                                <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>New areas will be assigned to the selected district.</small>
                            @else
                                <small class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Select or create a district first to auto-assign the area.</small>
                            @endif
                        </div>
                        <div class="col-lg-4">
                            <button type="button" wire:click="toggleAreaForm" class="btn btn-sm btn-{{ $showAreaForm ? 'secondary' : 'outline-info' }} btn-block">
                                <i class="fas fa-{{ $showAreaForm ? 'times' : 'plus' }} mr-1"></i>{{ $showAreaForm ? 'Cancel' : 'New Area' }}
                            </button>
                        </div>
                    </div>

                    @if($areaCreatedMessage)
                        <small class="text-success"><i class="fas fa-check-circle mr-1"></i>{{ $areaCreatedMessage }}</small>
                    @endif

                    @if($showAreaForm)
                    <div class="card card-outline card-info mt-2">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0">Create Area</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row align-items-end">
                                <div class="col-lg-4">
                                    <div class="form-group mb-0">
                                        <label>Area Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="areaCode" class="form-control @error('areaCode') is-invalid @enderror" placeholder="e.g. A001">
                                        @error('areaCode') <p class="text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group mb-0">
                                        <label>Area Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="areaName" class="form-control @error('areaName') is-invalid @enderror" placeholder="e.g. North District">
                                        @error('areaName') <p class="text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <button type="button" wire:click="saveArea" class="btn btn-info btn-sm btn-block">
                                        <i class="fas fa-save mr-1"></i>Create Area
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                <div class="modal-footer">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="button" wire:click="save" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Save Salesman
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif
</div>
