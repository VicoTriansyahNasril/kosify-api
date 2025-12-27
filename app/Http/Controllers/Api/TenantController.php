<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\BoardingHouse;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request, BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $query = Tenant::whereHas('room', function ($q) use ($boardingHouse) {
            $q->where('boarding_house_id', $boardingHouse->id);
        })->with('room');

        if ($search = $request->input('q')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return TenantResource::collection($query->latest()->get());
    }

    public function store(StoreTenantRequest $request)
    {
        $tenant = $this->tenantService->checkIn($request->validated());
        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);
        $tenant->update($request->validated());
        return new TenantResource($tenant);
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        $this->authorize('delete', $tenant);
        $this->tenantService->checkOut($tenant);
        return response()->json(['message' => 'Tenant checked out successfully']);
    }
}