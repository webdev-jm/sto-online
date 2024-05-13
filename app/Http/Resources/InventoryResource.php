<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\LocationResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\InventoryUploadResource;

class InventoryResource extends JsonResource
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
            'uom' => $this->uom,
            'inventory' => $this->inventory,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => new ProductResource($this->product),
            'location' => new LocationResource($this->location),
            'inventory_upload' => new InventoryUploadResource($this->inventory_upload)
        ];
    }
}
