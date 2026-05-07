<?php

namespace App\Http\Livewire\UploadMapping;

use Livewire\Component;
use App\Models\Account;
use App\Models\UploadTemplate;
use App\Models\UploadTemplateField;
use App\Models\AccountUploadTemplate;
use App\Models\AccountUploadTemplateField;

class MappingEntry extends Component
{
    public Account $account;
    public string $activeTab = 'sales';

    /** @var array<string, int|null> */
    public array $startRows = [];

    /**
     * Keyed by type → templateFieldId → ['file_column_number' => ?int, 'file_column_name' => string]
     *
     * @var array<string, array<int, array{file_column_number: int|null, file_column_name: string}>>
     */
    public array $fieldMappings = [];

    /** @var array<string, UploadTemplateField[]> */
    protected array $templateFields = [];

    /** Default start rows per type when no custom mapping exists */
    protected const DEFAULT_START_ROWS = [
        'sales'     => 2,
        'inventory' => 1,
        'customer'  => 2,
        'salesman'  => 1,
        'location'  => 2,
    ];

    public function mount(): void
    {
        $types = array_keys(self::DEFAULT_START_ROWS);

        foreach ($types as $type) {
            $this->startRows[$type] = null;
            $this->fieldMappings[$type] = [];

            $template = UploadTemplate::where('title', ucfirst($type) . ' Upload')->first();
            if (!$template) {
                continue;
            }

            $fields = UploadTemplateField::where('upload_template_id', $template->id)
                ->orderBy('number')
                ->get();

            $accountTemplate = AccountUploadTemplate::where('account_id', $this->account->id)
                ->where('type', $type)
                ->with('fields')
                ->first();

            $this->startRows[$type] = $accountTemplate?->start_row;

            foreach ($fields as $field) {
                $existing = $accountTemplate?->fields->firstWhere('upload_template_field_id', $field->id);

                $this->fieldMappings[$type][$field->id] = [
                    'file_column_number' => $existing?->file_column_number,
                    'file_column_name'   => $existing?->file_column_name ?? '',
                ];
            }
        }
    }

    public function updatedStartRows(mixed $value, string $key): void
    {
        $type = $key;

        $template = UploadTemplate::where('title', ucfirst($type) . ' Upload')->first();
        if (!$template) {
            return;
        }

        AccountUploadTemplate::updateOrCreate(
            ['account_id' => $this->account->id, 'type' => $type],
            ['upload_template_id' => $template->id, 'start_row' => (int) $value]
        );
    }

    public function updatedFieldMappings(mixed $value, string $key): void
    {
        // key format: "sales.123.file_column_number"
        $parts = explode('.', $key);
        if (count($parts) !== 3) {
            return;
        }

        [$type, $templateFieldId, $column] = $parts;
        $this->saveField($type, (int) $templateFieldId);
    }

    public function saveField(string $type, int $templateFieldId): void
    {
        $template = UploadTemplate::where('title', ucfirst($type) . ' Upload')->first();
        if (!$template) {
            return;
        }

        $accountTemplate = AccountUploadTemplate::firstOrCreate(
            ['account_id' => $this->account->id, 'type' => $type],
            ['upload_template_id' => $template->id, 'start_row' => self::DEFAULT_START_ROWS[$type] ?? 1]
        );

        $data = $this->fieldMappings[$type][$templateFieldId] ?? [];

        AccountUploadTemplateField::updateOrCreate(
            [
                'account_upload_template_id' => $accountTemplate->id,
                'upload_template_field_id'   => $templateFieldId,
            ],
            [
                'number'             => $templateFieldId,
                'file_column_number' => isset($data['file_column_number']) && $data['file_column_number'] !== '' ? (int) $data['file_column_number'] : null,
                'file_column_name'   => $data['file_column_name'] ?? '',
            ]
        );
    }

    public function render()
    {
        $types = array_keys(self::DEFAULT_START_ROWS);
        $templateFieldsByType = [];

        foreach ($types as $type) {
            $template = UploadTemplate::where('title', ucfirst($type) . ' Upload')->first();
            $templateFieldsByType[$type] = $template
                ? UploadTemplateField::where('upload_template_id', $template->id)->orderBy('number')->get()
                : collect();
        }

        return view('livewire.upload-mapping.mapping-entry')->with([
            'types'               => $types,
            'templateFieldsByType' => $templateFieldsByType,
            'defaultStartRows'    => self::DEFAULT_START_ROWS,
        ]);
    }
}
