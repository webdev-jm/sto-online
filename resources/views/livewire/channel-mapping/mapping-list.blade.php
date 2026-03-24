<div>
    <div class="row">
        <div class="col-lg-3">
            <div class="form-group">
                <label for="upload_file">UPLOAD</label>
                <input type="file" class="form-control" wire:model.live="upload_file">
            </div>
        </div>
        <div class="col-lg-12">
            <p>
                <a href="{{asset('/templates/channel-mapping-template.xlsx')}}"><i class="fa fa-download fa-sm mr-1"></i>Download</a> the template for uploading channel mapping data.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Channel Mapping<i class="ml-2 fa fa-spinner fa-spin" wire:loading></i></h5>
            <div class="card-tools">
                <button class="btn btn-info btn-xs" wire:click.prevent="addRow" wire:loading.attr="disabled">
                    <i class="fa fa-plus" wire:loading.remove></i>
                    <i class="fa fa-spinner fa-spin" wire:loading></i>
                    ADD
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th>Channel Code</th>
                        <th>Channel Name</th>
                        <th>External Channel Code</th>
                        <th>External Channel Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mapping_arr as $key => $mapping)
                        <tr>
                            <td class="p-0 text-center align-middle">
                                <select class="form-control form-contol-sm border-0" wire:model.live="mapping_arr.{{ $key }}.channel_id">
                                    <option value="">- select channel -</option>
                                    @foreach($channels as $channel)
                                        <option value="{{ $channel->id }}">[{{ $channel->code ?? '' }}] {{ $channel->name ?? ''}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-0 text-center align-middle">
                                {{ $channels->firstWhere('id', $mapping['channel_id'])->name ?? '' }}
                            </td>
                            <td class="p-0 text-center align-middle">
                                <input type="text" class="form-control border-0" wire:model.live="mapping_arr.{{ $key }}.external_channel_code">
                            </td>
                            <td class="p-0 text-center align-middle">
                                <input type="text" class="form-control border-0" wire:model.live="mapping_arr.{{ $key }}.external_channel_name">
                            </td>
                            <td class="p-0 text-center align-middle">
                                <button class="btn btn-danger btn-xs" wire:click.prevent="removeRow({{ $key }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@assets
    <style>
        /* --- GLASS CONTAINERS --- */
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

        /* --- TYPOGRAPHY & HEADERS --- */
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

        /* --- TABLE STYLING --- */
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

        /* --- INPUTS & SELECTS --- */
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

        /* --- DARK MODE ADAPTATION --- */
        .dark-mode .glass-card {
            background: rgba(28, 28, 30, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        .dark-mode .card-title, .dark-mode .glass-table tbody td { color: #f5f5f7 !important; }
        .dark-mode .glass-table thead th { background: rgba(255,255,255,0.03); color: #86868b !important; }
        .dark-mode .glass-table thead th, .dark-mode .glass-table tbody td { border-color: rgba(255,255,255,0.08) !important; }
    </style>
@endassets
