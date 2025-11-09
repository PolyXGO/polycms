<?php

namespace Modules\Polyx\PolyFengshui\Core\Support;

use App\Support\OptionRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Modules\Polyx\PolyFengshui\Core\Helpers\Helpers;
use Modules\Polyx\PolyFengshui\Models\Token;

class TokenService
{
    protected string $group = 'polyfengshui';

    public function isActive(): bool
    {
        return (bool) OptionRepository::get('secret_token_active', false, $this->group);
    }

    public function validate(?string $authorizationHeader, ?string $currentDomain): void
    {
        if (!$this->isActive()) {
            return;
        }

        $tokens = $this->tokens();

        if (empty($tokens)) {
            throw new AuthorizationException('No valid tokens configured.');
        }

        if (empty($authorizationHeader) || !str_starts_with($authorizationHeader, 'Bearer ')) {
            throw new AuthorizationException('Missing or invalid Authorization header.');
        }

        $token = substr($authorizationHeader, 7);
        $tokenMeta = $tokens[$token] ?? null;

        if ($tokenMeta === null) {
            throw new AuthorizationException('Invalid token.');
        }

        $allowedDomain = Arr::get($tokenMeta, 'domain');

        Helpers::ensureAuthorizedDomain($allowedDomain, $currentDomain);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function tokens(): array
    {
        return Token::query()
            ->get()
            ->mapWithKeys(function (Token $token) {
                $tokenValue = $token->token;

                if (empty($tokenValue)) {
                    return [];
                }

                return [
                    $tokenValue => [
                        'id' => $token->id,
                        'name' => $token->name,
                        'token' => $tokenValue,
                        'domain' => $token->domain,
                        'created_at' => $token->created_at?->toIso8601String(),
                    ],
                ];
            })
            ->all();
    }
}

