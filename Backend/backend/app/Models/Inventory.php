<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'category',
        'quantity',
        'unit',
        'minimum_stock',
        'reorder_point',
        'supplier',
        'last_restock_date',
        'expiry_date',
        'status', // 'in_stock', 'low_stock', 'out_of_stock'
    ];

    protected $casts = [
        'last_restock_date' => 'datetime',
        'expiry_date' => 'date',
    ];

    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function needsRestock()
    {
        return $this->quantity <= $this->minimum_stock;
    }
} 