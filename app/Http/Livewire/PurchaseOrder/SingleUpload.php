<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithFileUploads;

use Spatie\SimpleExcel\SimpleExcelReader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use App\Http\Traits\MergedCellReaderHelper;

use Illuminate\Support\Facades\Session;

class SingleUpload extends Component
{
    use WithFileUploads;
    use MergedCellReaderHelper;

    public $account_branch;
    public $file;
    public $po_data;

    public function checkUploads() {
        $this->validate([
            'file' => [
                'required'
            ]
        ]);

        

        $path1 = $this->file->storeAs('purchase-order-upload/account_branch_'.$this->account_branch->id, $this->file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;

        // Get the file extension
        $extension = $this->file->getClientOriginalExtension();
        if(in_array($extension, ['xlsx', 'csv', 'bin', 'xls'])) {
            // convert xls to xlsx
            $spreadsheet = IOFactory::load($path);
            // Ensure merged cell values are accessible
            $worksheet = $spreadsheet->getActiveSheet();

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $xlsxPath = storage_path('app').'/purchase-order-uploads/account_branch_'.$this->account_branch->id.'/converted-file-'.time().'.xlsx';
            $writer->save($xlsxPath);

            // Read rows with merged cells handling
            $rows = $this->readMergedCells($worksheet);

            $header_arr = [
                'PO NUMBER',
                'SHIP DATE',
                'SHIPPING INSTRUCTION',
                'SHIP TO NAME',
                'SHIP TO ADDRESS',
            ];

            $header_data = [];
            $header_row = [];

            $data = [];
            foreach($rows as $key => $row) {
                $current_header = strtoupper(trim($row[0]));

                // iterate through header array
                foreach($header_arr as $header) {
                    if($current_header === strtoupper($header)) {
                        $header_data[$current_header] = $row[1] ?? NULL;
                        // break loop if match is found
                        break;
                    }
                }

                if($current_header == 'BEVI SKU CODE') {
                    $header_row = $row;
                    $header_key = $key;
                }

                if(!empty($header_key) && $key > $header_key && !empty($row[0])) {
                    $data[] = $row;
                }
            }

            $details = [];
            foreach($data as $key => $val) {
                foreach($header_row as $key1 => $col) {
                    $details[$key][$col] = $val[$key1];
                }
            }
            
            $this->po_data = [
                'header' => $header_data,
                'details' => $details
            ];
        }
    }

    public function uploadData() {
        if(!empty($this->po_data)) {
            Session::put('po_data', $this->po_data);

            // reload page
            return redirect(request()->header('Referer'));
        }
    }
    
    public function mount($account_branch) {
        $this->account_branch = $account_branch;
    }

    public function render()
    {
        return view('livewire.purchase-order.single-upload');
    }
}
