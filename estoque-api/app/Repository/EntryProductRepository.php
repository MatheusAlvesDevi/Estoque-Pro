<?php

namespace App\Repository;

use App\Models\EntryProduct;

class EntryProductRepository extends BaseRepository
{
    public function __construct(EntryProduct $entry_product)
    {
        parent::__construct($entry_product);
    }
}