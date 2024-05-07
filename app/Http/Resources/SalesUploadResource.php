<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sku_count' => $this->sku_count,
            'total_quantity' => $this->total_quantity,
            'total_price_vat' => $this->total_price_vat,
            'total_amount' => $this->total_amount,
            'total_amount_vat' => $this->total_amount_vat,
            'total_cm_quantity' => $this->total_cm_quantity,
            'total_cm_price_vat' => $this->total_cm_price_vat,
            'total_cm_amount' => $this->total_cm_amount,
            'total_cm_amount_vat' => $this->total_cm_amount_vat,
        ];
    }
}
