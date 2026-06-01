<?php
namespace App\Repositories;

use App\Models\Capital;
use App\Repositories\Contracts\CapitalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CapitalRepository implements CapitalRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Capital::with('creator')->latest('entry_date')->paginate($perPage);
    }

    public function sumTotal(): float
    {
        $in  = (float) Capital::whereIn('type', ['initial', 'addition', 'purchase'])->sum('amount');
        $out = (float) Capital::where('type', 'withdrawal')->sum('amount');
        return $in - $out;
    }

    public function sumInitialAndAddition(): float
    {
        return (float) Capital::whereIn('type', ['initial', 'addition'])->sum('amount');
    }

    public function create(array $data): Capital
    {
        return Capital::create($data);
    }

    public function update(Capital $capital, array $data): Capital
    {
        $capital->update($data);
        return $capital->fresh();
    }

    public function delete(Capital $capital): void
    {
        $capital->delete();
    }
}
