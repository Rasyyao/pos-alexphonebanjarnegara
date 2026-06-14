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
    public string $period = 'all';
    public string $date   = '';
    public string $startDate = '';
    public string $endDate = '';

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedStatus(): void    { $this->resetPage(); }
    public function updatedPeriod(): void    { $this->resetPage(); }
    public function updatedDate(): void      { $this->resetPage(); }
    public function updatedStartDate(): void { $this->resetPage(); }
    public function updatedEndDate(): void   { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->period = 'all';
        $this->date   = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function render(SaleRepositoryInterface $repository)
    {
        $sales = $repository->paginate([
            'search'     => $this->search,
            'status'     => $this->status,
            'period'     => $this->period,
            'date'       => $this->date,
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ]);

        return view('livewire.transaction-filter', compact('sales'));
    }
}
