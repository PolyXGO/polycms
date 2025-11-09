<?php

namespace Modules\Polyx\PolyFengshui\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Modules\Polyx\PolyFengshui\Core\Support\TokenService;

abstract class Controller extends BaseController
{
    public function __construct(
        protected TokenService $tokenService
    ) {
        //
    }

    protected function authorizeRequest(Request $request): void
    {
        $this->tokenService->validate(
            $request->header('Authorization'),
            $request->getHost()
        );
    }
}

