<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevelopmentPlanController;
use App\Http\Controllers\EvaluationBuilderController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\JefeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\EvaluationTemplateController;
use App\Http\Controllers\Admin\PositionTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {

    // Password change (must be before other routes so middleware can redirect here)
    Route::get('/password/change',  [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Jefe — Mi equipo (requires jefe_area or director_rh role)
    Route::middleware('role:jefe_area,director_rh')->get('/mi-equipo', [JefeController::class, 'team'])->name('jefe.team');

    // Evaluations – all authenticated users
    Route::get('/evaluations',         [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/pdf', [EvaluationController::class, 'exportPdf'])->name('evaluations.export-pdf');
    Route::get('/evaluations/{evaluation}/live', [EvaluationController::class, 'liveState'])->name('evaluations.live-state');
    Route::post('/evaluations/{evaluation}/responses',    [EvaluationController::class, 'saveResponses'])->name('evaluations.save-responses');
    Route::post('/evaluations/{evaluation}/observations', [EvaluationController::class, 'saveObservations'])->name('evaluations.save-observations');

    // AJAX individual score save (all auth users)
    Route::patch('/evaluations/{evaluation}/score/{criteria}', [EvaluationBuilderController::class, 'saveScore'])->name('evaluations.score.save');

    // Dynamic builder routes (authorized in controller: RH/Gerencia/Presidencia/Admin)
    Route::post('/evaluations/{evaluation}/builder/reorder', [EvaluationBuilderController::class, 'reorderSections'])->name('evaluations.builder.reorder');
    Route::post('/evaluations/{evaluation}/builder/toggle/{section}', [EvaluationBuilderController::class, 'toggleSection'])->name('evaluations.builder.toggle');
    Route::post('/evaluations/{evaluation}/builder/add-section', [EvaluationBuilderController::class, 'addSection'])->name('evaluations.builder.add-section');
    Route::post('/evaluations/{evaluation}/builder/add-criteria/{section}', [EvaluationBuilderController::class, 'addCriteria'])->name('evaluations.builder.add-criteria');
    Route::delete('/evaluations/{evaluation}/builder/criteria/{criteria}', [EvaluationBuilderController::class, 'removeCriteria'])->name('evaluations.builder.remove-criteria');
    Route::put('/evaluations/{evaluation}/builder/section/{section}', [EvaluationBuilderController::class, 'updateSectionInline'])->name('evaluations.builder.update-section');
    Route::put('/evaluations/{evaluation}/builder/criteria/{criteria}', [EvaluationBuilderController::class, 'updateCriteriaInline'])->name('evaluations.builder.update-criteria');

    // Create evaluations (authorized in controller: RH/Gerencia/Presidencia/Admin)
    Route::get('/evaluations-create',                  [EvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/evaluations',                        [EvaluationController::class, 'store'])->name('evaluations.store');
    Route::post('/evaluations/{evaluation}/complete',  [EvaluationController::class, 'complete'])->name('evaluations.complete');
    Route::post('/evaluations/{evaluation}/reopen',    [EvaluationController::class, 'reopen'])->name('evaluations.reopen');
    Route::patch('/evaluations/bulk-reset',            [EvaluationController::class, 'resetBulk'])->name('evaluations.bulk-reset');

    // Notifications
    Route::get('/notifications',                       [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count',          [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{notification}/read',  [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read',        [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Development plan
    Route::post('/evaluations/{evaluation}/development-plan',  [DevelopmentPlanController::class, 'store'])->name('development-plans.store');
    Route::put('/development-plan/{plan}',                     [DevelopmentPlanController::class, 'update'])->name('development-plans.update');
    Route::delete('/development-plan/{plan}',                  [DevelopmentPlanController::class, 'destroy'])->name('development-plans.destroy');

    // Templates (authorized in controller: RH/Gerencia/Presidencia/Admin)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/templates',                  [EvaluationTemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates',                 [EvaluationTemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}/edit',  [EvaluationTemplateController::class, 'edit'])->name('templates.edit');
        Route::put('/templates/{template}',       [EvaluationTemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}',    [EvaluationTemplateController::class, 'destroy'])->name('templates.destroy');

        Route::post('/templates/{template}/reorder-sections', [EvaluationTemplateController::class, 'reorderSections'])->name('templates.reorder-sections');
        Route::post('/sections/{section}/reorder-criteria',   [EvaluationTemplateController::class, 'reorderCriteria'])->name('sections.reorder-criteria');
        Route::post('/templates/{template}/sections',         [EvaluationTemplateController::class, 'storeSection'])->name('templates.sections.store');
        Route::put('/sections/{section}',                     [EvaluationTemplateController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{section}',                  [EvaluationTemplateController::class, 'destroySection'])->name('sections.destroy');

        Route::post('/sections/{section}/criteria', [EvaluationTemplateController::class, 'storeCriteria'])->name('sections.criteria.store');
        Route::put('/criteria/{criteria}',          [EvaluationTemplateController::class, 'updateCriteria'])->name('criteria.update');
        Route::delete('/criteria/{criteria}',       [EvaluationTemplateController::class, 'destroyCriteria'])->name('criteria.destroy');

        Route::post('/templates/{template}/ranges', [EvaluationTemplateController::class, 'storeRange'])->name('templates.ranges.store');
        Route::put('/ranges/{range}',               [EvaluationTemplateController::class, 'updateRange'])->name('ranges.update');
        Route::delete('/ranges/{range}',            [EvaluationTemplateController::class, 'destroyRange'])->name('ranges.destroy');
    });

    // Reports (admin only)
    Route::middleware('role:director_rh')->get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    // Admin routes
    Route::middleware('role:director_rh')->prefix('admin')->name('admin.')->group(function () {

        // Jefes overview (Salomón)
        Route::get('/jefes', [JefeController::class, 'overview'])->name('jefes.overview');

        Route::get('/areas',          [AreaController::class, 'index'])->name('areas.index');
        Route::post('/areas',         [AreaController::class, 'store'])->name('areas.store');
        Route::put('/areas/{area}',   [AreaController::class, 'update'])->name('areas.update');
        Route::delete('/areas/{area}',[AreaController::class, 'destroy'])->name('areas.destroy');

        Route::get('/position-types',                  [PositionTypeController::class, 'index'])->name('position-types.index');
        Route::post('/position-types',                 [PositionTypeController::class, 'store'])->name('position-types.store');
        Route::put('/position-types/{positionType}',   [PositionTypeController::class, 'update'])->name('position-types.update');
        Route::delete('/position-types/{positionType}',[PositionTypeController::class, 'destroy'])->name('position-types.destroy');

        Route::get('/employees',                       [UserController::class, 'index'])->name('employees.index');
        Route::post('/employees/import',               [UserController::class, 'importEmployees'])->name('employees.import');
        Route::get('/api/areas/{area}/position-types', [UserController::class, 'getPositionTypes'])->name('api.position-types');

        Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');


    });
});
