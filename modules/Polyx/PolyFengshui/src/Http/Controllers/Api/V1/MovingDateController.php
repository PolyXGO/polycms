<?php

namespace Modules\Polyx\PolyFengshui\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Polyx\PolyFengshui\Core\Services\FengshuiService;
use Modules\Polyx\PolyFengshui\Core\Support\TokenService;
use Modules\Polyx\PolyFengshui\Http\Controllers\Controller;

class MovingDateController extends Controller
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

        $date = $request->input('date');

        if ($date !== null && !$this->isValidDate($date)) {
            throw ValidationException::withMessages([
                'date' => 'Invalid date format. Expected YYYY-MM-DD.',
            ]);
        }

        $data = $this->service->getMovingDate($date);

        if (isset($data['error'])) {
            throw ValidationException::withMessages([
                'date' => $data['error'],
            ]);
        }

        return response()->json($data);
    }

    protected function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));

        return checkdate($month, $day, $year);
    }
}

