<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DepartmentResource::collection(Department::query()->orderBy('name')->get());
    }

    public function store(StoreDepartmentRequest $request): DepartmentResource
    {
        Gate::authorize('manage_users');

        $department = Department::create($request->validated());

        return new DepartmentResource($department);
    }

    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }

    public function update(StoreDepartmentRequest $request, Department $department): DepartmentResource
    {
        Gate::authorize('manage_users');

        $department->update($request->validated());

        return new DepartmentResource($department);
    }

    public function destroy(Department $department)
    {
        Gate::authorize('manage_users');

        $department->delete();

        return response()->json(status: 204);
    }
}
