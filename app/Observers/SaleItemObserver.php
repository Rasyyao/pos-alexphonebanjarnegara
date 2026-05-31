<?php

namespace App\Observers;

use App\Models\SaleItem;

class SaleItemObserver
{
    public function saving(SaleItem $item): void
    {
        $hasUnit      = !is_null($item->unit_id);
        $hasAccessory = !is_null($item->accessory_id);

        if ($hasUnit && $hasAccessory) {
            throw new \LogicException('SaleItem cannot have both unit_id and accessory_id.');
        }
        if (!$hasUnit && !$hasAccessory) {
            throw new \LogicException('SaleItem must have either unit_id or accessory_id.');
        }
    }
}
