<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shopify_domain',
        'shopify_access_token',
        'whatify_token',
        'business_phone',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function abandonedCheckouts()
    {
        return $this->hasMany(AbandonedCheckout::class);
    }

    public function automations()
    {
        return $this->hasMany(WhatsappAutomation::class);
    }
}
