<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SalesmanResource;
use App\Http\Resources\ChannelResource;

class CustomerResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'brgy' => $this->brgy,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'salesman' => new SalesmanResource($this->salesman),
            'channel' => new ChannelResource($this->channel),
        ];
    }
}
