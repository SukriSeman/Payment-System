<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Throwable;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) { }

    public function list()
    {
        return Response()->json([
            'status' => 'success',
            'data' => $this->productService->getAll()
        ]);
    }
}
