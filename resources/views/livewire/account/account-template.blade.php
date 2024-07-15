<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">TEMPLATES</h3>
            <div class="card-tools">
                <a href="{{route('account.create-template', encrypt($account->id))}}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus mr-1"></i>
                    ADD TEMPLATE
                </a>
            </div>
        </div>
        <div class="card-body">
            <ul class="list-group flex-column">
                @foreach($account_templates as $template)
                <a href="{{route('account.template-edit', encrypt($template->id))}}" class="list-group-item py-1">
                    {{$template->upload_template->title}}
                </a>
                @endforeach
            </ul>
        </div>
        <div class="card-footer">
            {{$account_templates->links(data: ['scrollTo' => false])}}
        </div>
    </div>
</div>
