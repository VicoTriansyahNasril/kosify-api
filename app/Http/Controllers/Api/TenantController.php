<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\BoardingHouse;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(BoardingHouse $boardingHouse)
    {
        if ($boardingHouse->user_id !== auth()->id())
            abort(403);

        $tenants = Tenant::whereHas('room', function ($q) use ($boardingHouse) {
            $q->where('boarding_house_id', $boardingHouse->id);
        })->with('room')->latest()->get();

        return TenantResource::collection($tenants);
    }

    public function store(StoreTenantRequest $request)
    {
        $tenant = $this->tenantService->checkIn($request->validated());

        return new TenantResource($tenant);
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        if ($tenant->room->boardingHouse->user_id !== auth()->id())
            abort(403);

        $this->tenantService->checkOut($tenant);

        return response()->json(['message' => 'Tenant checked out successfully']);
    }
}