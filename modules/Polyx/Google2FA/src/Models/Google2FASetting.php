<?php

declare(strict_types=1);

namespace Modules\Polyx\Google2FA\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Google2FASetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google2fa_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'google2fa_secret',
        'google2fa_enabled',
        'recovery_codes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'google2fa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'google2fa_enabled' => 'boolean',
        'recovery_codes' => 'json',
        'google2fa_secret' => 'encrypted',
    ];

    /**
     * Get the user that owns the 2FA settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
