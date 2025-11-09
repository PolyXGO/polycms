<?php

namespace Modules\Polyx\PolyFengshui\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Polyx\PolyFengshui\Models\Token;
use Modules\Polyx\PolyFengshui\Support\TokenPresenter;

class TokenController extends Controller
{
    protected string $group = 'polyfengshui';

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'active' => $this->isTokenProtectionActive(),
            'tokens' => $this->resourceTokens(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
        ]);

        $token = Token::create([
            'name' => $data['name'] ?? null,
            'domain' => $data['domain'] ?? null,
            'token' => Str::random(64),
        ]);

        return response()->json([
            'message' => 'Token created successfully.',
            'token' => TokenPresenter::format($token),
            'tokens' => $this->resourceTokens(),
        ], 201);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        Token::query()->whereKey($tokenId)->delete();

        return response()->json([
            'message' => 'Token deleted successfully.',
            'tokens' => $this->resourceTokens(),
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        OptionRepository::set('secret_token_active', $data['active'], $this->group);

        return response()->json([
            'message' => 'Token protection setting updated.',
            'active' => $data['active'],
        ]);
    }

    protected function isTokenProtectionActive(): bool
    {
        return (bool) OptionRepository::get('secret_token_active', false, $this->group);
    }

    protected function resourceTokens(): array
    {
        return TokenPresenter::collection(
            Token::query()->orderByDesc('created_at')->get()
        );
    }
}
