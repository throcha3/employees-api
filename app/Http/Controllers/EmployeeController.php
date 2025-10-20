<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Resources\EmployeeIndexResource;
use App\Models\Employee;
use Illuminate\Http\Request;

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
}
