<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestTypeRequest;
use App\Http\Resources\RequestTypeResource;
use App\Models\RequestType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class RequestTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RequestTypeResource::collection(RequestType::query()->orderBy('name')->get());
    }

    public function store(StoreRequestTypeRequest $request): RequestTypeResource
    {
        Gate::authorize('manage_workflows');

        $requestType = RequestType::create($request->validated());

        return new RequestTypeResource($requestType);
    }

    public function show(RequestType $requestType): RequestTypeResource
    {
        return new RequestTypeResource($requestType);
    }

    public function update(StoreRequestTypeRequest $request, RequestType $requestType): RequestTypeResource
    {
        Gate::authorize('manage_workflows');

        $requestType->update($request->validated());

        return new RequestTypeResource($requestType);
    }

    public function destroy(RequestType $requestType)
    {
        Gate::authorize('manage_workflows');

        $requestType->delete();

        return response()->json(status: 204);
    }
}
