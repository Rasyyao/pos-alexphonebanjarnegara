<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'password', 'role', 'is_active'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'role'      => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function units()        { return $this->hasMany(Unit::class, 'created_by'); }
    public function createdSales() { return $this->hasMany(Sale::class, 'created_by'); }
    public function approvedSales(){ return $this->hasMany(Sale::class, 'approved_by'); }
    public function capitals()     { return $this->hasMany(Capital::class, 'created_by'); }
}
