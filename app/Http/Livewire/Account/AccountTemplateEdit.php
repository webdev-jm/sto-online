<?php

namespace App\Http\Livewire\Account;

use Livewire\Component;

use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplateField;

class AccountTemplateEdit extends Component
{
    public $template;
    public $accountTemplate;
    public $account;
    public $template_id;
    public $template_fields;
    public $account_template_fields;
    public $err;
    public $success_msg;

    public function update() {
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
                foreach($this->template_fields as $field) {
                    $account_template_field = AccountUploadTemplateField::where('account_upload_template_id', $this->accountTemplate->id)
                        ->where('upload_template_field_id', $field->id)
                        ->where('number', $field->number)
                        ->first();
                    if(!empty($account_template_field)) {
                        $account_template_field->update([
                            'file_column_name' => $this->account_template_fields[$field->id]['name'],
                        ]);
                    } else {
                        $account_template_field = new AccountUploadTemplateField([
                            'account_upload_template_id' => $this->accountTemplate->id,
                            'upload_template_field_id' => $field->id,
                            'number' => $field->number,
                            'file_column_name' => $this->account_template_fields[$field->id]['name'],
                        ]);
                        $account_template_field->save();
                    }
                }

                $this->success_msg = 'Template updated successfully!';
            }
        }
    }

    public function mount($template) {
        $this->accountTemplate = $template;
        $this->account = $template->account;
        $this->template_id = $template->upload_template_id;

        $this->template = $template->upload_template;
        $this->template_fields = $this->template->fields()->orderBy('number', 'ASC')->get();

        foreach($this->template_fields as $field) {
            $templateField = AccountUploadTemplateField::where('account_upload_template_id', $template->id)
                ->where('upload_template_field_id', $field->id)
                ->first();

            $this->account_template_fields[$field->id]['name'] = $templateField->file_column_name;
        }

    }

    public function render()
    {
        return view('livewire.account.account-template-edit');
    }
}
