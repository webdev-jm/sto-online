<div>
    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-3" id="uploadMappingTabs">
        @foreach($types as $type)
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === $type ? 'active' : '' }}"
                   href="#"
                   wire:click.prevent="$set('activeTab', '{{ $type }}')">
                    {{ ucfirst($type) }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Tab Content --}}
    @foreach($types as $type)
        <div class="{{ $activeTab !== $type ? 'd-none' : '' }}">
            <div class="card glass-card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        {{ ucfirst($type) }} Upload Mapping
                        <i class="ml-2 fa fa-spinner fa-spin" wire:loading wire:target="fieldMappings,startRows"></i>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="upload-label">START ROW <small class="text-muted">(default: {{ $defaultStartRows[$type] }})</small></label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       wire:model.live="startRows.{{ $type }}"
                                       placeholder="{{ $defaultStartRows[$type] }}"
                                       min="1">
                            </div>
                        </div>
                        <div class="col-lg-9 d-flex align-items-end">
                            <small class="text-muted">
                                Leave <strong>Custom Column #</strong> blank to use the default column position. Column numbers are 0-based (first column = 0).
                            </small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm glass-table">
                            <thead>
                                <tr class="text-center">
                                    <th>Internal Field</th>
                                    <th>Default Column #</th>
                                    <th>Custom Column #</th>
                                    <th>Custom Column Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templateFieldsByType[$type] as $field)
                                    <tr>
                                        <td class="align-middle px-3">
                                            <span class="font-weight-bold">{{ $field->column_name_alt }}</span>
                                            <br><small class="text-muted">{{ $field->column_name }}</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-secondary">{{ $field->number }}</span>
                                        </td>
                                        <td class="p-0 align-middle">
                                            <input type="number"
                                                   class="form-control glass-input border-0 text-center"
                                                   wire:model.live="fieldMappings.{{ $type }}.{{ $field->id }}.file_column_number"
                                                   placeholder="{{ $field->number }}"
                                                   min="0">
                                        </td>
                                        <td class="p-0 align-middle">
                                            <input type="text"
                                                   class="form-control glass-input border-0"
                                                   wire:model.live="fieldMappings.{{ $type }}.{{ $field->id }}.file_column_name"
                                                   placeholder="{{ $field->column_name_alt }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No template fields found. Run the UploadTemplateSeeder.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@assets
    <style>
        .glass-card {
            background: var(--glass-light) !important;
            backdrop-filter: var(--glass-blur) !important;
            -webkit-backdrop-filter: var(--glass-blur) !important;
            border-radius: var(--radius) !important;
            border: 1px solid var(--glass-border) !important;
            box-shadow: var(--shadow) !important;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Syne', sans-serif !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: -0.02em;
            color: var(--col-dark);
            font-size: 0.9rem !important;
        }

        .upload-label {
            font-family: 'Syne', sans-serif !important;
            font-weight: 700;
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            color: var(--col-subtle);
            margin-bottom: 8px;
            display: block;
        }

        .glass-table {
            background: transparent !important;
            margin-bottom: 0;
        }

        .glass-table thead th {
            font-family: 'Syne', sans-serif !important;
            font-size: 0.7rem !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--col-muted);
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
            border-top: none !important;
            padding: 12px 10px !important;
            background: rgba(0,0,0,0.02);
        }

        .glass-table tbody td {
            border-color: rgba(0,0,0,0.04) !important;
            font-family: 'Figtree', sans-serif !important;
            vertical-align: middle !important;
            color: var(--col-dark);
        }

        .glass-input {
            background: transparent !important;
            border: none !important;
            font-family: 'Figtree', sans-serif !important;
            font-size: 0.85rem !important;
            padding: 10px !important;
            transition: background 0.2s;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.4) !important;
            outline: none;
            box-shadow: none;
        }

        .nav-tabs .nav-link {
            font-family: 'Syne', sans-serif !important;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--col-muted);
            border: none;
            border-bottom: 2px solid transparent;
            padding: 10px 16px;
            transition: all 0.2s;
        }

        .nav-tabs .nav-link.active {
            color: var(--col-dark) !important;
            border-bottom: 2px solid var(--col-accent, #007aff) !important;
            background: transparent !important;
        }

        .nav-tabs .nav-link:hover {
            color: var(--col-dark) !important;
        }

        .dark-mode .glass-card {
            background: rgba(28, 28, 30, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        .dark-mode .card-title,
        .dark-mode .glass-table tbody td { color: #f5f5f7 !important; }
        .dark-mode .glass-table thead th { background: rgba(255,255,255,0.03); color: #86868b !important; }
        .dark-mode .glass-table thead th,
        .dark-mode .glass-table tbody td { border-color: rgba(255,255,255,0.08) !important; }
        .dark-mode .nav-tabs .nav-link { color: #86868b; }
        .dark-mode .nav-tabs .nav-link.active { color: #f5f5f7 !important; }
    </style>
@endassets
