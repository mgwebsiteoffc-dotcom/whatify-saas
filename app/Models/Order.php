<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'shopify_order_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'total_price',
        'financial_status',
        'payment_method',
        'cod_status',
        'line_items',
    ];

    protected $casts = [
        'line_items' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isCodPending(): bool
    {
        return $this->payment_method === 'COD' && $this->cod_status === 'pending';
    }
}
