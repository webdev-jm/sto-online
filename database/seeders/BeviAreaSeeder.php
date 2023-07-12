<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BeviArea;

class BeviAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areas_arr = [
            'GMA' => 'GMA',
            'NL' => 'NORTH LUZON',
            'SL' => 'SOUTH LUZON',
            'VIS' => 'VISAYAS',
            'MIN' => 'MIN',
            'OT' => 'OTHER'
        ];

        foreach($areas_arr as $code => $area) {
            $bevi_area = new BeviArea([
                'code' => $code,
                'name' => $area
            ]);
            $bevi_area->save();
        }
    }
}
