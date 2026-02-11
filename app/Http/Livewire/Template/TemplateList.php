<?php

namespace App\Http\Livewire\Template;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UploadTemplate;

class TemplateList extends Component
{
    use WithPagination;

    public $addTemplate = false;
    public $title;
    public $selectedTemplate;

    public function selectTemplate($id) {
        $this->selectedTemplate = UploadTemplate::find($id);
        $this->dispatch('selectTemplate', $this->selectedTemplate->id);
    }

    public function saveTemplate() {
        $this->validate([
            'title' => 'required'
        ]);

        $template = new UploadTemplate([
            'title' => $this->title
        ]);
        $template->save();

        $this->addTemplate = false;
        $this->reset('title');
    }

    public function addTemplate() {
        $this->addTemplate = true;
    }

    public function cancelAdd() {
        $this->addTemplate = false;
        $this->reset('title');
    }

    public function render()
    {
        $templates = UploadTemplate::orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('livewire.template.template-list')->with([
            'templates' => $templates
        ]);
    }
}
