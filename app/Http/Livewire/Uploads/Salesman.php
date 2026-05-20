<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use App\Models\Salesman as Sale;
use App\Models\District;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\UploadMappingTrait;

class Salesman extends Component
{
    use WithPagination;
    use WithFileUploads;
    use UploadMappingTrait;
    protected $paginationTheme = 'bootstrap';

    public $salesman_data;
    public $file;
    public $account;
    public $account_branch;
    public $err_msg;

    public array $uploadColumns = [];
    public ?int $uploadStartRow = null;

    public $perPage = 10;

    public string $mode = 'modal';
    public string $redirectRoute = 'salesman.index';
    public string $activeTab = '';

    public $upload_triggered = false;
    public $page;

    public function uploadData() {
        if($this->upload_triggered) {
            return;
        }

        foreach($this->salesman_data as $data) {
            // check
            if($data['check'] == 0) {
                $salesman = new Sale([
                    'account_id' => $this->account->id,
                    'account_branch_id' => $this->account_branch->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                    'type' => $data['type'],
                ]);
                $salesman->save();

                if(!empty($data['district_code'])) {
                    $district = District::where('district_code', $data['district_code'])
                        ->first();
                    if(!empty($district)) {
                        $salesman->update([
                            'district_id' => $district->id
                        ]);
                    }
                }
            }
        }

        $this->upload_triggered = true;

        // logs
        activity('upload')
        ->log(':causer.name has uploaded salesman data on ['.$this->account->short_name.']');

        $params = $this->activeTab ? ['tab' => $this->activeTab] : [];

        return redirect()->route($this->redirectRoute, $params)->with([
            'message_success' => 'Salesman data has been uploaded.'
        ]);
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->file->store('salesman-uploads');
        $path = storage_path('app').'/'.$path1;
        $data = Excel::toArray([], $path)[0];

        $hasMappedColumns = !empty($this->uploadColumns);
        $startRow = $this->uploadStartRow ?? 1;
        $headerRow = $startRow - 1;
        $cols = $this->uploadColumns;

        $codeIdx     = $this->resolveUploadColumn($cols, 'code', 0);
        $nameIdx     = $this->resolveUploadColumn($cols, 'name', 1);
        $typeIdx     = $this->resolveUploadColumn($cols, 'type', 2);
        $districtIdx = $this->resolveUploadColumn($cols, 'district_code', 3);

        $header = $data[$headerRow] ?? [];

        $this->reset('salesman_data');
        if ($hasMappedColumns || $this->checkHeader($header) === 0) {
            $current_salesmen = Sale::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('code');

            foreach ($data as $key => $row) {
                if ($key >= $startRow) {
                    $code = $row[$codeIdx] ?? null;
                    $check = $current_salesmen->get($code);

                    if (!empty($code)) {
                        $this->salesman_data[] = [
                            'check'         => empty($check) ? 0 : 1,
                            'code'          => $code,
                            'name'          => $row[$nameIdx] ?? '',
                            'type'          => $row[$typeIdx] ?? '',
                            'district_code' => $row[$districtIdx] ?? '',
                        ];
                    }
                }
            }
        } else {
            $this->err_msg = 'Invalid format. Please provide an excel with the correct format.';
        }
    }

    private function paginateArray($data, $perPage) {
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
            'type of salesman',
            'district code'
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

        $mapping = $this->getUploadColumnMapping($this->account->id, 'salesman');
        $this->uploadColumns = $mapping['columns'];
        $this->uploadStartRow = $mapping['start_row'];
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->salesman_data)) {
            $paginatedData = $this->paginateArray($this->salesman_data, $this->perPage);
        }

        return view('livewire.uploads.salesman')->with([
            'paginatedData' => $paginatedData,
            'mode'          => $this->mode,
        ]);
    }
}
