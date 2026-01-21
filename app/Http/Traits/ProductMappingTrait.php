<?php

namespace App\Http\Traits;

use App\Models\ProductMapping;

Trait ProductMappingTrait {

    public function productMapping($account_id, $stock_code) {
        $product_mapping = ProductMapping::where('account_id', $account_id)
            ->where('external_stock_code', $stock_code)
            ->first();

        // $product_mappings = [
        //     '3000058' => [
        //         3 => [
        //             'BCP0001' => 'KS01027',
        //             'BCP0002' => 'KS01030',
        //             'BCP0003' => 'DW01008',
        //             'BCP0004' => 'KS01032',
        //             'BCP0005' => 'KS03002',
        //         ],
        //         2 => [
        //             'KS03002FG' => 'KS03002',
        //             'KS01028FG' => 'KS01028',
        //             'KS01027FG' => 'KS01027',
        //             'KS01030FG' => 'KS01030',
        //             'DW01002FG' => 'DW01002',
        //         ],
        //     ],
        //     '3000076' => [
        //         3 => [
        //             'BCP0001' => 'KS01027',
        //             'BCP0002' => 'KS01030',
        //             'BCP0003' => 'DW01008',
        //             'BCP0004' => 'KS01032',
        //             'BCP0005' => 'KS03002',
        //         ],
        //         2 => [
        //             'KS03002FG' => 'KS03002',
        //             'KS01028FG' => 'KS01028',
        //             'KS01027FG' => 'KS01027',
        //             'KS01030FG' => 'KS01030',
        //             'DW01002FG' => 'DW01002',
        //         ],
        //     ],
        //     '3000062' => [
        //         3 => [
        //             'FGKS01032' => 'KS01032',
        //             'FGKS01027' => 'KS01027',
        //             'FGKS01030' => 'KS01030',
        //             'FGDW01008' => 'DW01008',
        //             'FGKS01031' => 'KS01031',
        //             'FGKS01033' => 'KS01033',
        //             'FGDW01003' => 'DW01003',
        //             'FGKS01028' => 'KS01028',
        //             'FGKS01046' => 'KS01046',
        //             'FGKS01035' => 'KS01035',
        //             'FGKS01029' => 'KS01029',
        //             'FGKS01036' => 'KS01036',
        //             'FGKS03002' => 'KS03002',
        //             'FGDW01001' => 'DW01001',
        //             'FGDW01002' => 'DW01002',
        //             'FGKS04009' => 'KS04009',
        //             'FGKS04013' => 'KS04013',
        //             'FGKS01034' => 'KS01034',
        //             'FGKS04010' => 'KS04010',
        //             'FGKS09006' => 'KS09006',
        //             'FG421766' => 'KS01027',
        //             'FGKM01005' => 'KM01005',
        //         ]
        //     ]
        // ];

        $type = NULL;
        if(!empty($product_mapping)) {
            $stock_code = $product_mapping->product->stock_code;
            $type = $product_mapping->type;
        }

        return [$stock_code, $type];
    }
}
