<?php

namespace App\Http\Livewire\ChannelMapping;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use App\Models\ChannelMapping;
use App\Models\Channel;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class MappingList extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account;
    public $mapping_arr;
    public $upload_file;

    public function render()
    {
        $channels = Channel::orderBy('code', 'asc')
            ->get();

        return view('livewire.channel-mapping.mapping-list')->with([
            'channels' => $channels,
        ]);
    }

    public function mount() {
        $mappings = ChannelMapping::orderBy('id', 'asc')
            ->where('account_id', $this->account->id)
            ->get();

        if(!empty($mappings->count())) {
            foreach($mappings as $mapping) {
                $this->mapping_arr[] = [
                    'id' => $mapping->id,
                    'channel_id' => $mapping->channel_id,
                    'external_channel_code' => $mapping->external_channel_code,
                    'external_channel_name' => $mapping->external_channel_name,
                ];
            }

        } else {
            $this->mapping_arr[] = [
                'id' => NULL,
                'channel_id' => NULL,
                'external_channel_code' => '',
                'external_channel_name' => '',
            ];
        }

    }

    public function addRow() {
        $this->mapping_arr[] = [
            'id' => NULL,
            'channel_id' => NULL,
            'external_channel_code' => '',
            'external_channel_name' => '',
        ];
    }

    public function removeRow($index) {
        if(isset($this->mapping_arr[$index]['id']) && !empty($this->mapping_arr[$index]['id'])) {
            $mapping = ChannelMapping::find($this->mapping_arr[$index]['id']);
            $mapping->forceDelete();
        }

        unset($this->mapping_arr[$index]);
        $this->mapping_arr = array_values($this->mapping_arr);
    }

    public function saveMapping($key) {
        $mapping_data = $this->mapping_arr[$key];

        if(isset($mapping_data['id']) && !empty($mapping_data['id'])) {
            $mapping = ChannelMapping::find($mapping_data['id']);
        } else {
            $mapping = new ChannelMapping();
        }

        $mapping->account_id = $this->account->id;
        $mapping->channel_id = $mapping_data['channel_id'];
        $mapping->external_channel_code = $mapping_data['external_channel_code'];
        $mapping->external_channel_name = $mapping_data['external_channel_name'];
        $mapping->save();

        $this->mapping_arr[$key]['id'] = $mapping->id;
    }

    public function updatedMappingArr($value, $key)
    {
        $key = explode('.', $key)[0];
        $this->saveMapping($key);
    }

    public function updatedUploadFile()
    {
       $this->validate([
            'upload_file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        // Generate a unique filename
        $originalName = $this->upload_file->getClientOriginalName();
        $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $this->upload_file->getClientOriginalExtension();
        $path1 = $this->upload_file->storeAs('product-mapping-uploads', $filename);
        $path = storage_path('app').'/'.$path1;

        $excelSheets = Excel::toArray([], $path);

        $dataRows = array_slice($excelSheets[0], 2);

        foreach($dataRows as $row) {
            if(!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $external_channel_code = trim($row[0]);
                $external_channel_name = trim($row[1]);
                $channel_code = trim($row[2]);

                $channel = Channel::where('code', $channel_code)->first();

                if(!empty($channel)) {

                    $mapping = ChannelMapping::where('account_id', $this->account->id)
                        ->where('external_channel_code', $external_channel_code)
                        ->first();

                    if(!$mapping) {
                        $mapping = new ChannelMapping();
                    }

                    $mapping->account_id = $this->account->id;
                    $mapping->channel_id = $channel->id;
                    $mapping->external_channel_code = $external_channel_code;
                    $mapping->external_channel_name = $external_channel_name;
                    $mapping->save();

                    $this->mapping_arr[] = [
                        'id' => $mapping->id,
                        'channel_id' => $mapping->channel_id,
                        'external_channel_code' => $mapping->external_channel_code,
                        'external_channel_name' => $mapping->external_channel_name,
                    ];
                }

            }
        }
    }
}
