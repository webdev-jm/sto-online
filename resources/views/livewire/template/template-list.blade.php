<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">TEMPLATES</h3>
        </div>
        <div class="card-body">
            @if(!$addTemplate)
                <a href="#" wire:click.prevent="addTemplate">
                    <i class="fa fa-plus fa-sm"></i>
                    Add new template
                </a>
            @else
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" placeholder="Title" wire:model.live="title">
                    <div class="input-group-append">
                        <button class="input-group-text btn btn-success" wire:click.prevent="saveTemplate">
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="input-group-text btn btn-danger" wire:click.prevent="cancelAdd">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

            <ul class="list-group flex-column">
                @foreach($templates as $key => $template)
                    <a href="#" class="list-group-item py-1 px-2{{!empty($selectedTemplate) && $selectedTemplate->id == $template->id ? ' active' : ''}}" wire:click.prevent="selectTemplate({{$template->id}})">
                        {{$template->title}}
                    </a>
                @endforeach
            </ul>
        </div>
        <div class="card-footer">
            {{$templates->links(data: ['scrollTo' => false])}}
        </div>
    </div>
</div>
