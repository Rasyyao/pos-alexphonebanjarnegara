<?php
namespace App\Services;

use App\Models\Capital;
use App\Models\User;
use App\Repositories\Contracts\CapitalRepositoryInterface;

class CapitalService
{
    public function __construct(
        private readonly CapitalRepositoryInterface $capitals,
    ) {}

    public function store(array $validated, User $actor): Capital
    {
        \App\Services\DailyClosingService::assertDateNotLocked($validated['entry_date']);
        $validated['created_by'] = $actor->id;
        return $this->capitals->create($validated);
    }

    public function update(Capital $capital, array $validated): Capital
    {
        \App\Services\DailyClosingService::assertDateNotLocked($capital->entry_date->toDateString());
        \App\Services\DailyClosingService::assertDateNotLocked($validated['entry_date']);
        return $this->capitals->update($capital, $validated);
    }

    public function destroy(Capital $capital): void
    {
        \App\Services\DailyClosingService::assertDateNotLocked($capital->entry_date->toDateString());
        $this->capitals->delete($capital);
    }
}
