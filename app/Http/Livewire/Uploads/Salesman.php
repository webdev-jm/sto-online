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
    public $account_branch;
    public $err_msg;

    public $perPage = 10;

    public function uploadData() {
        foreach($this->salesman_data as $data) {
            // check
            if($data['check'] == 0) {
                $salesman = new Sale([
                    'account_id' => $this->account->id,
                    'account_branch_id' => $this->account_branch->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                ]);
                $salesman->save();
                
                if(!empty($data['area'])) {
                    // assign area
                    $area = Area::where('account_id', $this->account->id)
                        ->where('account_branch_id', $this->account_branch->id)
                        ->where(function($query) use($data) {
                            $query->where('code', $data['area'])
                                ->orWhere('name', $data['area']);
                        })
                        ->first();
                    if(empty($area)) {
                        $area = new Area([
                            'account_id' => $this->account->id,
                            'account_branch_id' => $this->account_branch->id,
                            'code' => $data['area'],
                            'name' => $data['area']
                        ]);
                        $area->save();
                    }
        
                    $salesman->areas()->syncWithoutDetaching($area->id);
                }
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

        $path1 = $this->file->store('salesman-uploads');
        $path = storage_path('app').'/'.$path1;
        $data = Excel::toArray([], $path)[0];
        $header = $data[0];
        
        $this->reset('salesman_data');
        if($this->checkHeader($header) == 0) {
            $current_salesmen = Sale::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('code');

            foreach($data as $key => $row) {
                if($key > 0) {
                    $check = $current_salesmen->get($row[0]);

                    $this->salesman_data[] = [
                        'check' => empty($check) ? 0 : 1,
                        'code' => $row[0],
                        'name' => $row[1],
                        'area' => $row[2],
                    ];
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
        $this->account_branch = Session::get('account_branch');
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
