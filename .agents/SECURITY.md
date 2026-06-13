# SECURITY.md — Security Rules & Auth Patterns

> Read this before touching any route, middleware, controller, form, or user input.
> These rules are non-negotiable. Never skip or bypass them.

---

## Authentication

- Auth is handled by **Laravel Breeze** (session-based, standard `auth` middleware).
- All non-login routes are wrapped in `Route::middleware('auth')`.
- Login field: `username` (not email). Customize Breeze's `AuthenticatedSessionController` accordingly.
- Passwords are hashed with `bcrypt` via `Hash::make()`. Never store plaintext.
- Session invalidation on logout: `auth()->logout()` + `$request->session()->invalidate()` + `$request->session()->regenerateToken()`.

---

## Role middleware

```php
// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
```

Register in `bootstrap/app.php` (Laravel 13):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]);
})
```

Usage:
```php
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    // superadmin-only routes
});
```

---

## Authorization rules

### Never trust role from frontend
Always read `auth()->user()->role` server-side. Never accept a role value from the request.

### Controller-level gate for sensitive actions

```php
// SaleController::approve()
public function approve(Sale $sale): RedirectResponse
{
    // Explicit gate check even though route is behind middleware
    // Defense in depth — two layers
    if (auth()->user()->role !== 'superadmin') {
        abort(403);
    }

    // Proceed...
}
```

### Ownership check on editable resources

Admin can only approve their own created sales (if that rule is added later).
Always use `$sale->created_by === auth()->id()` before allowing edit.
Use Laravel Policies when ownership checks become complex:

```php
// app/Policies/SalePolicy.php
public function approve(User $user, Sale $sale): bool
{
    return $user->role === 'superadmin';
}

public function update(User $user, Sale $sale): bool
{
    return $sale->status === 'pending' && $sale->created_by === $user->id;
}
```

---

## Input validation rules

### Always use FormRequest — never validate in Controllers

```php
// WRONG
public function store(Request $request)
{
    $request->validate([...]);
}

// CORRECT
public function store(StoreSaleRequest $request)
{
    // $request is already validated
}
```

### Validation must-haves

| Input type | Rules to always include |
|---|---|
| String fields | `string`, `max:N`, `strip_tags` via sanitizer |
| Numeric/money | `numeric`, `min:0` — never allow negative |
| Enum fields | `in:value1,value2` — whitelist only |
| Foreign key | `exists:{table},{column}` |
| File upload | `file`, `mimes:jpeg,png,webp`, `max:2048` |
| Date | `date` or `date_format:Y-m-d` |
| Array inputs | `array`, `min:1`, then `*` rules for each item |
| IMEI/SN | `nullable`, `string`, `max:20`, `unique:units,imei` with ignore on update |

### Never trust `selling_price` from the frontend alone

Always re-fetch `purchase_price` from the database in `SaleService::create()`:
```php
// WRONG — trusting frontend-provided purchase_price
$purchasePrice = $item['purchase_price'];

// CORRECT — always read from DB
$purchasePrice = $this->units->findById($item['unit_id'])->purchase_price;
```

---

## CSRF

Laravel's `web` middleware group includes `VerifyCsrfToken` by default.
- All POST/PUT/PATCH/DELETE forms must include `@csrf`.
- All Livewire forms have CSRF handled automatically.
- Never add routes to `$except` in `VerifyCsrfToken` unless it's a documented external webhook.

---

## Mass assignment protection

All models must define either `$fillable` or `$guarded`. Never use `$guarded = []`.

```php
// Unit.php
protected $fillable = [
    'model_id', 'created_by', 'unit_type', 'ram', 'rom',
    'color', 'imei', 'serial_number', 'purchase_price',
    'photo_path', 'notes', 'status', 'purchase_date',
];

// Sale.php
protected $fillable = [
    'created_by', 'approved_by', 'invoice_number', 'sale_date',
    'total_price', 'total_paid', 'profit', 'status',
];
```

Never allow `role`, `is_active`, `approved_by`, or `created_by` to be filled from user-facing forms.

---

## File upload security

```php
// StoreUnitRequest.php rules
'photo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
```

```php
// In UnitService::store()
if ($photo) {
    // store() generates a random filename — do NOT use original name
    $path = $photo->store('units', 'public');
    $validated['photo_path'] = $path;
}
```

- Always use `->store()` (generates random filename) — never `->storeAs()` with user-supplied names.
- Validate `mimes` whitelist — reject PHP, JS, SVG, etc.
- Store inside `storage/app/public/` — never inside `public/` directly.
- Run `php artisan storage:link` once on deploy.

---

## SQL injection prevention

- Always use Eloquent or query builder with bound parameters. Never concatenate user input into raw SQL.

```php
// WRONG — SQL injection risk
DB::select("SELECT * FROM units WHERE color = '{$color}'");

// CORRECT — parameterized
Unit::where('color', $color)->get();

// CORRECT — raw with binding
DB::select('SELECT * FROM units WHERE color = ?', [$color]);
```

---

## XSS prevention

- Blade uses `{{ }}` (escaped) by default. Never use `{!! !!}` with user-provided content.
- Sanitize text inputs that will be displayed:
```php
// In FormRequest or Service before saving
$data['notes'] = strip_tags($request->input('notes'));
```

- For rich-text fields (if added later), use a whitelist HTML purifier, not `strip_tags`.

---

## Inactive user enforcement

Check `is_active` on every login attempt. Override the default Breeze login logic:

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
protected function authenticated(Request $request, $user): void
{
    if (! $user->is_active) {
        auth()->logout();
        throw ValidationException::withMessages([
            'username' => 'Akun Anda telah dinonaktifkan.',
        ]);
    }
}
```

Alternatively, add a `CheckUserActive` middleware on all `auth` routes.

---

## Superadmin self-protection

Superadmin cannot deactivate or delete their own account:

```php
// AdminUserController::update() / destroy()
if ($targetUser->id === auth()->id()) {
    return back()->with('error', 'Tidak dapat memodifikasi akun sendiri.');
}
```

---

## Sensitive data exposure

- `purchase_price` of units must never be exposed in frontend JS or Livewire public properties.
  Pass it only to authenticated server-side views.
- Livewire `$rules` and public properties are visible in HTML source — never put purchase prices,
  profits, or other sensitive figures as Livewire public properties that are not intended to be shown.
- API responses (if added): always use Eloquent API Resources to whitelist returned fields.

---

## Logging

Use structured logging for all business-critical events:

```php
use Illuminate\Support\Facades\Log;

// On sale approved
Log::info('Sale approved', [
    'sale_id'     => $sale->id,
    'invoice'     => $sale->invoice_number,
    'approved_by' => auth()->id(),
    'total'       => $sale->total_price,
]);

// On login failure (handled by Breeze / Laravel default)
// On user created/deactivated by superadmin
Log::info('Admin user deactivated', [
    'target_user_id' => $user->id,
    'by'             => auth()->id(),
]);
```

Never log passwords, full credit card info, or raw request payloads.

---

## Checklist before shipping any feature

- [ ] All routes behind `auth` middleware
- [ ] Superadmin routes behind `role:superadmin` middleware
- [ ] All forms use `@csrf`
- [ ] All form inputs validated via FormRequest
- [ ] No `$guarded = []` on any model
- [ ] No user-supplied filenames used in file storage
- [ ] No raw SQL concatenation with user input
- [ ] No `{!! !!}` with user-provided content
- [ ] Selling price re-validated server-side (not trusted from frontend)
- [ ] Superadmin cannot delete/deactivate their own account
- [ ] Inactive user blocked on login
