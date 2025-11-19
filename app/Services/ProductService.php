<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\Order;
use App\Repositories\ProductRepository;

final class ProductService
{
    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function getAll()
    {
        return ProductResource::collection($this->productRepository->findAll());
    }
}
