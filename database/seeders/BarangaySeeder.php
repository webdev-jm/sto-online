<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barangay;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $barangay_json = file_get_contents(public_path('address-db/json/table_barangay.json'));
        $data = json_decode($barangay_json, true);

        foreach($data as $row) {
            $barangay_json = new Barangay([
                'municipality_id' => $row['municipality_id'],
                'barangay_name' => $row['barangay_name']
            ]);
            $barangay_json->save();
        }
    }
}
