<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Channel;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channel_arr = [
            'SMKT'      => 'SUPERMARKET',
            'GRO'       => 'GROCERY',
            'DEPT'      => 'DEPARTMENT STORE',
            'WS'        => 'WHOLESALER',
            'SSS'       => 'SARI-SARI STORE',
            'ECOM'      => 'E-COMMERCE',
            'CVS'       => 'CONVENIENCE STORE',
            'DS-SS'     => 'DRUGSTORE SELF SERVICE',
            'DS-OTC'    => 'DRUGSTORE OVER THE COUNTER',
            'HO'        => 'HEAD OFFICE',
            'MS'        => 'MARKET STALLS/PUBLIC MARKET',
            'HYPER'     => 'HYPERMARKET',
            'MINI'      => 'MINIMART',
            'DS-IDS'    => 'DRUGSTORE INDEPENDENT',
            'SPEC'      => 'SPECIALTY STORE',
            'INSTI'     => 'INSTITUTIONAL',
            'SUBD'      => 'SUB DISTRIBUTOR',
            'OT'        => 'OTHER',
        ];

        foreach($channel_arr as $key => $val) {
            $channel = new Channel([
                'code' => $key,
                'name' => $val
            ]);
            $channel->save();
        }
    }
}
