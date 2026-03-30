<?php

namespace App\Repository;

use App\Models\ExitProduct;

class ExitProductRepository extends BaseRepository
{
    public function __construct(ExitProduct $exit_product)
    {
        parent::__construct($exit_product);
    }
}