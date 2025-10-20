<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Resources\EmployeeIndexResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $result = Employee::query()
            ->with(['manager'])
            ->currentUser()
            ->simplePaginate(15);

        return EmployeeIndexResource::collection($result);
    }

    public function store(EmployeeCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['manager_id'] = $request->user()->id;
        $employee = Employee::create($data);

        return response()->json($employee, ResponseAlias::HTTP_CREATED);
    }

    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        if ($employee->manager_id !== $request->user()->id) {
            return response()->json(null, ResponseAlias::HTTP_NOT_FOUND);
        }

        $data = $request->validated();
        $employee->update($data);

        return response()->json($employee->only(array_keys($data)), ResponseAlias::HTTP_OK);
    }
}
