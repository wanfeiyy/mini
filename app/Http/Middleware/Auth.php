<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-21
 * Time: 20:27
 */

namespace App\Http\Middleware;

use App\Http\Response;
use App\Services\AdminService;
use App\Services\PassportService;

class Auth
{
    private $passportService;

    protected $adminService;

    public function __construct(PassportService $passportService, AdminService $adminService)
    {
        $this->passportService = $passportService;
        $this->adminService = $adminService;
    }

    public function handle($request, \Closure $next, $isAdmin = false)
    {
        $this->passportService->checkLogin();
        if ($isAdmin) {
            $adminSess = $request->input('adminSess');
            if ($this->adminService->checkSess($adminSess)) {
                return $next($request);
            } else {
                Response::throwError(Response::ACCOUNT_ERROR);
            }
        }

        return $next($request);

    }
}