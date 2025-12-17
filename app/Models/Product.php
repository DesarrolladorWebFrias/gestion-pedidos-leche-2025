<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'current_unit_price',
        'measurement_unit',
        'pieces_per_box',
        'inventory_status',
        'product_status',
        'updated_act',
        'updated_by_user_id',
    ];

    protected $casts = [
        'updated_act' => 'datetime',
        'current_unit_price' => 'decimal:2',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }
}
