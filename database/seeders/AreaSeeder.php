<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $area_data = [
            [
                'account_id' => 245,
                'code' => 'PASIG',
                'name' => 'PASIG'
            ],
            [
                'account_id' => 245,
                'code' => 'MANILA',
                'name' => 'MANILA'
            ],
            [
                'account_id' => 245,
                'code' => 'QC',
                'name' => 'QUEZON CITY'
            ],
            [
                'account_id' => 245,
                'code' => 'CAMANAVA',
                'name' => 'CAMANAVA'
            ],
            [
                'account_id' => 245,
                'code' => 'SOUTH',
                'name' => 'SOUTH'
            ],
            [
                'account_id' => 245,
                'code' => 'SOUTH QC',
                'name' => 'SOUTH QC'
            ],
            [
                'account_id' => 245,
                'code' => 'LOWER QC',
                'name' => 'LOWER QC'
            ],
            [
                'account_id' => 245,
                'code' => 'UPPER QC',
                'name' => 'UPPER QC'
            ],
        ];

        foreach($area_data as $data) {
            $area = new Area([
                'account_id' => $data['account_id'],
                'code' => $data['code'],
                'name' => $data['name']
            ]);
            $area->save();
        }
    }
}
