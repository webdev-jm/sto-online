<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;

use App\Models\Area as AreaModel;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\UploadMappingTrait;

class Area extends Component
{
    use WithPagination;
    use WithFileUploads;
    use UploadMappingTrait;
    protected $paginationTheme = 'bootstrap';

    public $file;
    public $area_data;
    public $account;
    public $account_branch;
    public $err_msg;

    public array $uploadColumns = [];
    public ?int $uploadStartRow = null;

    public string $mode = 'card';
    public string $redirectRoute = 'area.index';
    public string $activeTab = '';

    public $perPage = 10;
    public $upload_triggered = false;
    public $page;

    public function uploadData(): mixed
    {
        if ($this->upload_triggered) {
            return null;
        }

        foreach ($this->area_data as $data) {
            if ($data['check'] == 0) {
                $area = new AreaModel([
                    'account_id'        => $this->account->id,
                    'account_branch_id' => $this->account_branch->id,
                    'code'              => $data['code'],
                    'name'              => $data['name'],
                ]);
                $area->save();
            }
        }

        $this->upload_triggered = true;

        activity('upload')
            ->log(':causer.name has uploaded area data on [' . $this->account->short_name . ']');

        $params = $this->activeTab ? ['tab' => $this->activeTab] : [];

        return redirect()->route($this->redirectRoute, $params)->with([
            'message_success' => 'Area data has been uploaded.',
        ]);
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel',
        ]);

        $path1 = $this->file->storeAs('area-uploads', time() . '_' . $this->file->getClientOriginalName());
        $path  = storage_path('app') . '/' . $path1;
        $data  = Excel::toArray([], $path)[0];

        $hasMappedColumns = !empty($this->uploadColumns);
        $startRow         = $this->uploadStartRow ?? 2;
        $headerRow        = $startRow - 1;
        $cols             = $this->uploadColumns;

        $codeIdx = $this->resolveUploadColumn($cols, 'code', 0);
        $nameIdx = $this->resolveUploadColumn($cols, 'name', 1);

        $header = $data[$headerRow] ?? [];

        $this->reset('area_data', 'err_msg');

        if ($hasMappedColumns || $this->checkHeader($header) === 0) {
            $current_areas = AreaModel::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('code');

            foreach ($data as $key => $row) {
                if ($key >= $startRow) {
                    $code = $row[$codeIdx] ?? null;
                    $name = $row[$nameIdx] ?? null;

                    if (empty($code)) {
                        continue;
                    }

                    $exists = $current_areas->get($code);

                    $this->area_data[] = [
                        'check' => $exists ? 1 : 0,
                        'code'  => $code,
                        'name'  => $name,
                    ];
                }
            }
        } else {
            $this->err_msg = 'Invalid format. Please provide an Excel file with the correct format (CODE, NAME).';
        }
    }

    private function checkHeader(array $header): int
    {
        $requiredHeaders = ['code', 'name'];

        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            $cell = trim(strtolower($header[$index] ?? ''));
            if ($cell !== strtolower($requiredHeader)) {
                $err++;
            }
        }

        return $err;
    }

    private function paginateArray(array $data, int $perPage): LengthAwarePaginator
    {
        $currentPage          = $this->page ?: 1;
        $items                = collect($data);
        $offset               = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage  = $items->slice($offset, $perPage);

        return new LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'onEachSide' => 1]
        );
    }

    public function gotoPage(int $page, mixed $el): void
    {
        $this->page = $page;
    }

    public function previousPage(mixed $el): void
    {
        $this->page--;
    }

    public function nextPage(mixed $el): void
    {
        $this->page++;
    }

    public function mount(): void
    {
        $this->account        = Session::get('account');
        $this->account_branch = Session::get('account_branch');

        $mapping              = $this->getUploadColumnMapping($this->account->id, 'area');
        $this->uploadColumns  = $mapping['columns'];
        $this->uploadStartRow = $mapping['start_row'];
    }

    public function render(): \Illuminate\View\View
    {
        $paginatedData = null;
        if (!empty($this->area_data)) {
            $paginatedData = $this->paginateArray($this->area_data, $this->perPage);
        }

        return view('livewire.uploads.area')->with([
            'paginatedData' => $paginatedData,
            'mode'          => $this->mode,
        ]);
    }
}
