<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $province_json = file_get_contents(public_path('address-db/json/table_province.json'));
        $data = json_decode($province_json, true);

        foreach($data as $row) {
            if(!empty($row['province_name'])) {
                $province = new Province([
                    'region_id' => $row['region_id'],
                    'province_name' => $row['province_name'],
                ]);
                $province->save();
            }
        }
    }
}
