<div>
    <style>
        .item-btn {
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            border-radius: 9px !important;
            font-family: "Roboto", sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all .15s;
            overflow: hidden;
        }
        .item-btn .item-code {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            opacity: 0.6;
            flex-shrink: 0;
        }
        .item-btn .item-name {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-btn .item-check {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.45);
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .item-btn.is-selected .item-code { opacity: 0.75; }

        .selection-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: "Roboto", sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--col-accent);
            background: rgba(10, 132, 255, 0.1);
            border: 1px solid rgba(10, 132, 255, 0.2);
            border-radius: 20px;
            padding: 3px 10px;
            margin-left: auto;
        }

        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: "Roboto", sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            border-radius: 8px !important;
            padding: 6px 13px;
        }

        .empty-state-inline {
            text-align: center;
            padding: 40px 16px;
            color: var(--col-subtle);
        }
        .empty-state-inline svg { opacity: 0.3; margin-bottom: 10px; }
        .empty-state-inline p { font-size: 0.82rem; margin: 0; font-family: "Roboto", sans-serif; }
        .empty-state-inline small { font-size: 0.72rem; opacity: 0.65; }
    </style>



    <div class="card mb-2">
        <div class="card-header">
            <h3 class="card-title">Accounts</h3>
            <div class="card-tools">
                <input
                    type="text"
                    class="form-control form-control-sm"
                    wire:model.live="search"
                    placeholder="Search accounts…"
                >
            </div>
        </div>

        <div class="card-body">

            @if(!empty($form_message))
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $form_message }}
                </div>
            @endif

            {{-- Toolbar --}}
            <div class="d-flex align-items-center mb-3" style="gap: 6px;">
                <button
                    class="btn btn-default toolbar-btn"
                    wire:loading.attr="disabled"
                    wire:click.prevent="clear"
                >
                    <i class="fa fa-times fa-sm mr-1"></i>
                    Clear
                </button>
                <button
                    class="btn btn-default toolbar-btn"
                    wire:loading.attr="disabled"
                    wire:click.prevent="selectAll"
                >
                    <i class="fa fa-check fa-sm mr-1"></i>
                    Select All
                </button>
                @if(!empty($selected))
                    <div class="selection-badge">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ count($selected) }} selected
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="row" style="row-gap: 6px;">
                @forelse($accounts as $account)
                    @php $isSelected = !empty($selected) && in_array($account->id, $selected); @endphp
                    <div class="col-lg-6">
                        <button
                            class="item-btn {{ $isSelected ? 'btn btn-primary is-selected' : 'btn btn-default' }}"
                            wire:loading.attr="disabled"
                            wire:click.prevent="selectAccount('{{ encrypt($account->id) }}')"
                        >
                            <span class="item-code">{{ $account->account_code }}</span>
                            <span class="item-name">{{ $account->short_name }}</span>
                            @if($isSelected)
                                <span class="item-check">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                            @endif
                        </button>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state-inline">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 10px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <p>No accounts found</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $accounts->links(data: ['scrollTo' => false]) }}
            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">
            <button
                class="btn btn-primary"
                wire:loading.attr="disabled"
                wire:click.prevent="assign"
                style="min-width: 120px;"
            >
                <span wire:loading.remove wire:target="assign">Save Accounts</span>
                <span wire:loading wire:target="assign">
                    <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                    Saving…
                </span>
            </button>
        </div>
    </div>
</div>
