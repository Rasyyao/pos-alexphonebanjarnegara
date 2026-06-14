<?php

namespace App\Livewire;

use App\Models\ProductBrand;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class StockFilter extends Component
{
    use WithPagination;

    public string $search    = '';
    public ?int $brand_id    = null;
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
        $this->brand_id  = null;
        $this->unit_type = '';
        $this->status    = 'ready';
        $this->ram       = '';
        $this->rom       = '';
        $this->color     = '';
        $this->grade     = '';
        $this->resetPage();
    }

    public function render()
    {
        $brands = ProductBrand::orderBy('name')->get();

        $units = Unit::with('model.brand')
            ->when($this->search, fn($q) => $q->whereHas('model', fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('brand', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ))
            ->when($this->brand_id, fn($q) => $q->whereHas('model', fn($q) => $q->where('brand_id', $this->brand_id)))
            ->when($this->unit_type, fn($q) => $q->where('unit_type', $this->unit_type))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->ram, fn($q) => $q->where('ram', $this->ram))
            ->when($this->rom, fn($q) => $q->where('rom', $this->rom))
            ->when($this->color, fn($q) => $q->where('color', $this->color))
            ->when($this->grade, fn($q) => $q->where('grade', $this->grade))
            ->latest()
            ->paginate(10);

        return view('livewire.stock-filter', compact('brands', 'units'));
    }
}
