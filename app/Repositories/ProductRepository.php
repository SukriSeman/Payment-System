<?php

namespace App\Repositories;

use App\Models\Product;

final class ProductRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Product());
    }
}
