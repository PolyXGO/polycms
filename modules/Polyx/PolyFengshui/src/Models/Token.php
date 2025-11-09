<?php

namespace Modules\Polyx\PolyFengshui\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'polyfengshui_tokens';

    protected $fillable = [
        'name',
        'token',
        'domain',
    ];
}

