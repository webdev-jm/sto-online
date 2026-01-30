<?php

namespace App\Http\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use App\Models\Account as AccountModel; // Assuming this is your Account model
use App\Models\Customer as Cust;
use App\Models\Salesman;
use App\Models\Channel;
use App\Models\SalesmanCustomer;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;

use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;

use Maatwebsite\Excel\Facades\Excel;

use App\Jobs\CustomerImportJob;
use Illuminate\Support\Str;

use App\Http\Traits\ChannelMappingTrait;

class Customer extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    use ChannelMappingTrait;

    public ?array $customer_data = [];
    public $file;
    public AccountModel $account;
    public $account_branch; // Type hint if AccountBranch model exists, e.g., AccountBranch $account_branch
    public $err_msg;

    public $perPage = 10;

    public $upload_triggered = false;

    public function uploadData() {
        // avoid duplicate uploads
        if ($this->upload_triggered) {
            return;
        }

        $totalValidCustomers = collect($this->customer_data)->where('check', 0)->count();

        // Determine tenant connection name
        $tenantConnection = $this->account->db_data->connection_name ?? config('database.default');

        $upload_data = [
            'total' => $totalValidCustomers,
            'start' => Cust::on($tenantConnection)
                            ->where('account_id', $this->account->id)
                            ->where('account_branch_id', $this->account_branch->id) // Assuming account_branch is an object with id
                            ->count(),
        ];

        CustomerImportJob::dispatch($this->customer_data, $this->account->id, $this->account_branch->id); // Ensure account_branch->id is correct

        $this->upload_triggered = true;
        // logs
        activity('upload')
        ->log(':causer.name has uploaded customer data on ['.$this->account->short_name.']');

        return redirect()->route('customer.index')->with([
            'message_success' => 'Customer data has been added to queue to process.',
            'upload_data' => $upload_data
        ]);
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        // Generate a unique filename
        $originalName = $this->file->getClientOriginalName();
        $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $this->file->getClientOriginalExtension();
        $path1 = $this->file->storeAs('customer-uploads', $filename);
        $path = storage_path('app').'/'.$path1;

        $this->reset([
            'customer_data',
            'err_msg'
        ]);

        $excelSheets = Excel::toArray([], $path);

        if (empty($excelSheets) || empty($excelSheets[0])) {
            $this->err_msg = 'The Excel file is empty or the first sheet is missing.';
            return;
        }

        $firstSheetData = $excelSheets[0];
        if (count($firstSheetData) < 2) { // Needs at least a header and one data row
            $this->err_msg = 'The Excel file does not contain enough data (requires a header and at least one data row).';
            return;
        }

        $headerRow = $firstSheetData[1];
        if ($this->checkHeader($headerRow) !== 0) {
            $this->err_msg = 'Invalid Excel header format. Please ensure all required columns are present in the correct order: Code, Name, Address, Salesman Code, Channel Code, Channel Name, Province, City/Town, Barangay, Street, Postal Code.';
            return;
        }

        $dataRows = array_slice($firstSheetData, 2);

        if (empty($dataRows)) {
            $this->err_msg = 'The Excel file contains a header but no data rows.';
            return;
        }

        // Determine tenant connection name
        $tenantConnection = $this->account->db_data->connection_name ?? config('database.default');

            // Pre-fetch data for efficiency
            $current_customers = Cust::on($tenantConnection)
                ->where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get()
                ->keyBy('code');

            $customer_ubos = CustomerUbo::on($tenantConnection)
                ->where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->get();

            $channels = Channel::get();

            // Pre-fetch address entities
            $provinceNames = collect($dataRows)->pluck(6)->map(fn($name) => trim($name ?? ''))->unique()->filter()->all();
            $cityNames = collect($dataRows)->pluck(7)->map(fn($name) => trim($name ?? ''))->unique()->filter()->all();
            $brgyNames = collect($dataRows)->pluck(8)->map(fn($name) => trim($name ?? ''))->unique()->filter()->all();

            $dbProvinces = Province::whereIn('province_name', $provinceNames)->get()->keyBy('province_name');
            $dbMunicipalities = Municipality::whereIn('municipality_name', $cityNames)->get()->keyBy('municipality_name');
            $dbBarangays = Barangay::whereIn('barangay_name', $brgyNames)->get()->keyBy('barangay_name');

        $this->customer_data = collect($dataRows)->map(function ($row) use (
            $current_customers,
            $customer_ubos,
            $channels,
            $dbProvinces,
            $dbMunicipalities,
            $dbBarangays
        ) {
                $code = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');
                $address = trim($row[2] ?? '');
                $salesmanCode = trim($row[3] ?? '');
                $channel_code = trim($row[4] ?? '');
                // $channel_name = trim($row[5] ?? ''); // Not directly used if looking up by code
                $provinceName = trim($row[6] ?? '');
                $cityName = trim($row[7] ?? '');
                $brgyName = trim($row[8] ?? '');
                $street = trim($row[9] ?? '');
                $postalCode = trim($row[10] ?? '');

                // Map channel code using ChannelMappingTrait
                [$channel_code, $channel_name_mapped] = $this->channelMapping($this->account->id, $channel_code);

                $customerData = [
                    'code' => $code,
                    'name' => $name,
                    'address' => $address,
                    'salesman' => $salesmanCode, // This is the salesman code
                    'channel' => [],
                    'street' => $street,
                    'brgy' => $brgyName,
                    'city' => $cityName,
                    'province' => $provinceName,
                    'country' => '', // Default or from config
                    'postal_code' => $postalCode,
                    'status' => 0, // Default: No UBO similarity / New UBO
                    'similar' => [], // To store details of similar UBO for display
                    'check' => 3, // Default: Error - Incomplete/Invalid
                    'brgy_id' => null,
                    'city_id' => null,
                    'province_id' => null,
                ];

                // Basic validation for required fields from Excel
                if (empty($code) || empty($name) || empty($channel_code) || empty($provinceName) || empty($cityName) || empty($brgyName)) {
                    return $customerData; // 'check' remains 3 (incomplete)
                }

                $channel = $channels->where('code', $channel_code)->first();
                if (!$channel) {
                    $customerData['check'] = 2; // Error: Invalid channel
                    return $customerData;
                }
                $customerData['channel'] = $channel->toArray(); // Pass channel data to job

                // Assign address IDs from pre-fetched data
                $customerData['province_id'] = $dbProvinces->get($provinceName)->id ?? null;
                $customerData['city_id'] = $dbMunicipalities->get($cityName)->id ?? null;
                $customerData['brgy_id'] = $dbBarangays->get($brgyName)->id ?? null;

                // Check if customer already exists by code
                $existingCustomer = $current_customers->get($code);
                $customerData['check'] = $existingCustomer ? 1 : 0; // 1 if exists, 0 if new and valid so far

                // UBO similarity check (only if it's a new customer, check = 0)
                if ($customerData['check'] === 0) {
                    $similarUbo = $customer_ubos->first(function ($item) use ($name, $address) {
                        return $this->checkSimilarity($item->name, $name) >= 90
                            && $this->checkSimilarity($item->address, $address) >= 90;
                    });

                    if ($similarUbo) {
                        $customerData['similar'] = $similarUbo->toArray();
                        $customerData['status'] = 1; // Indicates UBO similarity found, will be linked by job
                    }
                }
                return $customerData;
            })->toArray();
    }

    private function paginateArray(array $data, int $perPage): LengthAwarePaginator
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
            'postal code',
        ];

        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (empty($header[$index]) || trim(strtolower($header[$index])) !== strtolower($requiredHeader)) {
                $err++;
            }
        }

        return $err;
    }

    public function differentCustomer(string $customer_key): void
    {
        $customer_key = decrypt($customer_key);
        if (isset($this->customer_data[$customer_key])) {
            $this->customer_data[$customer_key]['status'] = 0; // Mark as not similar to UBO / process as new UBO
            $this->customer_data[$customer_key]['check'] = 0;  // Mark as valid for import
        }
    }

    public function sameCustomer(string $customer_key): void
    {
        $customer_key = decrypt($customer_key);
        if (isset($this->customer_data[$customer_key])) {
            $this->customer_data[$customer_key]['status'] = 1; // Mark as similar to UBO, will be linked by job
            $this->customer_data[$customer_key]['check'] = 0;  // Mark as valid for import
        }
    }

    private function checkSimilarity(?string $str1, ?string $str2): float
    {
        $s1 = Str::upper(str_replace(' ', '', $str1 ?? ''));
        $s2 = Str::upper(str_replace(' ', '', $str2 ?? ''));

        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0 && $len2 === 0) return 100.0; // Both empty, consider them same
        if ($len1 === 0 || $len2 === 0) return 0.0;   // One is empty, not similar

        $maxLength = max($len1, $len2);
        $distance = levenshtein($s1, $s2);

        return (1 - ($distance / $maxLength)) * 100.0;
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
