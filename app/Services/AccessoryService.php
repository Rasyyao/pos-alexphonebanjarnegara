<?php

namespace App\Services;

use App\Models\Accessory;
use App\Repositories\Contracts\AccessoryRepositoryInterface;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\AccessoryStatus;

class AccessoryService
{
    public function __construct(private readonly AccessoryRepositoryInterface $accessories) {}

    public function store(array $validated, User $actor): Accessory
    {
        \App\Services\DailyClosingService::assertDateNotLocked(today()->toDateString());
        $validated['status'] = $actor->role === UserRole::Superadmin
            ? AccessoryStatus::Approved
            : AccessoryStatus::Pending;

        return $this->accessories->create($validated);
    }

    public function approve(Accessory $accessory, User $actor): Accessory
    {
        \App\Services\DailyClosingService::assertDateNotLocked($accessory->created_at->toDateString());
        if ($accessory->status !== AccessoryStatus::Pending) {
            throw new \LogicException('Hanya aksesoris pending yang bisa di-approve.');
        }

        \Illuminate\Support\Facades\Log::info('Accessory approved', [
            'accessory_id' => $accessory->id,
            'approved_by'  => $actor->id,
        ]);

        return $this->accessories->update($accessory, ['status' => AccessoryStatus::Approved]);
    }

    public function update(Accessory $accessory, array $validated): Accessory
    {
        \App\Services\DailyClosingService::assertDateNotLocked($accessory->created_at->toDateString());
        return $this->accessories->update($accessory, $validated);
    }

    public function destroy(Accessory $accessory): void
    {
        \App\Services\DailyClosingService::assertDateNotLocked($accessory->created_at->toDateString());
        $this->accessories->delete($accessory);
    }
}
