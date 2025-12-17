<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monthly_closure_id',
        'order_date',
        'estimated_delivery_date',
        'actual_delivery_date',
        'total_amount',
        'order_status',
        'payment_method',
        'payment_status',
        'observations',
        'confirmed_by_user_id',
        'confirmation_date',
        'delivered_by_user_id',
        'delivery_date',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'datetime',
        'confirmation_date' => 'datetime',
        'delivery_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function monthlyClosure()
    {
        return $this->belongsTo(MonthlyClosure::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by_user_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
