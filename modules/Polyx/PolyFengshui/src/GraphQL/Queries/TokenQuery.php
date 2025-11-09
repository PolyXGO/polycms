<?php

namespace Modules\Polyx\PolyFengshui\GraphQL\Queries;

use App\Support\OptionRepository;
use Modules\Polyx\PolyFengshui\Models\Token;
use Modules\Polyx\PolyFengshui\Support\TokenPresenter;

class TokenQuery
{
    public function tokens(): array
    {
        return TokenPresenter::collection(
            Token::query()
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function settings(): array
    {
        return [
            'active' => (bool) OptionRepository::get('secret_token_active', false, 'polyfengshui'),
        ];
    }
}

