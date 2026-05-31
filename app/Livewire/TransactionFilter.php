<?php

namespace App\Livewire;

use App\Repositories\Contracts\SaleRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionFilter extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $date   = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }
    public function updatedDate(): void   { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->date   = '';
        $this->resetPage();
    }

    public function render(SaleRepositoryInterface $repository)
    {
        $sales = $repository->paginate([
            'search' => $this->search,
            'status' => $this->status,
            'date'   => $this->date,
        ]);

        return view('livewire.transaction-filter', compact('sales'));
    }
}
