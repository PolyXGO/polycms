<?php

namespace Modules\Polyx\PolyFengshui\GraphQL\Mutations;

use App\Support\OptionRepository;
use Illuminate\Support\Str;
use Modules\Polyx\PolyFengshui\Models\Token;
use Modules\Polyx\PolyFengshui\Support\TokenPresenter;

class TokenMutation
{
    public function create(array $args): array
    {
        $input = $args['input'] ?? [];

        $token = Token::create([
            'name' => $input['name'] ?? null,
            'domain' => $input['domain'] ?? null,
            'token' => Str::random(64),
        ]);

        return [
            'token' => TokenPresenter::format($token),
            'tokens' => TokenPresenter::collection(
                Token::query()->orderByDesc('created_at')->get()
            ),
        ];
    }

    public function delete(mixed $root, array $args): array
    {
        $id = (int) ($args['id'] ?? 0);

        if ($id > 0) {
            Token::query()->whereKey($id)->delete();
        }

        return [
            'tokens' => TokenPresenter::collection(
                Token::query()->orderByDesc('created_at')->get()
            ),
        ];
    }

    public function updateSettings(mixed $root, array $args): array
    {
        $active = (bool) ($args['active'] ?? false);

        OptionRepository::set('secret_token_active', $active, 'polyfengshui');

        return [
            'active' => $active,
        ];
    }
}

