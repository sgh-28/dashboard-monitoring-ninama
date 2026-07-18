<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TestIntegrationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminProjectController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MarketingOfferController;
use App\Http\Controllers\AdminMarketingController;
use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\EmployeeTaskController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('throttle:5,1');

Route::get('/home', function () {
    return redirect()->route('main.dashboard');
})->middleware('auth');

// ==================== GOOGLE CALENDAR OAUTH ROUTES (Harus Login Dulu) ====================
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// ==================== TEST INTEGRATION ====================
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::get('/test-integration', [TestIntegrationController::class, 'testAll'])
        ->name('test.integration');
});

// ==================== PROTECTED ROUTES ====================
Route::middleware(['auth'])->group(function () {

    Route::get('/realtime/version', function () {
        $trackedTables = [
            'projects',
            'project_tasks',
            'project_divisions',
            'project_phases',
            'marketing_offers',
            'marketing_offer_histories',
            'users',
        ];

        $versions = [];

        foreach ($trackedTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $versions[$table] = [
                'count' => DB::table($table)->count(),
                'updated_at' => DB::table($table)->max('updated_at'),
            ];
        }

        return response()->json([
            'version' => hash('sha256', json_encode($versions)),
        ]);
    })->name('realtime.version');
    
    // Dashboard Utama (Untuk Pegawai, Marketing, Customer)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('main.dashboard');
    
    // Detail Proyek dengan Timeline & SLA
    Route::get('/projects/{project}/detail', [ProjectController::class, 'showDetail'])
        ->name('projects.detail')
        ->middleware(['role:super_admin,direktur']);
    
    // ✅ EXPORT PROJECTS (Direktur/Super Admin)
    Route::middleware(['role:super_admin,direktur'])->group(function () {
        Route::get('/projects/category/{category}/export', [ProjectController::class, 'exportProjects'])
            ->name('projects.export');
        Route::get('/projects/export', [ProjectController::class, 'exportProjects'])
            ->name('projects.export.all');
    });
    
    // Detail Kategori
    Route::middleware(['role:super_admin,direktur'])->group(function () {
        Route::get('/projects/category/{category}', [ProjectController::class, 'categoryDetail'])
            ->name('projects.category.detail')
            ->where('category', 'web|internet|cctv');
    });
    
    // ==================== MARKETING ROUTES (Penawaran) ====================
    Route::middleware(['role:marketing'])->prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/', [MarketingOfferController::class, 'index'])->name('index');
        Route::get('/create', [MarketingOfferController::class, 'create'])->name('create');
        Route::post('/', [MarketingOfferController::class, 'store'])->name('store');
        Route::get('/{offer}/edit', [MarketingOfferController::class, 'edit'])->name('edit');
        Route::put('/{offer}', [MarketingOfferController::class, 'update'])->name('update');
        Route::delete('/{offer}', [MarketingOfferController::class, 'destroy'])->name('destroy');
    });
    
    // ==================== PROJECT ROUTES (UMUM) ====================
    Route::prefix('user/projects')->name('user.projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
    });
    
    // ==================== TASK MANAGEMENT ROUTES ====================
    
    // Admin Task Management (Super Admin Only)
    Route::middleware(['role:super_admin'])->prefix('admin/tasks')->name('admin.tasks.')->group(function () {
        Route::get('/', [AdminTaskController::class, 'index'])->name('index');
        Route::get('/create', [AdminTaskController::class, 'create'])->name('create');
        Route::post('/', [AdminTaskController::class, 'store'])->name('store');
        Route::get('/{task}/edit', [AdminTaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [AdminTaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [AdminTaskController::class, 'destroy'])->name('destroy');
        
        // Task management per proyek spesifik
        Route::get('/project/{project_id}', [AdminTaskController::class, 'indexByProject'])
            ->name('index.by.project');
    });
    
    // Employee/Marketing Task Management (For assigned users only)
    Route::middleware(['role:pegawai,marketing'])->prefix('my-tasks')->name('employee.tasks.')->group(function () {
        Route::get('/', [EmployeeTaskController::class, 'index'])->name('index');
        Route::get('/projects/{project}', [EmployeeTaskController::class, 'showManagedProject'])->name('projects.show');
        Route::post('/projects/{project}/complete', [EmployeeTaskController::class, 'completeProject'])->name('projects.complete');
        Route::post('/tasks/{task}/approve', [EmployeeTaskController::class, 'approveTask'])->name('tasks.approve');
        Route::get('/{task}', [EmployeeTaskController::class, 'show'])->name('show');
        Route::get('/{task}/submit', [EmployeeTaskController::class, 'submitForm'])->name('submit.form');
        Route::post('/{task}/submit', [EmployeeTaskController::class, 'submit'])->name('submit');
    });
    
    // ==================== EMPLOYEE ROUTES ====================
    Route::middleware(['role:pegawai'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'index'])->name('dashboard');
        Route::get('/projects', [EmployeeController::class, 'projects'])->name('projects');
    });
    
    // ==================== CUSTOMER ROUTES ====================
    Route::middleware(['role:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'index'])->name('dashboard');
        Route::get('/projects', [CustomerController::class, 'projects'])->name('projects');
        Route::get('/category/{category}', [CustomerController::class, 'show'])->name('category');
    });
    
    // ==================== ADMIN ROUTES ====================
    Route::middleware(['role:super_admin,direktur'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');
        
        // Route AJAX untuk divisi dinamis
        Route::get('/projects/divisions/{category}', [AdminProjectController::class, 'getDivisionsByCategory'])
            ->name('admin.projects.divisions');
        
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::get('/', [AdminMarketingController::class, 'index'])->name('index');
            Route::get('/export', [AdminMarketingController::class, 'exportMarketing'])->name('export');
            Route::get('/{category}/export', [AdminMarketingController::class, 'exportMarketing'])->name('export.category');
            Route::get('/{offer}', [AdminMarketingController::class, 'show'])->name('show');
        });
        
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [AdminProjectController::class, 'index'])->name('index');
            Route::get('/create', [AdminProjectController::class, 'create'])->name('create');
            Route::post('/', [AdminProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [AdminProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [AdminProjectController::class, 'edit'])->name('edit');
            Route::put('/{project}', [AdminProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [AdminProjectController::class, 'destroy'])->name('destroy');
            Route::get('/{project}/manage', [AdminProjectController::class, 'manage'])->name('manage');
        });
        
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [AdminCustomerController::class, 'index'])->name('index');
            Route::get('/create', [AdminCustomerController::class, 'create'])->name('create');
            Route::post('/', [AdminCustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [AdminCustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [AdminCustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [AdminCustomerController::class, 'destroy'])->name('destroy');
        });
        
        // ✅ ROUTE KELOLA AKUN PEGAWAI (Super Admin Only)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });
        
        Route::get('/offers', [AdminProjectController::class, 'offers'])->name('offers');
        
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/general', function () { return view('admin.dashboard'); })->name('general');
            Route::get('/notifications', function () { return view('admin.dashboard'); })->name('notifications');
            Route::get('/integrations', function () { return view('admin.dashboard'); })->name('integrations');
        });
    });
    
    // ==================== DIREKTUR SPECIFIC ROUTES ====================
    Route::middleware(['role:direktur'])->prefix('direktur')->name('direktur.')->group(function () {
        // ✅ Menggunakan method indexDirector agar view-nya beda (ada tombol export)
        Route::get('/dashboard', [DashboardController::class, 'indexDirector'])->name('dashboard');
        Route::get('/marketing/{offer}', [DashboardController::class, 'showDirectorMarketingOffer'])->name('marketing.show');
        
        Route::get('/reports', function () {
            return view('direktur.dashboard'); 
        })->name('reports');
        
        // ✅ EXPORT LAPORAN LENGKAP (SEMUA BIDANG)
        Route::get('/export/all', function() {
            return Excel::download(
                new \App\Exports\DirectorReportExport(), 
                'laporan-lengkap-direktur-'.date('Y-m-d').'.xlsx',
                null,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        })->name('export.all');
        
    }); // ✅ Tutup group direktur
    
}); // ✅ Tutup group auth (PROTECTED ROUTES)

// ==================== FALLBACK ROUTE ====================
Route::fallback(function () {
    return redirect()->route('home')->with('error', 'Halaman tidak ditemukan.');
});
