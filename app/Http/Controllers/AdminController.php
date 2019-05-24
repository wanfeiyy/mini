<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-21
 * Time: 20:06
 */

namespace App\Http\Controllers;


use App\Http\Response;
use App\Services\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }


    public function getUserList(Request $request)
    {
        $data = $this->adminService->getUserList(
            ['role' => ['>=', 0]],
            intval($request->input('start', 0)),
            intval($request->input('limit', 20))
        );

        return Response::success($data);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateRole(Request $request)
    {
        $ret = $this->adminService->updateUserRole(
            $request->input('userId'),
            $request->input('role')
        );

        return Response::success($ret);
    }
}