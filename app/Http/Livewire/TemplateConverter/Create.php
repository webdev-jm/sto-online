<?php

namespace App\Http\Livewire\TemplateConverter;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\AccountProductReference;
use App\Models\SMSProduct;
use App\Models\Customer;

class Create extends Component
{
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $account_branch;
    public $file_upload;
    public $file_content;
    public $data = [];

    public function updatedFileUpload()
    {
        // Validate the uploaded file
        $this->validate([
            'file_upload' => 'required|mimes:csv,txt'
        ]);

        // Process the uploaded file and get its content
        $this->file_content = $this->getFileContent();

        // Get the lines from the file content
        $lines = $this->getLinesFromFile($this->file_content);

        // Extract the year and month from the first line
        $first_line = isset($lines[0]) ? trim($lines[0]) : null;
        $year = $first_line ? substr($first_line, 5, 4) : null;
        $month = $first_line ? substr($first_line, 9, 2) : null;

        // Preload all references and products to avoid multiple database queries
        $references = $this->getAccountProductReferences();
        $customers = Customer::where('account_branch_id', $this->account_branch->id)
            ->get()
            ->keyBy('code');

        // Process the data
        $clean_data = array_filter(array_map(function($line) use ($year, $month, $references, $customers) {
            $val = $this->separateData(trim($line), $references, $customers);
            
            // Only process if type is 2
            if ($val['type'] == 2) {
                $val['date'] = $year.'-'.$month.'-30';
                return $val; // Return the valid result
            }
            
            return null; // Return null for entries that should not be included
        }, array_slice($lines, 1)), function($val) {
            // Filter out null values returned from array_map
            return !is_null($val);
        });

        // Set the processed data to the component
        $this->data = $clean_data;
    }

    // Method to process the file content
    private function getFileContent()
    {
        // Store the file temporarily
        $filePath = $this->file_upload->storeAs('uploads', $this->file_upload->getClientOriginalName());

        // Get the full path to the file
        $fullPath = storage_path('app/' . $filePath);

        // Get the content of the file
        return file_get_contents($fullPath);
    }

    // Method to split the file content into lines
    private function getLinesFromFile($fileContent)
    {
        return explode("\n", $fileContent);
    }

    // Method to preload account product references to avoid multiple DB queries
    private function getAccountProductReferences()
    {
        return AccountProductReference::with('product')
            ->where('account_id', $this->account_branch->account->sms_account_id)
            ->get()
            ->keyBy('account_reference');
    }

    // Method to separate data from each line
    private function separateData($data, $references, $customers)
    {
        // Extract parts using substr
        $type = substr($data, 0, 1);      // First 1 digit
        $branch = substr($data, 1, 4);    // Next 4 digits
        $sku = substr($data, 5, 6);       // Next 6 digits
        $qty = substr($data, 11, 5);      // Last 5 digits

        // Get the reference and product details from preloaded data
        $reference = $references->get($sku);
        $product = $reference ? $reference->product : null;
        $customer = $customers->get((int)$branch);

        return [
            'type' => $type,
            'branch' => $branch,
            'customer' => $customer->name ?? '',
            'sku' => $sku,
            'stock_code' => $product->stock_code ?? null,
            'description' => $product->description ?? null,
            'qty' => $qty,
        ];
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;
    }

    public function render()
    {
        return view('livewire.template-converter.create');
    }
}
