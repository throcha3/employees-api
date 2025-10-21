<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [LoginController::class, 'me']);

    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employee.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employee.store');
    Route::post('/employees/upload-csv', [EmployeeController::class, 'uploadCsv'])->name('employee.uploadCsv');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employee.show');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
});
