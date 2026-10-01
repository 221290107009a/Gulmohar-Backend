<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price?->amount(),
            'special_price' => $this->special_price?->amount(),
            'sku' => $this->sku,
            'manage_stock' => $this->manage_stock,
            'qty' => $this->qty,
            'in_stock' => $this->in_stock,
            'is_active' => $this->is_active,
            'sellers' => $this->sellers->pluck('id'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}