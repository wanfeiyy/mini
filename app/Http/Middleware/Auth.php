<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-21
 * Time: 20:27
 */

namespace App\Http\Middleware;

use App\Http\Response;
use App\Services\PassportService;

class Auth
{
    private $passportService;

    public function __construct(PassportService $passportService)
    {
        $this->passportService = $passportService;
    }

    public function handle($request, \Closure $next, $isAdmin = false)
    {
        $this->passportService->checkLogin();
        if ($isAdmin) {
            if ($this->passportService->getIsAdmin()) {
                return $next($request);
            } else {
                Response::throwError(Response::ACCOUNT_ERROR);
            }
        }

        return $next($request);

    }
}