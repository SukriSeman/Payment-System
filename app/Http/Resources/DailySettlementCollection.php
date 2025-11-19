<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DailySettlementCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($row) {
            return [
                'count' => (int) $row->count,
                'total' => sprintf('%.2f', ($row->total / 100)),
            ];
        });
    }
}
