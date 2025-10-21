<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Requests\UploadCsvRequest;
use App\Models\User as AppUser;
use App\Services\EmployeeService;
use App\Http\Resources\EmployeeIndexResource;
use App\Http\Resources\EmployeeShowResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @group Employees
 * @authenticated
 *
 * APIs for managing employees
 */

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $page = $request->get('page', 1);
        $cacheKey = "employees_index_user_{$userId}_page_{$page}";

        $result = Cache::remember($cacheKey, 300, function () use ($request) {
            return Employee::query()
                ->with(['manager'])
                ->currentUser()
                ->simplePaginate(15);
        });

        return EmployeeIndexResource::collection($result);
    }

    public function store(EmployeeCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['manager_id'] = $request->user()->id;
        $employee = Employee::create($data);

        $this->invalidateUserEmployeeCache($request->user()->id);

        return response()->json($employee, ResponseAlias::HTTP_CREATED);
    }

    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        if ($employee->manager_id !== $request->user()->id) {
            return response()->json(null, ResponseAlias::HTTP_NOT_FOUND);
        }

        $data = $request->validated();
        $employee->update($data);

        $this->invalidateUserEmployeeCache($request->user()->id);

        return response()->json($employee->only(array_keys($data)), ResponseAlias::HTTP_OK);
    }

    public function show(Request $request, Employee $employee)
    {
        if ($employee->manager_id !== $request->user()->id) {
            return response()->json(null, ResponseAlias::HTTP_NOT_FOUND);
        }

        $userId = $request->user()->id;
        $cacheKey = "employee_show_user_{$userId}_employee_{$employee->id}";

        $result = Cache::remember($cacheKey, 300, function () use ($employee) {
            return new EmployeeShowResource($employee);
        });

        return $result;
    }

    public function destroy(Request $request, Employee $employee)
    {
        if ($employee->manager_id !== $request->user()->id) {
            return response()->json(null, ResponseAlias::HTTP_NOT_FOUND);
        }
        $employee->delete();

        $this->invalidateUserEmployeeCache($request->user()->id);

        return response()->noContent();
    }

    public function uploadCsv(UploadCsvRequest $request, EmployeeService $service): JsonResponse
    {
        $file = $request->file('file');
        $manager = auth()->user();

        $service->createEmployeesByCsv($file, $manager);

        $this->invalidateUserEmployeeCache($manager->id);

        return response()->json(['message' => 'Batch dispatched'], ResponseAlias::HTTP_ACCEPTED);
    }

    /**
     * Invalidate all employee-related cache for a specific user
     *
     * @param int $userId
     * @return void
     */
    private function invalidateUserEmployeeCache(int $userId): void
    {
        $pattern = "employees_index_user_{$userId}_*";
        $this->invalidateCacheByPattern($pattern);

        $pattern = "employee_show_user_{$userId}_*";
        $this->invalidateCacheByPattern($pattern);
    }

    private function invalidateCacheByPattern(string $pattern): void
    {
        if (config('cache.default') !== 'redis') {
            return;
        }

        try {
            $keys = Cache::store('redis')->getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::store('redis')->getRedis()->del($keys);
            }
        } catch (\Exception $e) {
            // Redis not available, skip cache invalidation
        }
    }

}
