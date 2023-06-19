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

class Location extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $file;
    public $location_data;
    public $account;

    public $perPage = 10;

    public function uploadData() {
        foreach($this->location_data as $data) {
            // check
            $check = Loc::where('account_id', $this->account->id)
                ->where('code', $data['code'])
                ->where('name', $data['name'])
                ->first();
            if(empty($check)) {
                $location = new Loc([
                    'account_id' => $this->account->id,
                    'code' => $data['code'],
                    'name' => $data['name'],
                ]);
                $location->save();
            }
        }

        // logs
        activity('upload')
        ->log(':causer.name has uploaded location data on ['.$this->account->short_name.']');

        return redirect()->route('location.index')->with([
            'message_success' => 'Location data has been uploaded.'
        ]);
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path = $this->file->getRealPath();
        $data = Excel::toArray([], $path)[0];
        $header = $data[1];
        
        $this->reset('location_data');
        if($this->checkHeader($header) == 0) {
            foreach($data as $key => $row) {
                if($key > 1) {
                    $this->location_data[] = [
                        'code' => $row[0],
                        'name' => $row[1]
                    ];
                }
            }
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

    public function mount() {
        $this->account = Session::get('account');
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->location_data)) {
            $paginatedData = $this->paginateArray($this->location_data, $this->perPage);
        }

        return view('livewire.uploads.location')->with([
            'paginatedData' => $paginatedData
        ]);
    }
}
