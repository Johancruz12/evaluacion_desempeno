<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevelopmentPlanController;
use App\Http\Controllers\EvaluationBuilderController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\JefeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\EvaluationTemplateController;
use App\Http\Controllers\Admin\PositionTypeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PeriodController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // "Olvidé mi contraseña" — flujo con código OTP (cédula + teléfono → código por correo → nueva contraseña)
    Route::get('/password/forgot',           [PasswordResetController::class, 'showRequest'])->name('password.otp.request');
    Route::post('/password/forgot',          [PasswordResetController::class, 'sendCode'])->name('password.otp.send');
    Route::get('/password/verify',           [PasswordResetController::class, 'showVerify'])->name('password.otp.verify.show');
    Route::post('/password/verify',          [PasswordResetController::class, 'verifyCode'])->name('password.otp.verify');
    Route::get('/password/reset',            [PasswordResetController::class, 'showReset'])->name('password.otp.reset.show');
    Route::post('/password/reset',           [PasswordResetController::class, 'reset'])->name('password.otp.reset');

    // Alias para compatibilidad con vistas que aún apuntan al nombre antiguo
    Route::get('/password/forgot-legacy', fn() => redirect()->route('password.otp.request'))->name('password.forgot');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {

    // Password change (must be before other routes so middleware can redirect here)
    Route::get('/password/change',  [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');

    // Cambio voluntario con OTP (usuario logueado): teléfono → código → cambio
    Route::get('/password/change/otp',         [AuthController::class, 'showChangeOtpRequest'])->name('password.otp.change.request');
    Route::post('/password/change/otp',        [AuthController::class, 'sendChangeOtp'])->name('password.otp.change.send');
    Route::get('/password/change/otp/verify',  [AuthController::class, 'showChangeOtpVerify'])->name('password.otp.change.verify');
    Route::post('/password/change/otp/verify', [AuthController::class, 'verifyChangeOtp'])->name('password.otp.change.confirm');

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
    Route::post('/evaluations/{evaluation}/submit-evaluator', [EvaluationController::class, 'submitEvaluator'])->name('evaluations.submit-evaluator');

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
    Route::get('/evaluations/preview',                 [EvaluationController::class, 'preview'])->name('evaluations.preview');

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

        Route::post('/templates/{template}/cleanup-participants', [EvaluationTemplateController::class, 'cleanupParticipants'])->name('templates.cleanup-participants');
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

        // Section types (catalog)
        Route::get('/section-types',                       [\App\Http\Controllers\Admin\SectionTypeController::class, 'index'])->name('section-types.index');
        Route::post('/section-types',                      [\App\Http\Controllers\Admin\SectionTypeController::class, 'store'])->name('section-types.store');
        Route::put('/section-types/{sectionType}',         [\App\Http\Controllers\Admin\SectionTypeController::class, 'update'])->name('section-types.update');
        Route::delete('/section-types/{sectionType}',      [\App\Http\Controllers\Admin\SectionTypeController::class, 'destroy'])->name('section-types.destroy');
    });

    // Reports (admin only)
    Route::middleware('role:director_rh')->get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    // Admin routes
    Route::middleware('role:director_rh')->prefix('admin')->name('admin.')->group(function () {

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
        Route::patch('/employees/{user}/toggle-active',[UserController::class, 'toggleActive'])->name('employees.toggle-active');
        Route::get('/api/areas/{area}/position-types', [UserController::class, 'getPositionTypes'])->name('api.position-types');

        Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Period management
        Route::post('/periods',                [PeriodController::class, 'store'])->name('periods.store');
        Route::delete('/periods/{period}',     [PeriodController::class, 'destroy'])->name('periods.destroy');
        Route::patch('/periods/{period}/toggle',[PeriodController::class, 'toggle'])->name('periods.toggle');
        Route::post('/periods/generate',       [PeriodController::class, 'generate'])->name('periods.generate');


    });

    // ═══════════════════════════════════════════════════════════════════
    //  ROLES Y PERMISOS — solo para el Super Administrador (cédula 1070588425)
    // ═══════════════════════════════════════════════════════════════════
    Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/roles',              [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create',       [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles',             [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit',  [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}',       [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}',    [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{role}/users',         [RoleController::class, 'attachUser'])->name('roles.users.attach');
        Route::delete('/roles/{role}/users/{user}',[RoleController::class, 'detachUser'])->name('roles.users.detach');
    });
});
