<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id', 'type', 'is_default',
        'full_name', 'phone', 'company_name', 'tax_id',
        'country', 'province', 'district', 'address_line'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
