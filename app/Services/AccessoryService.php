<?php

namespace App\Services;

use App\Models\Accessory;
use App\Repositories\Contracts\AccessoryRepositoryInterface;

class AccessoryService
{
    public function __construct(private readonly AccessoryRepositoryInterface $accessories) {}

    public function store(array $validated): Accessory
    {
        return $this->accessories->create($validated);
    }

    public function update(Accessory $accessory, array $validated): Accessory
    {
        return $this->accessories->update($accessory, $validated);
    }

    public function destroy(Accessory $accessory): void
    {
        $this->accessories->delete($accessory);
    }
}
