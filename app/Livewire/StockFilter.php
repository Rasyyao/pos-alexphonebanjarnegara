<?php

namespace App\Livewire;

use App\Models\ProductBrand;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class StockFilter extends Component
{
    use WithPagination;

    public ?int $brand_id   = null;
    public ?int $model_id   = null;
    public string $unit_type = '';
    public string $status    = 'ready';
    public string $ram       = '';
    public string $rom       = '';
    public string $color     = '';
    public string $grade     = '';

    public function updatingBrandId(): void
    {
        $this->model_id = null;
        $this->resetPage();
    }

    public function updatedBrandId(): void    { $this->resetPage(); }
    public function updatedModelId(): void    { $this->resetPage(); }
    public function updatedUnitType(): void   { $this->resetPage(); }
    public function updatedStatus(): void     { $this->resetPage(); }
    public function updatedRam(): void        { $this->resetPage(); }
    public function updatedRom(): void        { $this->resetPage(); }
    public function updatedColor(): void      { $this->resetPage(); }
    public function updatedGrade(): void      { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->brand_id  = null;
        $this->model_id  = null;
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
        $brands = ProductBrand::with('models')->orderBy('name')->get();

        $models = $this->brand_id
            ? ProductBrand::find($this->brand_id)?->models()->orderBy('name')->get() ?? collect()
            : collect();

        $units = Unit::with('model.brand')
            ->when($this->brand_id, fn($q) => $q->whereHas('model', fn($q) => $q->where('brand_id', $this->brand_id)))
            ->when($this->model_id, fn($q) => $q->where('model_id', $this->model_id))
            ->when($this->unit_type, fn($q) => $q->where('unit_type', $this->unit_type))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->ram, fn($q) => $q->where('ram', $this->ram))
            ->when($this->rom, fn($q) => $q->where('rom', $this->rom))
            ->when($this->color, fn($q) => $q->where('color', $this->color))
            ->when($this->grade, fn($q) => $q->where('grade', $this->grade))
            ->latest()
            ->paginate(10);

        return view('livewire.stock-filter', compact('brands', 'models', 'units'));
    }
}
