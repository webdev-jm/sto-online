<?php

namespace App\Http\Livewire\Template;

use Livewire\Component;
use App\Models\UploadTemplate;

class TemplateDetail extends Component
{
    public $template;
    public $lines;

    protected $listeners = [
        'selectTemplate' => 'setTemplate'
    ];

    public function setTemplate($template_id) {
        $this->template = UploadTemplate::find($template_id);
        if(!empty($this->template->fields)) {
            $this->lines = array();
            foreach($this->template->fields as $field) {
                $this->lines[] = [
                    'column_name' => $field->column_name,
                    'column_name_alt' => $field->column_name_alt,
                ];
            }
        }
    }

    public function addLine() {
        $this->lines[] = [
            'column_name' => '',
            'column_name_alt' => ''
        ];
    }

    public function mount() {
        $this->lines[] = [
            'column_name' => '',
            'column_name_alt' => ''
        ];
    }

    public function render()
    {
        return view('livewire.template.template-detail');
    }
}
