<?php

namespace Modules\Polyx\PolyFengshui\Support;

use Illuminate\Support\Collection;
use Modules\Polyx\PolyFengshui\Models\Token;

class TokenPresenter
{
    public static function format(Token $token): array
    {
        return [
            'id' => $token->id,
            'name' => $token->name,
            'token' => $token->token,
            'domain' => $token->domain,
            'created_at' => $token->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  iterable<Token>|Collection  $tokens
     * @return array<int, array<string, mixed>>
     */
    public static function collection(iterable $tokens): array
    {
        return collect($tokens)
            ->map(fn (Token $token) => self::format($token))
            ->values()
            ->all();
    }
}

