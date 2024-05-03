<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipality;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $municipality_json = file_get_contents(public_path('address-db/json/table_municipality.json'));
        $data = json_decode($municipality_json, true);

        foreach($data as $row) {
            if(!empty($row['municipality_name'])) {
                $municipality = new Municipality([
                    'province_id' => $row['province_id'],
                    'municipality_name' => $row['municipality_name'],
                ]);
                $municipality->save();
            }
        }
    }
}
