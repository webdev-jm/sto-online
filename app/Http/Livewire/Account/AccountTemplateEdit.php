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

    public $start_row, $column_type;

    public function update() {
        $this->validate([
            'start_row' => [
                'required'
            ],
            'column_type' => [
                'required'
            ]
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
                $this->accountTemplate->update([
                    'start_row' => $this->start_row,
                    'type' => $this->column_type,
                ]);

                foreach($this->template_fields as $field) {
                    $account_template_field = AccountUploadTemplateField::where('account_upload_template_id', $this->accountTemplate->id)
                        ->where('upload_template_field_id', $field->id)
                        ->where('number', $field->number)
                        ->first();
                    if(!empty($account_template_field)) {
                        $account_template_field->update([
                            'file_column_name' => $this->account_template_fields[$field->id]['name'] ?? NULL,
                            'file_column_number' => $this->account_template_fields[$field->id]['number'] ?? NULL,
                        ]);
                    } else {
                        $account_template_field = new AccountUploadTemplateField([
                            'account_upload_template_id' => $this->accountTemplate->id,
                            'upload_template_field_id' => $field->id,
                            'number' => $field->number,
                            'file_column_name' => $this->account_template_fields[$field->id]['name'] ?? NULL,
                            'file_column_number' => $this->account_template_fields[$field->id]['number'] ?? NULL,
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
        $this->start_row = $this->accountTemplate->start_row;
        $this->column_type = $this->accountTemplate->type;

        foreach($this->template_fields as $field) {
            $templateField = AccountUploadTemplateField::where('account_upload_template_id', $template->id)
                ->where('upload_template_field_id', $field->id)
                ->first();

            $this->account_template_fields[$field->id]['name'] = $templateField->file_column_name ?? NULL;
            $this->account_template_fields[$field->id]['number'] = $templateField->file_column_number ?? NULL;
        }

    }

    public function render()
    {
        return view('livewire.account.account-template-edit');
    }
}
