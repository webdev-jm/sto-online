<?php

namespace App\Http\Livewire\Account;

use Livewire\Component;

use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;
use App\Models\AccountUploadTemplateField;

class AccountTemplateCreate extends Component
{
    public $account;
    public $templates;
    public $template_id;
    public $selectedTemplate;
    public $template_fields;
    public $account_template_fields;
    public $err;

    public function save() {
        $this->validate([
            'template_id' => [
                'required'
            ],
        ]);

        // check data
        if(!empty($this->account_template_fields)) {
            $this->err = array();
            foreach($this->template_fields as $field) {
                if(empty($this->account_template_fields[$field->id])) {
                    $this->err[$field->id] = 'Template field name is required';
                }
            }

            // save
            if(empty($this->err)) {
                $account_template = new AccountUploadTemplate([
                    'account_id' => $this->account->id,
                    'upload_template_id' => $this->template_id,
                ]);
                $account_template->save();

                foreach($this->template_fields as $field) {
                    $account_template_field = new AccountUploadTemplateField([
                        'account_upload_template_id' => $account_template->id,
                        'upload_template_field_id' => $field->id,
                        'number' => $field->number,
                        'file_column_name' => $this->account_template_fields[$field->id]['name'],
                    ]);
                    $account_template_field->save();
                }
            }
        }

    }

    public function updatedTemplateId() {
        if(!empty($this->template_id)) {
            $this->selectedTemplate = $this->templates->where('id', $this->template_id)->first();
            $this->template_fields = $this->selectedTemplate->fields()->orderBy('number', 'ASC')->get();
        } else {
            $this->reset('selectedTemplate', 'template_fields', 'account_template_fields', 'err');
        }
    }

    public function mount($account) {
        $this->account = $account;

        $this->templates = UploadTemplate::get();
    }

    public function render()
    {
        return view('livewire.account.account-template-create');
    }
}
