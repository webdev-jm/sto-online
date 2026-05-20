<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;

use App\Models\District as DistrictModel;
use App\Models\Area as AreaModel;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\UploadMappingTrait;

class District extends Component
{
    use WithPagination;
    use WithFileUploads;
    use UploadMappingTrait;
    protected $paginationTheme = 'bootstrap';

    public $file;
    public $district_data;
    public $account;
    public $account_branch;
    public $err_msg;

    public array $uploadColumns = [];
    public ?int $uploadStartRow = null;

    public string $mode = 'card';
    public string $redirectRoute = 'district.index';
    public string $activeTab = '';

    public $perPage = 10;
    public $upload_triggered = false;
    public $page;

    public function uploadData(): mixed
    {
        if ($this->upload_triggered) {
            return null;
        }

        foreach ($this->district_data as $data) {
            if ($data['check'] == 0) {
                $district = new DistrictModel([
                    'account_branch_id' => $this->account_branch->id,
                    'district_code'     => $data['district_code'],
                ]);
                $district->save();

                if (!empty($data['area_ids'])) {
                    $district->areas()->sync($data['area_ids']);
                }
            }
        }

        $this->upload_triggered = true;

        activity('upload')
            ->log(':causer.name has uploaded district data on [' . $this->account->short_name . ']');

        $params = $this->activeTab ? ['tab' => $this->activeTab] : [];

        return redirect()->route($this->redirectRoute, $params)->with([
            'message_success' => 'District data has been uploaded.',
        ]);
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel',
        ]);

        $path1 = $this->file->storeAs('district-uploads', time() . '_' . $this->file->getClientOriginalName());
        $path  = storage_path('app') . '/' . $path1;
        $data  = Excel::toArray([], $path)[0];

        $hasMappedColumns   = !empty($this->uploadColumns);
        $startRow           = $this->uploadStartRow ?? 2;
        $headerRow          = $startRow - 1;
        $cols               = $this->uploadColumns;

        $districtCodeIdx = $this->resolveUploadColumn($cols, 'district_code', 0);
        $areaCodesIdx    = $this->resolveUploadColumn($cols, 'area_codes', 1);

        $header = $data[$headerRow] ?? [];

        $this->reset('district_data', 'err_msg');

        if ($hasMappedColumns || $this->checkHeader($header) === 0) {
            // Pre-fetch all areas for the account to validate area codes
            $accountAreas = AreaModel::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('code');

            $current_districts = DistrictModel::where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('district_code');

            foreach ($data as $key => $row) {
                if ($key >= $startRow) {
                    $districtCode = trim($row[$districtCodeIdx] ?? '');
                    $areaCodesRaw = trim($row[$areaCodesIdx] ?? '');

                    if (empty($districtCode)) {
                        continue;
                    }

                    $areaIds      = [];
                    $invalidAreas = [];

                    if (!empty($areaCodesRaw)) {
                        $areaCodes = array_map('trim', explode(',', $areaCodesRaw));
                        foreach ($areaCodes as $areaCode) {
                            if (empty($areaCode)) {
                                continue;
                            }
                            $area = $accountAreas->get($areaCode);
                            if ($area) {
                                $areaIds[] = $area->id;
                            } else {
                                $invalidAreas[] = $areaCode;
                            }
                        }
                    }

                    $exists = $current_districts->get($districtCode);

                    $this->district_data[] = [
                        'check'         => $exists ? 1 : 0,
                        'district_code' => $districtCode,
                        'area_codes'    => $areaCodesRaw,
                        'area_ids'      => $areaIds,
                        'invalid_areas' => $invalidAreas,
                    ];
                }
            }
        } else {
            $this->err_msg = 'Invalid format. Please provide an Excel file with the correct format (DISTRICT_CODE, AREA_CODES).';
        }
    }

    private function checkHeader(array $header): int
    {
        $requiredHeaders = ['district_code', 'area_codes'];

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
        $currentPage         = $this->page ?: 1;
        $items               = collect($data);
        $offset              = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $items->slice($offset, $perPage);

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

        $mapping              = $this->getUploadColumnMapping($this->account->id, 'district');
        $this->uploadColumns  = $mapping['columns'];
        $this->uploadStartRow = $mapping['start_row'];
    }

    public function render(): \Illuminate\View\View
    {
        $paginatedData = null;
        if (!empty($this->district_data)) {
            $paginatedData = $this->paginateArray($this->district_data, $this->perPage);
        }

        return view('livewire.uploads.district')->with([
            'paginatedData' => $paginatedData,
            'mode'          => $this->mode,
        ]);
    }
}
