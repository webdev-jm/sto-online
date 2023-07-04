<div>
    @if($total > $count)
        <div class="progress" wire:poll.100ms>
            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="{{$count}}" aria-valuemin="0"
                aria-valuemax="{{$total}}" style="width: {{number_format(($count / $total) * 100)}}%">
                <span class="">{{number_format(($count / $total) * 100)}}% Complete</span>
            </div>
        </div>
        <p><code class="text-">uploading please wait.</code></p>
    @else
        <small class="text-success">Upload complete.</small>
    @endif
</div>
