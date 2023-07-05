<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use App\Models\Customer as Cust;
use App\Models\Salesman;
use App\Models\SalesmanCustomer;

use Maatwebsite\Excel\Facades\Excel;

use App\Jobs\CustomerImportJob;

class Customer extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $customer_data;
    public $file;
    public $account;
    public $account_branch;
    public $err_msg;

    public $perPage = 10;

    public function uploadData() {
        // foreach($this->customer_data as $data) {
        //     // get salesman
        //     $salesman = Salesman::where('account_id', $this->account->id)
        //         ->where('account_branch_id', $this->account_branch->id)
        //         ->where('code', $data['salesman'])
        //         ->first();
            
        //     // check
        //     $customer = Cust::where('account_id', $this->account->id)
        //         ->where('account_branch_id', $this->account_branch->id)
        //         ->where('code', $data['code'])
        //         ->where('name', $data['name'] ?? '-')
        //         ->first();
        //     if(empty($customer)) {
        //         $customer = new Cust([
        //             'account_id' => $this->account->id,
        //             'account_branch_id' => $this->account_branch->id,
        //             'code' => $data['code'],
        //             'name' => $data['name'] ?? '-',
        //             'address' => $data['address'] ?? '-',
        //         ]);
        //         $customer->save();
        //     }

        //     // add salesman
        //     if(!empty($salesman) && $customer->salesman_id != $salesman->id) {
        //         // update previous salesan history record
        //         $salesman_customer = SalesmanCustomer::where('salesman_id', $customer->salesman_id)
        //             ->where('customer_id', $customer->id)
        //             ->first();
                
        //         if(!empty($salesman_customer)) {
        //             $salesman_customer->update([
        //                 'end_date' => date('Y-m-d')
        //             ]);
        //         }

        //         // update salesman
        //         $customer->update([
        //             'salesman_id' => $salesman->id
        //         ]);

        //         // record new salesman history
        //         $salesman_customer = new SalesmanCustomer([
        //             'salesman_id' => $salesman->id,
        //             'customer_id' => $customer->id,
        //             'start_date' => date('Y-m-d'),
        //         ]);
        //         $salesman_customer->save();
        //     }
            
        // }

        $total = 0;
        foreach($this->customer_data as $data) {
            if($data['check'] == 0) {
                $total++;
            }
        }

        $upload_data = [
            'total' => $total,
            'start' => Cust::where('account_id', $this->account->id)->where('account_branch_id', $this->account_branch->id)->count(),
        ];

        CustomerImportJob::dispatch($this->customer_data, $this->account->id, $this->account_branch->id);

        // logs
        activity('upload')
        ->log(':causer.name has uploaded customer data on ['.$this->account->short_name.']');
        
        return redirect()->route('customer.index')->with([
            'message_success' => 'Customer data has been added to queue to process.',
            'upload_data' => $upload_data
        ]);
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->file->storeAs('customer-uploads', $this->file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;

        $data = collect(Excel::toArray([], $path))->flatten(1)->skip(1);
        $header = $data->first();
        
        $this->reset([
            'customer_data',
            'err_msg'
        ]);

        if($this->checkHeader($header) == 0) {

            $current_customers = Cust::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('code');
            
            $this->customer_data = $data->skip(1)->map(function ($row) use($current_customers) {
                $customer = $current_customers->get($row[0]);
            
                return [
                    'check' => empty($customer) ? 0 : 1,
                    'code' => $row[0],
                    'name' => $row[1],
                    'address' => $row[2],
                    'salesman' => $row[3],
                ];
            })->toArray();
        } else {
            $this->err_msg = 'Invalid format. please provide an excel with the correct format.';
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
            'address',
            'salesman code',
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
        if(!empty($this->customer_data)) {
            $paginatedData = $this->paginateArray($this->customer_data, $this->perPage);
        }

        return view('livewire.uploads.customer')->with([
            'paginatedData' => $paginatedData
        ]);
    }
}
