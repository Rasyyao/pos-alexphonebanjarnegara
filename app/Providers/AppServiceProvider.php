<?php

namespace App\Providers;

use App\Models\SaleItem;
use App\Observers\SaleItemObserver;
use App\Repositories\AccessoryRepository;
use App\Repositories\CapitalRepository;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Repositories\Contracts\CapitalRepositoryInterface;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\DebtRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\SaleRepository;
use App\Repositories\UnitRepository;
use App\Repositories\Contracts\ProductBrandRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\ProductBrandRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UnitRepositoryInterface::class,      UnitRepository::class);
        $this->app->bind(AccessoryRepositoryInterface::class, AccessoryRepository::class);
        $this->app->bind(SaleRepositoryInterface::class,      SaleRepository::class);
        $this->app->bind(CapitalRepositoryInterface::class,   CapitalRepository::class);
        $this->app->bind(DebtRepositoryInterface::class,      DebtRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class,   ExpenseRepository::class);
        $this->app->bind(ProductBrandRepositoryInterface::class, ProductBrandRepository::class);
        $this->app->bind(UserRepositoryInterface::class,         UserRepository::class);
    }

    public function boot(): void
    {
        SaleItem::observe(SaleItemObserver::class);
    }
}
