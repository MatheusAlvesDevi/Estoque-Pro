<?php

namespace App\Repository;

use App\Models\Supplier;

class SupplierRepository extends BaseRepository
{
    public function __construct(Supplier $supply)
    {
        parent::__construct($supply);
    }
}