<?php
/**
 * Created by PhpStorm.
 * User: wanfeiyy
 * Date: 2019-05-24
 * Time: 10:25
 */

namespace App\Http\Controllers;

use App\Http\Requests\SchedulingRequest;
use App\Http\Response;
use App\Services\PassportService;
use App\Services\VehicleSchedulingService;

class SchedulingController extends Controller
{
    private $service;

    private $passportService;

    public function __construct(VehicleSchedulingService $service, PassportService $passportService)
    {
        $this->service = $service;
        $this->passportService = $passportService;
    }

    public function create(SchedulingRequest $request)
    {
        return Response::success($this->service->create($this->getUserId(), $request->all()));
    }

    
    public function paginate(SchedulingRequest $request)
    {
        $list = $this->service->getList(
            $this->getUserId(),
            $this->getUserRole(),
            $request->input('start', 0),
            $request->input('limit', 20)
        );

        return Response::success($list);
    }


    public function show(SchedulingRequest $request)
    {
        $detail = $this->service->getDetail($request->input('id'), $this->getUserId(), $this->getUserRole());
        return Response::success($detail);
    }

    
    public function check(SchedulingRequest $request)
    {
        return Response::success(
            $this->service->check(
                $request->input('id'),
                $request->input('state'),
                $request->input('safetyAccounting', ''),
                $request->input('opinion', ''),
                [
                    'name' => $this->getUserName(),
                    'role' => $this->getUserRole(),
                    'userId' => $this->getUserId(),
                ]
            )
        );
    }


    public function scheduling(SchedulingRequest $request)
    {
        return Response::success(
            $this->service->scheduling(
                $request->input('id'),
                $request->input('state'),
                $request->input('driver', ''),
                $request->input('numberPlates', ''),
                $request->input('remarks', ''),
                [
                    'name' => $this->getUserName(),
                    'role' => $this->getUserRole(),
                    'userId' => $this->getUserId(),
                ]
            )
        );
    }
    
    private function getUserId()
    {
        return $this->passportService->getCurrentUserId();
    }


    private function getUserName()
    {
        return $this->passportService->getUserInfo()['name'] ?? 0;
    }


    private function getUserRole()
    {
        return $this->passportService->getUserInfo()['role'] ?? 0;
    }
}