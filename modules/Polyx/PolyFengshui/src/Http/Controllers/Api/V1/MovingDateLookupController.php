<?php

namespace Modules\Polyx\PolyFengshui\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Polyx\PolyFengshui\Core\Services\FengshuiService;
use Modules\Polyx\PolyFengshui\Core\Support\TokenService;
use Modules\Polyx\PolyFengshui\Http\Controllers\Controller;
use RuntimeException;

class MovingDateLookupController extends Controller
{
    public function __construct(
        TokenService $tokenService,
        FengshuiService $service
    ) {
        parent::__construct($tokenService);
        $this->service = $service;
    }

    protected FengshuiService $service;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeRequest($request);

        $payload = $this->validateRequest($request);

        $checkedYear = $payload['checked_year'];
        $targetYear = $payload['target_year'] ?? '';
        $targetMonth = $payload['target_month'] ?? '';
        $targetDay = $payload['target_day'] ?? '01';

        try {
            $result = $this->service->getMovingDateLookup(
                $checkedYear,
                $targetYear ? (int) $targetYear : null,
                $targetMonth ? (int) $targetMonth : null,
                $targetDay ? (int) $targetDay : null
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'checked_year' => $exception->getMessage(),
            ]);
        }

        return response()->json($result);
    }

    /**
     * @return array<string, int|string>
     */
    protected function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'checked_year' => ['required', 'integer', 'between:1000,2199'],
            'target_year' => ['nullable', 'integer', 'between:1000,2199'],
            'target_month' => ['nullable', 'integer', 'between:1,12'],
            'target_day' => ['nullable', 'integer', 'between:1,31'],
        ]);

        return $validated;
    }
}

