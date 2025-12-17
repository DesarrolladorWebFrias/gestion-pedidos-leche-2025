<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'opening_date',
        'closing_date',
        'inventory_date',
        'total_collected',
        'processed_orders',
        'closure_status',
        'in_inventory',
    ];

    protected $casts = [
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
        'inventory_date' => 'date',
        'in_inventory' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
