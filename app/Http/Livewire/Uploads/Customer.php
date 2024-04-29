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
use App\Models\Channel;
use App\Models\SalesmanCustomer;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;

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

    public $upload_triggered = false;

    public function uploadData() {
        // avoid duplicate uploads
        if ($this->upload_triggered) {
            return;
        }

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

        $this->upload_triggered = true;

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

            $customer_ubos = CustomerUbo::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get();
            
            $channels = Channel::get();
            
            $this->customer_data = $data->skip(1)->map(function ($row) use($current_customers, $customer_ubos, $channels) {
                $code = trim($row[0]);
                $name = trim($row[1]);
                $address = trim($row[2]);
                $salesman = trim($row[3]);
                $channel_code = trim($row[4] ?? '');
                $channel_name = trim($row[5] ?? '');
                $province = trim($row[6] ?? '');
                $city = trim($row[7] ?? '');
                $brgy = trim($row[8] ?? '');
                $street = trim($row[9] ?? '');

                $customer = $current_customers->get($code);
                $channel = $channels->where('code', $channel_code)
                    ->first();

                $check = $customer_ubos->filter(function ($item) use ($name, $address) {
                    return $this->checkSimilarity($item->name, $name) >= 90
                        && $this->checkSimilarity($item->address, $address) >= 90;
                })->first();

                if(!empty($code) && !empty($name) && !empty($channel_code) && !empty($province) && !empty($city) && !empty($brgy)) {

                    if(empty($channel)) {
                        return [
                            'similar' => !empty($check) ? $check->toArray() : [],
                            'check' => 2,
                            'code' => $code,
                            'name' => $name,
                            'address' => $address,
                            'salesman' => $salesman,
                            'channel' => $channel ?? [],
                            'street' => $street,
                            'brgy' => $brgy,
                            'city' => $city,
                            'province' => $province,
                            'country' => '',
                            'status' => !empty($check) ? 1 : 0
                        ];
                    } else {
                        return [
                            'similar' => !empty($check) ? $check->toArray() : [],
                            'check' => empty($customer) ? 0 : 1,
                            'code' => $code,
                            'name' => $name,
                            'address' => $address,
                            'salesman' => $salesman,
                            'channel' => $channel ?? [],
                            'street' => $street,
                            'brgy' => $brgy,
                            'city' => $city,
                            'province' => $province,
                            'country' => '',
                            'status' => !empty($check) ? 1 : 0
                        ];
                    }

                } else {
                    return [
                        'similar' => [],
                        'check' => 3,
                        'code' => $code,
                        'name' => $name,
                        'address' => $address,
                        'salesman' => $salesman,
                        'channel' => $channel ?? [],
                        'street' => $street,
                        'brgy' => $brgy,
                        'city' => $city,
                        'province' => $province,
                        'country' => '',
                        'status' => 1
                    ];
                }

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
            'channel code',
            'channel name',
            'province',
            'city/town',
            'barangay',
            'street',
        ];
    
        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (empty($header[$index]) || trim(strtolower($header[$index])) !== strtolower($requiredHeader)) {
                $err++;
            }
        }
    
        return $err;
    }

    public function differentCustomer($customer_key) {
        $customer_key = decrypt($customer_key);
        $this->customer_data[$customer_key]['status'] = 'different';
        $this->customer_data[$customer_key]['check'] = 0;
    }

    public function sameCustomer($customer_key) {
        $customer_key = decrypt($customer_key);
        $this->customer_data[$customer_key]['status'] = 'same';
        $this->customer_data[$customer_key]['check'] = 0;
    }

    private function checkSimilarity($str1, $str2) {
        $similarity = 0;
        if(strlen($str1) > 0 && strlen($str2) > 0) {
            $distance = levenshtein(strtoupper($str1), strtoupper($str2));
            $max_length = max(strlen($str1), strlen($str2));
            $similarity = 1 - ($distance / $max_length);
            $similarity = $similarity * 100;
        }

        return $similarity;
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
