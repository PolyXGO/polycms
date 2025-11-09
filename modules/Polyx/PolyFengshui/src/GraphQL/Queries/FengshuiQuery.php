<?php

namespace Modules\Polyx\PolyFengshui\GraphQL\Queries;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Polyx\PolyFengshui\Core\Services\FengshuiService;
use Modules\Polyx\PolyFengshui\Core\Support\TokenService;
use RuntimeException;

class FengshuiQuery
{
    public function __construct(
        protected FengshuiService $service,
        protected TokenService $tokenService,
        protected Request $request
    ) {
    }

    public function date(mixed $rootValue, array $args): array
    {
        $this->authorize();

        $years = $this->parseYears($args['years'] ?? []);

        if (empty($years)) {
            throw ValidationException::withMessages([
                'years' => 'Provide at least one year between 1000 and 2199.',
            ]);
        }

        return $this->service->getDateInsights($years);
    }

    public function movingDate(mixed $rootValue, array $args): array
    {
        $this->authorize();

        $date = $args['date'] ?? null;

        if ($date !== null && !$this->isValidDate($date)) {
            throw ValidationException::withMessages([
                'date' => 'Invalid date format. Expected YYYY-MM-DD.',
            ]);
        }

        $result = $this->service->getMovingDate($date);

        if (isset($result['error'])) {
            throw ValidationException::withMessages([
                'date' => $result['error'],
            ]);
        }

        return $result;
    }

    public function movingDateLookup(mixed $rootValue, array $args): array
    {
        $this->authorize();

        $checkedYear = $args['checked_year'] ?? null;
        $targetYear = $args['target_year'] ?? null;
        $targetMonth = $args['target_month'] ?? null;
        $targetDay = $args['target_day'] ?? null;

        $this->validateLookupArgs($checkedYear, $targetYear, $targetMonth, $targetDay);

        try {
            return $this->service->getMovingDateLookup(
                (int) $checkedYear,
                $targetYear !== null ? (int) $targetYear : null,
                $targetMonth !== null ? (int) $targetMonth : null,
                $targetDay !== null ? (int) $targetDay : null
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'checked_year' => $exception->getMessage(),
            ]);
        }
    }

    protected function authorize(): void
    {
        $this->tokenService->validate(
            $this->request->header('Authorization'),
            $this->request->getHost()
        );
    }

    /**
     * @param  mixed  $input
     * @return array<int, int>
     */
    protected function parseYears(mixed $input): array
    {
        if ($input === null) {
            return [];
        }

        $values = is_array($input) ? $input : [$input];

        $years = [];

        foreach ($values as $value) {
            $year = (int) $value;

            if ($year >= 1000 && $year <= 2199) {
                $years[] = $year;
            }
        }

        return array_values(array_unique($years));
    }

    protected function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));

        return checkdate($month, $day, $year);
    }

    protected function validateLookupArgs(mixed $checkedYear, mixed $targetYear, mixed $targetMonth, mixed $targetDay): void
    {
        $errors = [];

        if ($checkedYear === null || (int) $checkedYear < 1000 || (int) $checkedYear > 2199) {
            $errors['checked_year'] = 'checked_year must be between 1000 and 2199.';
        }

        if ($targetYear !== null && ((int) $targetYear < 1000 || (int) $targetYear > 2199)) {
            $errors['target_year'] = 'target_year must be between 1000 and 2199.';
        }

        if ($targetMonth !== null && ((int) $targetMonth < 1 || (int) $targetMonth > 12)) {
            $errors['target_month'] = 'target_month must be between 1 and 12.';
        }

        if ($targetDay !== null && ((int) $targetDay < 1 || (int) $targetDay > 31)) {
            $errors['target_day'] = 'target_day must be between 1 and 31.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}

