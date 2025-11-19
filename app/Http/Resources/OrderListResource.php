<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'unit_price' => $this->product->currency . " " . sprintf('%.2f', ($this->unit_price / 100)),
            'total_price' => $this->product->currency . " " . sprintf('%.2f', ($this->total_price / 100)),
        ];
    }
}
