# AGENTS.md — POS Toko HP Pak Dika

> **Entry point.** Drop all 4 files at the Laravel project root.
> Claude Code MUST read all referenced files before writing any code.

---

## Project overview

Point-of-sale system for a cellphone store. Manages HP unit inventory, accessories, daily sales with multi-payment splits, debt tracking, and financial reporting. Two roles: `superadmin` (full access) and `admin` (daily operations).

## Tech stack

- Laravel 13 + PHP 8.5
- MySQL 8 (InnoDB, utf8mb4)
- Blade + Alpine.js + Tailwind CSS v3
- `maatwebsite/excel` — `.xlsx` export
- Spatie Media Library — unit photo storage
- Laravel Breeze — auth scaffold (then customized)

## Required reading before any task

| File | Read when |
|---|---|
| `DATABASE.md` | Any migration, model, query, or relationship work |
| `FLOW.md` | Any controller, route, service, or feature logic |
| `PATTERNS.md` | Any new file or class — enforces repo/service structure |
| `SECURITY.md` | Any auth, middleware, route, or user-input handling |

## Development phases

### Phase 1 — Foundation
- [ ] Auth (Breeze), Role middleware, Blade layout
- [ ] All migrations + seeders
- [ ] Brand & model CRUD (superadmin)

### Phase 2 — Stock
- [ ] Units CRUD + photo upload + Livewire StockFilter
- [ ] Accessories CRUD + price filter

### Phase 3 — Sales
- [ ] SaleForm Livewire (expandable items, payment split, live total)
- [ ] SaleController + SaleService (create + approve flow)
- [ ] Print struk (Blade print-only view)

### Phase 4 — Finance & Reports
- [ ] DashboardStats Livewire
- [ ] Capital CRUD + Debt management
- [ ] Finance page (superadmin)
- [ ] Excel exports: stock, opname, daily sales, full finance

### Phase 5 — Admin management
- [ ] AdminUser CRUD (superadmin only)
- [ ] Daily finance report (admin read-only)

## Hard rules

- Never put business logic in Controllers or Blade — use Services.
- Never skip reading `SECURITY.md` before touching routes or auth.
- Never use `dd()` or `dump()` in any committed code — use `Log::debug()`.
- Never delete migrations — rollback and recreate.
