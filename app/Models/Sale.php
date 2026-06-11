<?php

namespace App\Models;

use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'created_by', 'approved_by', 'invoice_number', 'customer_name', 'description', 'sale_date',
        'total_price', 'total_paid', 'profit', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status'      => SaleStatus::class,
            'sale_date'   => 'date',
            'total_price' => 'decimal:2',
            'total_paid'  => 'decimal:2',
            'profit'      => 'decimal:2',
        ];
    }

    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function items()    { return $this->hasMany(SaleItem::class); }
    public function payments() { return $this->hasMany(SalePayment::class); }
    public function debt()     { return $this->hasOne(Debt::class); }

    public function scopeApproved(Builder $q): Builder { return $q->where('status', 'approved'); }
    public function scopePending(Builder $q): Builder  { return $q->where('status', 'pending'); }
}
