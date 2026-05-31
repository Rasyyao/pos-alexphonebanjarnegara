<?php

namespace App\Livewire;

use App\Repositories\Contracts\AccessoryRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class AccessoryFilter extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $category     = '';
    public string $stock_status = '';

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedCategory(): void    { $this->resetPage(); }
    public function updatedStockStatus(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search       = '';
        $this->category     = '';
        $this->stock_status = '';
        $this->resetPage();
    }

    public function render(AccessoryRepositoryInterface $repository)
    {
        $categories = $repository->categories();

        $accessories = $repository->paginate([
            'search'       => $this->search,
            'category'     => $this->category,
            'stock_status' => $this->stock_status,
        ]);

        return view('livewire.accessory-filter', compact('categories', 'accessories'));
    }
}
