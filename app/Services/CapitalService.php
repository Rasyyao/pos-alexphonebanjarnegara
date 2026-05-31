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
        $validated['created_by'] = $actor->id;
        return $this->capitals->create($validated);
    }

    public function update(Capital $capital, array $validated): Capital
    {
        return $this->capitals->update($capital, $validated);
    }

    public function destroy(Capital $capital): void
    {
        $this->capitals->delete($capital);
    }
}
