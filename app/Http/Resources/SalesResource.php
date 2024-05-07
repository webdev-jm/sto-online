<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SalesmanResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\ChannelResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\SalesUploadResource;

class SalesResource extends JsonResource
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
            'type' => $this->type,
            'date' => $this->date,
            'document_number' => $this->document_number,
            'category' => $this->category,
            'uom' => $this->uom,
            'quantity' => $this->quantity,
            'price_inc_vat' => $this->price_inc_vat,
            'amount' => $this->amount,
            'amount_inc_vat' => $this->amount_inc_vat,
            'status' => $this->status,
            'sales_upload' => new SalesUploadResource($this->sales_upload),
            'customer' => new CustomerResource($this->customer),
            'channel' => new ChannelResource($this->channel),
            'salesman' => new SalesmanResource($this->salesman),
            'location' => new LocationResource($this->location),
            'product' => new ProductResource($this->product),
            'user' => new UserResource($this->user),
        ];
    }
}
