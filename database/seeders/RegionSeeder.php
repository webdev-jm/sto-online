<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $region_json = file_get_contents(public_path('address-db/json/table_region.json'));
        $data = json_decode($region_json, 'true');

        foreach($data as $row) {
            if(!empty($row['region_name'])) {
                $region = new Region([
                    'region_name' => $row['region_name'],
                    'description' => $row['region_description'],
                ]);
                $region->save();
            }
        }
    }
}
