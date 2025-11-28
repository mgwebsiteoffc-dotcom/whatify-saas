<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbandonedCheckout extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'shopify_checkout_id',
        'customer_phone',
        'customer_email',
        'total_price',
        'line_items',
        'abandoned_at',
        'recovery_status',
        'recovery_attempts',
    ];

    protected $casts = [
        'line_items'   => 'array',
        'abandoned_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
