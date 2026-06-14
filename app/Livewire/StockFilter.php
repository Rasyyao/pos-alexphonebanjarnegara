<?php

namespace App\Livewire;

use App\Models\ProductBrand;
use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class StockFilter extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $brand_id  = '';
    public string $unit_type = '';
    public string $status    = 'ready';
    public string $ram       = '';
    public string $rom       = '';
    public string $color     = '';
    public string $grade     = '';

    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedBrandId(): void   { $this->resetPage(); }
    public function updatedUnitType(): void  { $this->resetPage(); }
    public function updatedStatus(): void    { $this->resetPage(); }
    public function updatedRam(): void       { $this->resetPage(); }
    public function updatedRom(): void       { $this->resetPage(); }
    public function updatedColor(): void     { $this->resetPage(); }
    public function updatedGrade(): void     { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search    = '';
        $this->brand_id  = '';
        $this->unit_type = '';
        $this->status    = 'ready';
        $this->ram       = '';
        $this->rom       = '';
        $this->color     = '';
        $this->grade     = '';
        $this->resetPage();
    }

    public function render(UnitRepositoryInterface $repository)
    {
        $brands = ProductBrand::orderBy('name')->get();

        $units = $repository->paginate([
            'search'    => $this->search,
            'brand_id'  => $this->brand_id,
            'unit_type' => $this->unit_type,
            'status'    => $this->status,
            'ram'       => $this->ram,
            'rom'       => $this->rom,
            'color'     => $this->color,
            'grade'     => $this->grade,
        ], 10);

        return view('livewire.stock-filter', compact('brands', 'units'));
    }
}
