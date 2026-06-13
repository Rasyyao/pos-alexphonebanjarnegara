# DATABASE.md — Schema & Relationships

> Read this before writing any migration, model, Eloquent query, or seeder.

---

## Migration run order

Run in exactly this sequence to satisfy FK constraints:

1. `create_users_table`
2. `create_product_brands_table`
3. `create_product_models_table`
4. `create_units_table`
5. `create_accessories_table`
6. `create_sales_table`
7. `create_sale_items_table`
8. `create_sale_payments_table`
9. `create_debts_table`
10. `create_capitals_table`

---

## Tables

### `users`
| column | type | constraints |
|---|---|---|
| id | bigint | PK, auto-increment |
| name | string(100) | not null |
| username | string(50) | unique, not null |
| password | string | bcrypt hashed |
| role | enum('superadmin','admin') | not null |
| is_active | boolean | default true |
| created_at / updated_at | timestamps | |

### `product_brands`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| name | string(80) | unique, not null |

### `product_models`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| brand_id | bigint | FK → product_brands(id), cascade delete |
| name | string(100) | not null |

### `units`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| model_id | bigint | FK → product_models(id) |
| created_by | bigint | FK → users(id) |
| unit_type | enum('baru','second') | not null |
| ram | string(20) | e.g. "8GB" |
| rom | string(20) | e.g. "256GB" |
| color | string(50) | |
| imei | string(20) | nullable, unique when not null |
| serial_number | string(50) | nullable |
| purchase_price | decimal(12,2) | not null — harga beli |
| photo_path | string | nullable |
| notes | text | nullable |
| status | enum('ready','sold','returned') | default 'ready' |
| purchase_date | date | not null |
| created_at / updated_at | timestamps | |

### `accessories`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| name | string(100) | not null |
| category | string(80) | nullable |
| stock_qty | int | default 0, not null |
| purchase_price | decimal(10,2) | not null |
| selling_price | decimal(10,2) | not null |
| created_at / updated_at | timestamps | |

### `sales`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| created_by | bigint | FK → users(id) |
| approved_by | bigint | FK → users(id), nullable |
| invoice_number | string(30) | unique, auto-generated |
| sale_date | date | not null |
| total_price | decimal(14,2) | sum of sale_items.subtotal |
| total_paid | decimal(14,2) | sum of sale_payments.amount |
| profit | decimal(14,2) | computed server-side |
| status | enum('pending','approved','cancelled') | default 'pending' |
| created_at / updated_at | timestamps | |

### `sale_items`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| sale_id | bigint | FK → sales(id), cascade delete |
| unit_id | bigint | FK → units(id), **nullable** |
| accessory_id | bigint | FK → accessories(id), **nullable** |
| purchase_price | decimal(12,2) | snapshot at time of sale |
| selling_price | decimal(12,2) | harga jual input by admin |
| quantity | int | default 1; units always 1 |
| subtotal | decimal(14,2) | selling_price × quantity |

> **Critical constraint:** exactly one of `unit_id` or `accessory_id` must be non-null.
> Enforce in `SaleItem::saving()` observer — do not rely on DB alone.

### `sale_payments`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| sale_id | bigint | FK → sales(id), cascade delete |
| method | enum('cash','transfer','utang') | not null |
| amount | decimal(14,2) | not null |
| created_at | timestamp | |

### `debts`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| sale_id | bigint | FK → sales(id) |
| amount | decimal(14,2) | mirrors sum of utang payments |
| paid_amount | decimal(14,2) | default 0 |
| due_date | date | nullable |
| status | enum('unpaid','partial','paid') | default 'unpaid' |
| created_at / updated_at | timestamps | |

### `capitals`
| column | type | constraints |
|---|---|---|
| id | bigint | PK |
| created_by | bigint | FK → users(id) |
| description | string(255) | not null |
| amount | decimal(14,2) | not null |
| type | enum('initial','addition','purchase') | not null |
| entry_date | date | not null |
| created_at / updated_at | timestamps | |

---

## Eloquent relationships

```php
// User.php
hasMany(Unit::class, 'created_by')
hasMany(Sale::class, 'created_by')
hasMany(Sale::class, 'approved_by')
hasMany(Capital::class, 'created_by')

// ProductBrand.php
hasMany(ProductModel::class)

// ProductModel.php
belongsTo(ProductBrand::class)
hasMany(Unit::class)

// Unit.php
belongsTo(ProductModel::class)
belongsTo(User::class, 'created_by')
hasOne(SaleItem::class)           // a unit sold only once

// Accessory.php
hasMany(SaleItem::class)

// Sale.php
belongsTo(User::class, 'created_by')
belongsTo(User::class, 'approved_by')
hasMany(SaleItem::class)
hasMany(SalePayment::class)
hasOne(Debt::class)
scopeApproved($q)  → $q->where('status', 'approved')
scopePending($q)   → $q->where('status', 'pending')

// SaleItem.php
belongsTo(Sale::class)
belongsTo(Unit::class)->withDefault()
belongsTo(Accessory::class)->withDefault()

// SalePayment.php
belongsTo(Sale::class)

// Debt.php
belongsTo(Sale::class)

// Capital.php
belongsTo(User::class, 'created_by')
```

---

## Key queries (for Repository implementations)

```php
// Dashboard: stock summary
Unit::where('status', 'ready')->count();
Unit::where('status', 'ready')->where('unit_type', 'baru')->count();
Unit::where('status', 'ready')->where('unit_type', 'second')->count();

// Dashboard: today sales (approved only)
Sale::approved()->whereDate('sale_date', today())
    ->selectRaw('SUM(total_price) as revenue, SUM(profit) as profit')
    ->first();

// Dashboard: 7-day revenue chart
Sale::approved()
    ->whereBetween('sale_date', [now()->subDays(6)->toDateString(), now()->toDateString()])
    ->selectRaw('DATE(sale_date) as date, SUM(total_price) as total')
    ->groupBy('date')
    ->orderBy('date')
    ->get();

// Dashboard: payment method breakdown today
SalePayment::whereHas('sale', fn($q) =>
    $q->approved()->whereDate('sale_date', today())
)->selectRaw('method, SUM(amount) as total')
 ->groupBy('method')
 ->get();

// Dashboard: 5 latest ready units
Unit::with('model.brand')
    ->where('status', 'ready')
    ->latest()
    ->limit(5)
    ->get();

// Finance: total utang lifetime
Debt::where('status', '!=', 'paid')->sum('amount');

// Finance: asset value (cost of all ready units)
Unit::where('status', 'ready')->sum('purchase_price');
```

---

## Seeder order

```
DatabaseSeeder
  → UserSeeder           // superadmin (username: superadmin) + 1 admin
  → ProductBrandSeeder   // Samsung, Xiaomi, Oppo, Vivo, Apple, Realme
  → ProductModelSeeder   // 3-5 models per brand
  → AccessorySeeder      // sample: case, charger, earphone
  → UnitSeeder           // 10 sample ready units
```

---

## Naming conventions

| Artifact | Convention | Example |
|---|---|---|
| Model | singular PascalCase | `Unit`, `SaleItem`, `ProductModel` |
| Table | plural snake_case | `units`, `sale_items`, `product_models` |
| FK column | `{singular_table}_id` | `model_id`, `created_by` |
| Enum values | lowercase | `'ready'`, `'pending'`, `'cash'` |
| Decimal money | `decimal(14,2)` for totals, `decimal(12,2)` for unit prices | |
