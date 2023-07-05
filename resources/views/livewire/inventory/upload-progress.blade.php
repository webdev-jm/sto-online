<div>
    @if($total > $count)
        <div class="progress" wire:poll.100ms>
            <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="{{$count}}" aria-valuemin="0"
                aria-valuemax="{{$total}}" style="width: {{number_format(($count / $total) * 100)}}%">
                <span class="">{{number_format(($count / $total) * 100)}}% Complete</span>
            </div>
        </div>
        <p><code class="">uploading please wait.</code></p>
    @else
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="100" aria-valuemin="0"
                aria-valuemax="100" style="width: 100%">
                <span class="">100% Complete</span>
            </div>
        </div>
        <p><code class="text-success">upload complete.</code></p>

        <script>
            // Reload the page after 2 seconds
            setTimeout(function() {
                location.reload();
            }, 2000);
        </script>
    @endif
</div>
