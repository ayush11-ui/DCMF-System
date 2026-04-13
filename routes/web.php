<?php

use Illuminate\Support\Facades\Route;

// For an SPA-style app, we can just point all non-API web routes to the index view,
// and let Alpine.js or Vue handle the routing.
// Since the prompt specifies standard URL routes (e.g. /login, /register, /dashboard),
// we will serve blade templates. We can just define basic view routes.

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// API Routes handled by Web Session Guard
Route::prefix('api')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);

    Route::middleware('auth')->group(function () {
        Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('/user', [\App\Http\Controllers\Api\AuthController::class, 'user']);
        Route::get('/users', [\App\Http\Controllers\Api\AuthController::class, 'index']);

        Route::apiResource('cases', \App\Http\Controllers\Api\CaseController::class);
        
        Route::get('/hearings', [\App\Http\Controllers\Api\HearingController::class, 'index']);
        Route::post('/cases/{id}/auto-schedule', [\App\Http\Controllers\Api\HearingController::class, 'autoScheduleToggle']);

        Route::get('/notifications/unread', [\App\Http\Controllers\Api\NotificationController::class, 'unread']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);

        Route::get('/reports/stats', [\App\Http\Controllers\Api\ReportController::class, 'stats']);
    });
});

// The rest of the routes will use the main app layout.
// All state and fetching will be handled internally by Alpine.js via API.

Route::get('/dashboard', function () {
    return view('dashboard');
});
    
    Route::get('/cases', function () {
        return view('cases.index');
    });
    
    Route::get('/cases/create', function () {
        return view('cases.create');
    });
    
    Route::get('/cases/{id}', function ($id) {
        return view('cases.show', ['id' => $id]);
    });
    
    Route::get('/cases/{id}/edit', function ($id) {
        return view('cases.edit', ['id' => $id]);
    });
    
    Route::get('/hearings', function () {
        return view('hearings.calendar');
    });
    
    Route::get('/notifications', function () {
        return view('notifications.index');
    });
    
    Route::get('/reports', function () {
        return view('reports.index');
    });
