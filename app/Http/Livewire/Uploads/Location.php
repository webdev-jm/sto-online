<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use App\Models\Location as Loc;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\UploadMappingTrait;

class Location extends Component
{
    use WithPagination;
    use WithFileUploads;
    use UploadMappingTrait;
    protected $paginationTheme = 'bootstrap';

    public $file;
    public $location_data;
    public $account;
    public $account_branch;
    public $err_msg;

    public array $uploadColumns = [];
    public ?int $uploadStartRow = null;

    public $perPage = 10;

    public string $mode = 'modal';
    public string $redirectRoute = 'location.index';
    public string $activeTab = '';

    public $upload_triggered = false;
    public $page;

    public function uploadData() {
        // avoid duplicate uploads
        if ($this->upload_triggered) {
            return;
        }

        foreach($this->location_data as $data) {
            if($data['check'] == 0) {
                $location = new Loc([
                    'account_id' => $this->account->id,
                    'account_branch_id' => $this->account_branch->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                ]);
                $location->save();
            }
        }

        $this->upload_triggered = true;

        // logs
        activity('upload')
        ->log(':causer.name has uploaded location data on ['.$this->account->short_name.']');

        $params = $this->activeTab ? ['tab' => $this->activeTab] : [];

        return redirect()->route($this->redirectRoute, $params)->with([
            'message_success' => 'Location data has been uploaded.'
        ]);
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->file->store('location-uploads');
        $path = storage_path('app').'/'.$path1;
        $data = Excel::toArray([], $path)[0];

        $hasMappedColumns = !empty($this->uploadColumns);
        $startRow = $this->uploadStartRow ?? 2;
        $headerRow = $startRow - 1;
        $cols = $this->uploadColumns;

        $codeIdx = $this->resolveUploadColumn($cols, 'code', 0);
        $nameIdx = $this->resolveUploadColumn($cols, 'name', 1);

        $header = $data[$headerRow] ?? [];

        $this->reset('location_data');
        if ($hasMappedColumns || $this->checkHeader($header) === 0) {
            foreach ($data as $key => $row) {
                if ($key >= $startRow) {
                    $code = $row[$codeIdx] ?? null;
                    $name = $row[$nameIdx] ?? null;

                    if (empty($code)) {
                        continue;
                    }

                    $location = Loc::where('account_id', $this->account->id)
                        ->where('account_branch_id', $this->account_branch->id)
                        ->where('code', $code)
                        ->where('name', $name)
                        ->first();

                    $this->location_data[] = [
                        'check' => $location ? 1 : 0,
                        'code'  => $code,
                        'name'  => $name,
                    ];
                }
            }
        } else {
            $this->err_msg = 'Invalid format. Please provide an excel with the correct format.';
        }
    }

    private function paginateArray($data, $perPage)
    {
        $currentPage = $this->page ?: 1;
        $items = collect($data);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $items->slice($offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'onEachSide' => 1]
        );

        return $paginator;
    }

    public function gotoPage($page, $el) {
        $this->page = $page;
    }

    public function previousPage($el) {
        $this->page--;
    }

    public function nextPage($el) {
        $this->page++;
    }

    private function checkHeader($header) {
        $requiredHeaders = [
            'code',
            'name',
        ];

        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (trim(strtolower($header[$index])) !== strtolower($requiredHeader)) {
                $err++;
            }
        }

        return $err;
    }

    public function mount(): void
    {
        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');

        $mapping = $this->getUploadColumnMapping($this->account->id, 'location');
        $this->uploadColumns = $mapping['columns'];
        $this->uploadStartRow = $mapping['start_row'];
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->location_data)) {
            $paginatedData = $this->paginateArray($this->location_data, $this->perPage);
        }

        return view('livewire.uploads.location')->with([
            'paginatedData' => $paginatedData,
            'mode'          => $this->mode,
        ]);
    }
}
