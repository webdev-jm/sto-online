<?php

namespace App\Http\Traits;

use App\Models\AccountUploadTemplate;

trait UploadMappingTrait
{
    /**
     * Returns the custom column mapping for a given account and upload type.
     *
     * @return array{start_row: int|null, columns: array<string, int>}
     */
    public function getUploadColumnMapping(int $accountId, string $type): array
    {
        $template = AccountUploadTemplate::where('account_id', $accountId)
            ->where('type', $type)
            ->with(['fields.upload_template_field'])
            ->first();

        if (!$template) {
            return ['start_row' => null, 'columns' => []];
        }

        $columns = [];
        foreach ($template->fields as $field) {
            if ($field->file_column_number !== null && $field->upload_template_field) {
                $columns[$field->upload_template_field->column_name] = (int) $field->file_column_number;
            }
        }

        return [
            'start_row' => $template->start_row,
            'columns'   => $columns,
        ];
    }

    /**
     * Resolves a column index from the mapping, falling back to the default.
     */
    public function resolveUploadColumn(array $columns, string $fieldName, int $default): int
    {
        return $columns[$fieldName] ?? $default;
    }
}
