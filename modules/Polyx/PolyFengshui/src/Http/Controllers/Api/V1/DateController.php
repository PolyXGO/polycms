<?php

namespace Modules\Polyx\PolyFengshui\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Polyx\PolyFengshui\Core\Services\FengshuiService;
use Modules\Polyx\PolyFengshui\Core\Support\TokenService;
use Modules\Polyx\PolyFengshui\Http\Controllers\Controller;

class DateController extends Controller
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

        $years = $this->parseYears($request->input('years'));

        if (empty($years)) {
            throw ValidationException::withMessages([
                'years' => 'The years parameter must include at least one year between 1000 and 2199.',
            ]);
        }

        return response()->json($this->service->getDateInsights($years));
    }

    /**
     * @return array<int, int>
     */
    protected function parseYears(mixed $input): array
    {
        if ($input === null) {
            return [];
        }

        if (is_array($input)) {
            $values = $input;
        } else {
            $values = explode(',', (string) $input);
        }

        $years = [];

        foreach ($values as $value) {
            $year = (int) trim((string) $value);

            if ($year >= 1000 && $year <= 2199) {
                $years[] = $year;
            }
        }

        return array_values(array_unique($years));
    }
}

