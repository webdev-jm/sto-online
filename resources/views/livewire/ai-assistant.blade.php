<div style="position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;">

    {{-- Chat Panel --}}
    @if($isOpen)
        <div class="card shadow" style="width: 320px; height: 420px; display: flex; flex-direction: column; margin-bottom: 0.75rem;">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-robot mr-1"></i> AI Assistant</span>
                <button type="button" class="btn btn-sm text-white p-0" wire:click="toggle" style="line-height:1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Messages --}}
            <div class="card-body p-2" style="flex: 1; overflow-y: auto;" id="ai-chat-messages">
                @foreach($messages as $message)
                    @if($message['role'] === 'assistant')
                        <div class="d-flex mb-2">
                            <div class="mr-2">
                                <i class="fas fa-robot text-primary"></i>
                            </div>
                            <div class="bg-black rounded p-2 text-sm" style="font-size: 0.8rem; max-width: 85%;">
                                {!! nl2br(e($message['content'])) !!}
                            </div>
                        </div>
                    @else
                        <div class="d-flex mb-2 justify-content-end">
                            <div class="bg-primary text-white rounded p-2" style="font-size: 0.8rem; max-width: 85%;">
                                {{ $message['content'] }}
                            </div>
                        </div>
                    @endif
                @endforeach

                @if($isLoading)
                    <div class="d-flex mb-2">
                        <div class="mr-2">
                            <i class="fas fa-robot text-primary"></i>
                        </div>
                        <div class="bg-light rounded p-2" style="font-size: 0.8rem;">
                            <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            <span class="ml-1 text-muted">Thinking...</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input --}}
            <div class="card-footer p-2">
                <div class="input-group input-group-sm">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Ask something..."
                        wire:model="userInput"
                        wire:keydown.enter="sendMessage"
                        @if($isLoading) disabled @endif
                    >
                    <div class="input-group-append">
                        <button
                            class="btn btn-primary"
                            type="button"
                            wire:click="sendMessage"
                            wire:loading.attr="disabled"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Toggle Button --}}
    <div class="text-right">
        <button
            type="button"
            class="btn btn-primary btn-lg rounded-circle shadow"
            wire:click="toggle"
            style="width: 56px; height: 56px; padding: 0;"
            title="AI Assistant"
        >
            @if($isOpen)
                <i class="fas fa-times"></i>
            @else
                <i class="fas fa-robot"></i>
            @endif
        </button>
    </div>

    <script>
        document.addEventListener('livewire:update', function () {
            var el = document.getElementById('ai-chat-messages');
            if (el) { el.scrollTop = el.scrollHeight; }
        });
    </script>

</div>
