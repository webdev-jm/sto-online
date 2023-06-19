<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use App\Models\Salesman as Sale;
use App\Models\Area;

use Maatwebsite\Excel\Facades\Excel;

class Salesman extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $salesman_data;
    public $file;
    public $account;

    public $perPage = 10;

    public function uploadData() {
        foreach($this->salesman_data as $data) {
            // check
            $salesman = Sale::where('account_id', $this->account->id)
                ->where('code', $data['code'])
                ->where('name', $data['name'])
                ->first();
            if(empty($salesman)) {
                $salesman = new Sale([
                    'account_id' => $this->account->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                ]);
                $salesman->save();
            }

            if(!empty($data['area'])) {
                // assign area
                $area = Area::where('account_id', $this->account->id)
                    ->where(function($query) use($data) {
                        $query->where('code', $data['area'])
                            ->orWhere('name', $data['area']);
                    })
                    ->first();
                if(empty($area)) {
                    $area = new Area([
                        'account_id' => $this->account->id,
                        'code' => $data['area'],
                        'name' => $data['area']
                    ]);
                    $area->save();
                }
    
                $salesman->areas()->syncWithoutDetaching($area->id);
            }
        }

        // logs
        activity('upload')
        ->log(':causer.name has uploaded salesman data on ['.$this->account->short_name.']');

        return redirect()->route('salesman.index')->with([
            'message_success' => 'Salesman data has been uploaded.'
        ]);
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path = $this->file->getRealPath();
        $data = Excel::toArray([], $path)[0];
        $header = $data[0];
        
        $this->reset('salesman_data');
        if($this->checkHeader($header) == 0) {
            foreach($data as $key => $row) {
                if($key > 0) {
                    $this->salesman_data[] = [
                        'code' => $row[0],
                        'name' => $row[1],
                        'area' => $row[2],
                    ];
                }
            }
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

    private function checkHeader($header) {
        $requiredHeaders = [
            'code',
            'name',
            'area',
        ];
    
        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (trim(strtolower($header[$index])) !== strtolower($requiredHeader)) {
                $err++;
            }
        }
    
        return $err;
    }

    public function mount() {
        $this->account = Session::get('account');
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->salesman_data)) {
            $paginatedData = $this->paginateArray($this->salesman_data, $this->perPage);
        }

        return view('livewire.uploads.salesman')->with([
            'paginatedData' => $paginatedData
        ]);
    }
}
