<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'trigger',
        'is_active',
        'flow_definition',
    ];

    protected $casts = [
        'flow_definition' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
