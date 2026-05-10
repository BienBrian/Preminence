<?php
use App\Http\Controllers\Dashboard\Article\ArticlesController;
use App\Http\Controllers\Dashboard\Communication\EmailController;
use App\Http\Controllers\Dashboard\Communication\ScheduleController;
use App\Http\Controllers\Dashboard\EventsAndSeminars\EventsController;
use App\Http\Controllers\Dashboard\EventsAndSeminars\NoticesController;
use App\Http\Controllers\Dashboard\EventsAndSeminars\SeminarController;
use App\Http\Controllers\Dashboard\Finance\FinancialController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\Notifications\NotificationsController;
use App\Http\Controllers\Dashboard\PaymentSettings\PaymentSettingsController;
use App\Http\Controllers\Dashboard\People\PeopleController;
use App\Http\Controllers\Dashboard\Profile\ProfileController;
use App\Http\Controllers\Dashboard\Search\SearchController;
use App\Http\Controllers\Dashboard\Settings\EmailSettingsController;
use App\Http\Controllers\Dashboard\SMS\SMSController;
use App\Http\Controllers\Dashboard\Settings\SMSSettingsController;
use App\Http\Controllers\Dashboard\Settings\CurrencySettingsController;
use App\Http\Controllers\Dashboard\Settings\GameLevelSettingsController;
use App\Http\Controllers\Dashboard\Settings\GeneralSettingsController;
use App\Http\Controllers\Dashboard\Settings\NotificationSettingsController;
use App\Http\Controllers\Dashboard\Settings\TopupAccountSettingsController;
use App\Http\Controllers\Dashboard\Shop\ShopController;
use App\Http\Controllers\Dashboard\Spiritual\PrayersController;
use App\Http\Controllers\Dashboard\Spiritual\SermonController;
use App\Http\Controllers\Dashboard\Spiritual\TestimonialController;
use App\Http\Controllers\Dashboard\Users\AttendanceController;
use App\Http\Controllers\Dashboard\People\PastorsController;
use App\Http\Controllers\Dashboard\Users\RolesController;
use App\Http\Controllers\Dashboard\Users\UsersController;
use App\Http\Controllers\Dashboard\Settings\TagsController;
use App\Http\Controllers\Dashboard\Settings\SettingsLookupController;
use App\Http\Controllers\Dashboard\Settings\IntegrationsController;
use App\Http\Controllers\Dashboard\Settings\ReferenceMappingsSettingsController;
use App\Http\Controllers\Dashboard\FileManager\FileManagerController;
use App\Http\Controllers\Dashboard\PrayerRequests\PrayerRequestController;
use App\Http\Controllers\Dashboard\Reports\ReportsController;
use App\Http\Controllers\Dashboard\Billing\BillingController;
use App\Http\Controllers\Dashboard\TenantMarketplaceController;
use App\Http\Controllers\PrayerWallController;
use App\Http\Controllers\Dashboard\Websites\GalleryController;
use App\Http\Controllers\Dashboard\Websites\HomePageSettingsController;
use App\Http\Controllers\Dashboard\Websites\OrderOfServiceSettingsController;
use App\Http\Controllers\Dashboard\Websites\PastorSettingsController;
use App\Http\Controllers\Dashboard\Links\LinkShortenerController;
use App\Http\Controllers\Dashboard\Websites\WebsiteSettingsController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\SuperAdmin\Auth\LoginController as SuperAdminLoginController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [IndexController::class, 'index']);
Route::get('/people', [IndexController::class, 'people']);
Route::get('/noticeboard', [IndexController::class, 'notices']);
Route::get('/events_seminars', [IndexController::class, 'events']);
Route::get('/events/view/{id}', [IndexController::class, 'event']);
Route::get('/seminar/view/{id}', [IndexController::class, 'seminar']);
Route::get('/spiritual', [IndexController::class, 'spiritual']);
Route::get('/our_gallery', [IndexController::class, 'gallery']);
Route::get('/our_articles', [IndexController::class, 'articles']);
Route::get('/shop', [IndexController::class, 'shop']);
Route::get('communities/{id}', [IndexController::class, "community"]);
Route::get('articles/{id}/{slug?}', [IndexController::class, "article"]);
Route::get('/our_articles/category/{slug}', [IndexController::class, 'articlesByCategory']);
Route::get('sermons/{id}', [IndexController::class, "sermon"]);
Route::get('notices/view/{id}', [IndexController::class, "notice"]);
Route::get('departments/view/{id}', [IndexController::class, "department"]);
Route::get('prayers/view/{id}', [IndexController::class, "prayer"]);
Route::get('registration', [IndexController::class, "registration"]);
Route::post('registration', [IndexController::class, "saveregistration"]);
Route::get('/check-login', [IndexController::class, 'checkLogin']);
/*Route::get('/get/genders',[IndexController::class, 'getGenders']);
Route::get('see/mail', function(){
    return view('mails.mail');
});*/

// Public profile verification route
Route::get('verify-profile/{token}', [\App\Http\Controllers\VerifyProfileController::class, 'show'])->name('verify-profile');
Route::post('verify-profile/{token}', [\App\Http\Controllers\VerifyProfileController::class, 'submit'])->name('verify-profile.submit');

// Public onboarding routes (invitation-based registration)
Route::get('onboarding/{token}', [\App\Http\Controllers\OnboardingController::class, 'show'])->name('onboarding');
Route::post('onboarding/{token}/step1', [\App\Http\Controllers\OnboardingController::class, 'step1'])->name('onboarding.step1');
Route::post('onboarding/{token}/step2', [\App\Http\Controllers\OnboardingController::class, 'step2'])->name('onboarding.step2');
Route::post('onboarding/{token}/step3', [\App\Http\Controllers\OnboardingController::class, 'step3'])->name('onboarding.step3');
Route::post('onboarding/{token}/resend-otp', [\App\Http\Controllers\OnboardingController::class, 'resendOtp'])->name('onboarding.resend-otp');

// Public short link redirect
Route::get('s/{code}', [LinkShortenerController::class, 'redirect'])->name('shortlink.redirect');

// Public Prayer Wall
Route::get('/prayer-wall', [PrayerWallController::class, 'index']);
Route::post('/prayer-wall/submit', [PrayerWallController::class, 'submit'])->middleware('throttle:5,1');
Route::post('/prayer-wall/{id}/prayed', [PrayerWallController::class, 'prayedFor'])->middleware('throttle:30,1');

// Discipleship & Mentorship
Route::group(['prefix' => 'dashboard/spiritual/discipleship', 'middleware' => ['auth', 'module:discipleship']], function () {
    Route::get('/', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'index']);
    
    // Tracks
    Route::get('tracks', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'tracks']);
    Route::get('tracks/{id}', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'showTrack']);
    Route::post('tracks/{id}/add-step', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'addStep'])->middleware('can:Manage Discipleship');
    Route::post('steps/{id}/complete', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'completeStep']);
    
    // Admin Routes
    Route::post('tracks/create', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'createTrack'])->middleware('can:Manage Discipleship');
    Route::post('tracks/assign', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'assignTrack'])->middleware('can:Manage Discipleship');
    Route::post('mentorship/match', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'matchMentor'])->middleware('can:Manage Discipleship');

    // Mentorship
    Route::get('mentorship', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'mentorship']);
    Route::post('mentorship/log', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'logSession']);

    // Journal
    Route::get('journal', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'journal']);
    Route::post('journal/save', [\App\Http\Controllers\Dashboard\Spiritual\DiscipleshipController::class, 'saveJournal']);
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPERADMIN ROUTES (Platform Administration)
// ═══════════════════════════════════════════════════════════════════════════════
// Access ONLY via subdomain: superadmin.happychurchruiru.org/*
// Path-based access (/superadmin/*) has been removed for security

// Helper to register superadmin routes with both path and subdomain support
$registerSuperAdminRoutes = function () {
    // Login routes (guest only)
    Route::get('login', [SuperAdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminLoginController::class, 'login']);
    
    // Root redirect - based on auth status
    Route::get('/', function () {
        if (auth('superadmin')->check()) {
            return redirect('/dashboard');
        }
        return redirect('/login');
    });
    
    // Authenticated routes
    Route::middleware(['auth:superadmin'])->group(function () {
        // Dashboard (main page)
        Route::get('dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Redirect /dashboard/home to /dashboard
        Route::get('dashboard/home', function () {
            return redirect('/dashboard');
        });
        
        // Also redirect authenticated root to dashboard
        Route::get('/', function () {
            return redirect('/dashboard');
        });
        
        // Logout
        Route::post('logout', [SuperAdminLoginController::class, 'logout'])->name('logout');
        
        // Tenant Management
        Route::get('tenants', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'index'])->name('tenants.index');
        Route::get('tenants/create', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'store'])->name('tenants.store');
        Route::get('tenants/{id}', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'show'])->name('tenants.show');
        Route::get('tenants/{id}/edit', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('tenants/{id}', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'update'])->name('tenants.update');
        Route::get('tenants/{id}/suspend', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'showSuspendForm'])->name('tenants.suspend.form');
        Route::post('tenants/{id}/suspend', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{id}/activate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{id}/impersonate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'impersonate'])->name('tenants.impersonate');
        
        // Plans Management
        Route::get('plans', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/create', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'create'])->name('plans.create');
        Route::post('plans', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'store'])->name('plans.store');
        Route::get('plans/{id}/edit', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'edit'])->name('plans.edit');
        Route::put('plans/{id}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'update'])->name('plans.update');
        
        // SuperAdmins Management
        Route::get('admins', [\App\Http\Controllers\SuperAdmin\AdminController::class, 'index'])->name('admins.index');
        Route::get('admins/create', [\App\Http\Controllers\SuperAdmin\AdminController::class, 'create'])->name('admins.create');
        Route::post('admins', [\App\Http\Controllers\SuperAdmin\AdminController::class, 'store'])->name('admins.store');
        Route::get('admins/{id}/edit', [\App\Http\Controllers\SuperAdmin\AdminController::class, 'edit'])->name('admins.edit');
        Route::put('admins/{id}', [\App\Http\Controllers\SuperAdmin\AdminController::class, 'update'])->name('admins.update');
        
        // DNS Management
        Route::get('dns', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'index'])->name('dns.index');
        Route::get('dns/{tenant}/subdomain/edit', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'editSubdomain'])->name('dns.subdomain.edit');
        Route::put('dns/{tenant}/subdomain', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'updateSubdomain'])->name('dns.subdomain.update');
        Route::get('dns/{tenant}/custom-domain/edit', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'editCustomDomain'])->name('dns.custom_domain.edit');
        Route::put('dns/{tenant}/custom-domain', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'updateCustomDomain'])->name('dns.custom_domain.update');
        Route::post('dns/{tenant}/verify', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'verifyDns'])->name('dns.verify');
        Route::post('dns/{tenant}/provision-ssl', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'provisionSsl'])->name('dns.provision-ssl');
        Route::get('dns/{tenant}/propagation', [\App\Http\Controllers\SuperAdmin\DnsManagementController::class, 'propagationStatus'])->name('dns.propagation');
        
        // Module Marketplace Management
        Route::get('modules', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'index'])->name('modules.index');
        Route::get('modules/create', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'create'])->name('modules.create');
        Route::post('modules', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'store'])->name('modules.store');
        Route::get('modules/{module}/edit', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('modules/{module}', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'update'])->name('modules.update');
        Route::delete('modules/{module}', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('modules/{module}/toggle-active', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'toggleActive'])->name('modules.toggle-active');
        Route::get('modules/{module}/analytics', [\App\Http\Controllers\SuperAdmin\ModuleController::class, 'analytics'])->name('modules.analytics');
        
        // Module Onboarding Configuration
        Route::get('modules/{module}/onboarding', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'edit'])->name('modules.onboarding.edit');
        Route::put('modules/{module}/onboarding', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'update'])->name('modules.onboarding.update');
        Route::get('modules/{module}/onboarding/preview', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'preview'])->name('modules.onboarding.preview');
        Route::post('modules/{module}/onboarding/apply-template', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'applyTemplate'])->name('modules.onboarding.apply-template');
        Route::post('modules/{module}/onboarding/steps', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'storeStep'])->name('modules.onboarding.store-step');
        Route::post('modules/{module}/onboarding/steps/reorder', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'reorderSteps'])->name('modules.onboarding.reorder-steps');
        Route::delete('modules/{module}/onboarding/steps/{step}', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingController::class, 'destroyStep'])->name('modules.onboarding.destroy-step');
        
        // Plan-Module Matrix
        Route::get('plan-modules', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'index'])->name('plan-modules.index');
        Route::get('plans/{plan}/modules/edit', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'edit'])->name('plan-modules.edit');
        Route::put('plans/{plan}/modules', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'update'])->name('plan-modules.update');
        Route::post('plan-modules/bulk-update', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'bulkUpdate'])->name('plan-modules.bulk-update');
        Route::post('plan-modules/toggle', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'toggle'])->name('plan-modules.toggle');
        Route::post('plan-modules/copy', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'copy'])->name('plan-modules.copy');
        Route::get('plans/{plan}/pricing-preview', [\App\Http\Controllers\SuperAdmin\PlanModuleController::class, 'previewPricing'])->name('plan-modules.preview-pricing');
        
        // Tenant Module Management
        Route::get('tenants/{tenant}/modules', [\App\Http\Controllers\SuperAdmin\TenantModuleController::class, 'index'])->name('tenant-modules.index');
        Route::post('tenants/{tenant}/modules/grant', [\App\Http\Controllers\SuperAdmin\TenantModuleController::class, 'grant'])->name('tenant-modules.grant');
        Route::post('tenants/{tenant}/modules/{module_key}/revoke', [\App\Http\Controllers\SuperAdmin\TenantModuleController::class, 'revoke'])->name('tenant-modules.revoke');
        Route::post('tenants/{tenant}/modules/{module_key}/toggle-suspension', [\App\Http\Controllers\SuperAdmin\TenantModuleController::class, 'toggleSuspension'])->name('tenant-modules.toggle-suspension');
        Route::post('tenants/{tenant}/modules/{module_key}/pricing', [\App\Http\Controllers\SuperAdmin\TenantModuleController::class, 'updatePricing'])->name('tenant-modules.update-pricing');

        // Module Onboarding Review
        Route::get('module-onboarding', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'index'])->name('module-onboarding.index');
        Route::get('module-onboarding/stats', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'stats'])->name('module-onboarding.stats');
        Route::get('module-onboarding/{id}', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'show'])->name('module-onboarding.show');
        Route::get('module-onboarding/{id}/documents/{documentKey}/preview', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'previewDocument'])->name('module-onboarding.preview-document');
        Route::get('module-onboarding/{id}/documents/{documentKey}/download', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'downloadDocument'])->name('module-onboarding.download-document');
        Route::post('module-onboarding/{id}/approve', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'approve'])->name('module-onboarding.approve');
        Route::post('module-onboarding/{id}/reject', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'reject'])->name('module-onboarding.reject');
        Route::post('module-onboarding/{id}/request-info', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'requestMoreInfo'])->name('module-onboarding.request-info');
        Route::post('module-onboarding/bulk', [\App\Http\Controllers\SuperAdmin\ModuleOnboardingReviewController::class, 'bulkAction'])->name('module-onboarding.bulk');
        
        // Billing & Payments Management
        Route::get('billing', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'index'])->name('billing.index');
        Route::get('billing/invoices', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'invoices'])->name('billing.invoices');
        Route::get('billing/invoices/{invoice}', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'showInvoice'])->name('billing.invoice.show');
        Route::post('billing/invoices/{invoice}/refund', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'refund'])->name('billing.invoice.refund');
        Route::post('billing/invoices/{invoice}/mark-paid', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'markAsPaid'])->name('billing.invoice.mark-paid');
        Route::get('billing/tenants/{tenant}', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'tenantBilling'])->name('billing.tenant');
        Route::post('billing/generate-invoices', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'generateInvoices'])->name('billing.generate-invoices');
        Route::get('billing/subscriptions', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'subscriptions'])->name('billing.subscriptions');
        Route::get('billing/settings', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'settings'])->name('billing.settings');
        Route::post('billing/settings', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'updateSettings'])->name('billing.settings.update');
        Route::get('billing/export', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'export'])->name('billing.export');
        Route::get('billing/api/stats', [\App\Http\Controllers\SuperAdmin\BillingController::class, 'apiStats'])->name('billing.api.stats');
    });
};

// ═══════════════════════════════════════════════════════════════════════════════
// SUPERADMIN ROUTING CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════════
// Mode is determined by PISTI_ENV and SUPERADMIN_MODE in .env file:
// - PISTI_ENV=dev (or SUPERADMIN_MODE=path): Uses path-based /superadmin/*
// - PISTI_ENV=staging|production (or SUPERADMIN_MODE=subdomain): Uses superadmin.* subdomain
//
// To force a specific mode, set SUPERADMIN_MODE=path or SUPERADMIN_MODE=subdomain
// ═══════════════════════════════════════════════════════════════════════════════

// Get platform domain from config
$platformDomain = pisti_platform_domain();

// Superadmin routes - only ONE mode can be active at a time
// Mode is determined by is_path_mode() / is_subdomain_mode() helper functions
if (is_path_mode()) {
    // Path-based superadmin route (used in development mode)
    Route::group([
        'prefix' => superadmin_path_prefix(),
        'as' => 'superadmin.',
    ], $registerSuperAdminRoutes);
} else {
    // Subdomain-based superadmin route (used in staging/production)
    Route::group([
        'domain' => superadmin_subdomain(),
        'as' => 'superadmin.',
    ], $registerSuperAdminRoutes);
}

// Stop impersonating and return to superadmin panel
Route::post('stop-impersonating', function () {
    if (session()->has('impersonate_return_id')) {
        $superadminId = session('impersonate_return_id');
        session()->forget('impersonate_return_id');
        
        auth()->logout();
        auth('superadmin')->loginUsingId($superadminId);
        
        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Returned to SuperAdmin panel.');
    }
    return redirect()->route('home');
})->name('stop-impersonating');

Auth::routes(/*['verify' => true]*/);

Route::get('home', function () {
    return redirect()->to('dashboard/home');
});
// Force password change routes (must be outside dashboard group)
Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::get('password/force-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'showForm']);
    Route::post('password/force-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update']);
});

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'tenant.active', 'force_password_change']], function () {
    //home
    Route::get('home', [HomeController::class, 'index'])->name('home');
    
    // Tenant Marketplace (Profile Dropdown)
    Route::get('marketplace/available-modules', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'availableModules']);
    Route::post('marketplace/modules/{moduleKey}/activate', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'startActivation']);
    Route::get('marketplace/onboarding/{onboardingId}', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'getOnboardingForm']);
    Route::get('marketplace/onboarding/{onboardingId}/render', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'renderOnboarding'])->name('marketplace.onboarding.render');
    Route::post('marketplace/onboarding/{onboardingId}/save', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'saveOnboardingProgress']);
    Route::post('marketplace/onboarding/{onboardingId}/upload', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'uploadDocument']);
    Route::post('marketplace/onboarding/{onboardingId}/submit', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'submitOnboarding'])->name('marketplace.onboarding.submit');
    Route::get('marketplace/onboarding/{onboardingId}/status', [\App\Http\Controllers\Dashboard\TenantMarketplaceController::class, 'checkOnboardingStatus']);
    
    Route::get('/view/{years}', [HomeController::class, 'years']);
    Route::get('/expenditure/{years}', [HomeController::class, 'expenditure']);
    Route::get('/view/{years}/{id}', [HomeController::class, 'myfunds']);
    //website settings
    Route::get('website/settings', [WebsiteSettingsController::class, 'index']);
    Route::post('website/settings/add', [WebsiteSettingsController::class, 'addSettings']);
    Route::post('website/settings/uploadfavicon', [WebsiteSettingsController::class, 'uploadfavicon']);
    Route::post('website/settings/uploadicon', [WebsiteSettingsController::class, 'uploadicon']);

    //homepage settings
    Route::get('website/homepage', [HomePageSettingsController::class, 'index']);
    Route::post('website/homepage/upload', [HomePageSettingsController::class, 'uploadHomePage']);
    Route::post('website/homepage/add', [HomePageSettingsController::class, 'addHomePage']);

    //Gallery Settings
    Route::get('website/gallery', [GalleryController::class, 'index']);
    Route::get('website/gallery/categories', [GalleryController::class, 'categories']);
    Route::post('website/gallery/categories/add', [GalleryController::class, 'addcategory']);
    Route::post('website/gallery/upload', [GalleryController::class, 'uploadgallery']);
    Route::get('/website/gallery/category/{id}', [GalleryController::class, 'viewcategory']);
    Route::post('website/gallery/category/delete/{id}', [GalleryController::class, 'deletecategory']);
    Route::post('website/gallery/delete/{id}', [GalleryController::class, 'deletegallery']);
    //Pastors Settings
    Route::get('website/pastorsmessage', [PastorSettingsController::class, 'getMessage']);
    Route::post('website/pastorsmessage/image/upload', [PastorSettingsController::class, 'uploadimage']);
    Route::post('website/pastorsmessage/add', [PastorSettingsController::class, 'addPastorMessage']);
    //
    /*
    Route::get('/communities', 'CommunitiesController::class, 'index']);
    Route::get('/newcommunity', 'CommunitiesController@newcommunity');
    Route::get('/editcommunity/{id}', 'CommunitiesController@editcommunity');
    Route::post('/addcommunity', 'CommunitiesController@addcommunity');
    Route::get('/removecommunity/{id}', 'CommunitiesController@removecommunity');*/
    Route::get('website/orderofservice', [OrderOfServiceSettingsController::class, 'orderofservice']);
    Route::post('website/service/add', [OrderOfServiceSettingsController::class, 'addservice']);
    Route::post('website/service/remove/{id}', [OrderOfServiceSettingsController::class, 'removeservice']);

    Route::get('website/weeklyverse', [OrderOfServiceSettingsController::class, 'weeklyverse']);
    Route::post('website/weeklyverse/add', [OrderOfServiceSettingsController::class, 'addverse']);

    //Financial Controller (requires finance module)
    Route::group(['middleware' => 'module:finance'], function () {
    Route::get('finances/overview', [FinancialController::class, 'overview']);

    Route::get('finances/funds', [FinancialController::class, 'index']);
    Route::get('finances/datatables/funds', [FinancialController::class, 'datatablefunds']);
    Route::get('/funds/search', [FinancialController::class, 'fundssearch']);

    Route::get("finances/tithing/individual", [FinancialController::class, "individualtithing"]);

    Route::get('finances/expenses', [FinancialController::class, 'expenses']);
    Route::get('finances/datatables/expenses', [FinancialController::class, 'datatableexpenses']);


    Route::post('finances/funds/save', [FinancialController::class, 'savefunds']);
    Route::post('finances/funds/remove/{id}', [FinancialController::class, 'removefund']);

    //Payments Settings
    Route::get('/settings/funds/sources', [FinancialController::class, 'fundsource']);
    Route::post('/settings/funds/sources/save', [FinancialController::class, 'savefundsource']);
    Route::post('settings/funds/sources/remove/{id}', [FinancialController::class, 'removefundsource']);
    Route::post('settings/funds/mode/remove/{id}', [FinancialController::class, 'removefundmode']);
    Route::post('settings/funds/mode/save', [FinancialController::class, 'saveModeOfPayment']);
    Route::get('settings/ajax/sources/{id}', [FinancialController::class, 'getsources']);
    Route::get('settings/ajax/payment/{id}', [FinancialController::class, 'getpayments']);

    Route::get('finances/donations', [FinancialController::class, 'donations']);
    Route::get('finances/assets', [FinancialController::class, 'assets']);
    Route::post('finances/assets/add', [FinancialController::class, 'saveassets']);
    Route::post('finances/assets/remove/{id}', [FinancialController::class, 'removeasset']);

    // M-PESA Logs Module (Advanced reconciliation)
    Route::group(['middleware' => 'module:mpesa_logs'], function () {
        Route::get('finances/missing_mpesa_phones', [FinancialController::class, 'missingMpesaPhones']);
        Route::get('finances/datatable/missing_mpesa_phones', [FinancialController::class, 'getMissingMpesaPhones']);
        Route::post('finances/missing_mpesa_phones/add', [FinancialController::class, 'addMissingMpesaPhone']);
        Route::post('finances/mpesa/populate-hashes', [FinancialController::class, 'populateMpesaHashes']);
        Route::post('finances/mpesa/retro-match', [FinancialController::class, 'retroMatchFunds']);
    });

    //Activities and Pledges
    Route::get('finances/activities', [FinancialController::class, 'activities']);
    Route::post("finances/activities/remove/{id}", [FinancialController::class, "removeactivity"]);
    Route::post('finances/activities/add', [FinancialController::class, 'addactivity']);
    Route::get('/getactivity/{id}', [FinancialController::class, 'getactivity']);
    Route::get('finances/activities/pledges/{id}', [FinancialController::class, 'pledges']);
    Route::post("finances/activities/users/groups/add", [FinancialController::class, "addgroup"]);
    Route::get('ajax/pledge/{id}', [FinancialController::class, 'getPledges']);
    Route::post('finances/activities/pledges/import', [FinancialController::class, 'importPledges']);

    Route::post('finances/activities/pledges/sms', [SMSController::class, 'pledges'])->middleware('throttle:10,1'); //add pledge and send sms
    Route::post('pledge/edit', [SMSController::class, 'editPledge'])->middleware('throttle:10,1');//edit pledge and send sms
    Route::post('finances/activities/pledge/remind', [SMSController::class, 'pledgeReminder'])->middleware('throttle:5,1');
    Route::post('pledges/remove/{id}', [FinancialController::class, 'removepledge']);
    Route::post('pledge/pay', [SMSController::class, 'paypledge'])->middleware('throttle:10,1');

    Route::get('finances/ajax/pledges/users', [FinancialController::class, 'getUsers']);
    Route::get('finances/activities/pledges/groups/{id}', [FinancialController::class, 'groups']);
    Route::get('ajax/groups/users', [FinancialController::class, 'getAjaxUsers']);
    Route::post('/pledges/recieved/{id}', [FinancialController::class, 'recieved']);
    Route::post('/groups/recieved/{id}', [FinancialController::class, 'recievedgroups']);
    Route::post('/groups/remove/{id}', [FinancialController::class, 'removegroup']);
    Route::get('finances/activities/groups/participants/{id}', [FinancialController::class, 'groupsparticipants']);
    Route::post('finances/activities/groups/pledges/members/add', [SMSController::class, 'addpledgegroupmembers']);

    //temp pdf
    Route::get("finances/printpdf/budget/{id}", [FinancialController::class, 'budgetpdf']);
    
    // Budgets Module
    Route::group(['middleware' => 'module:budgets'], function () {
        Route::get("finances/budgets", [FinancialController::class, "budgets"]);
        Route::post("finances/budget/save", [FinancialController::class, "addbudget"]);
        Route::post("finances/budgets/remove/{id}", [FinancialController::class, "removebudget"]);
        Route::get("finances/budgets/edit/{id}", [FinancialController::class, "budget"]);
        Route::get("finances/budgets/preview/{id}", [FinancialController::class, "previewbudget"]);
    });

    //Individual Tithing
    Route::get("finances/users/search/{search}", [FinancialController::class, "search"]);
    Route::post("finances/tithing/individual/save", [FinancialController::class, "saveindividualtithe"]);
    Route::get("finances/tithing/{id}", [FinancialController::class, "tithes"]);

    //funds summaries
    Route::get("finances/summaries", [FinancialController::class, "summaries"]);
    Route::post("finances/summaries/settings/add", [FinancialController::class, "summaries_settings"]);
    }); // End finance module group

    // Sermons Module
    Route::group(['middleware' => 'module:sermons'], function () {
        Route::get('spiritual/sermons', [SermonController::class, 'index']);
        Route::get('spiritual/sermons/new', [SermonController::class, 'sermon']);
        Route::post('spiritual/sermons/add', [SermonController::class, 'addsermon']);
        Route::get('spiritual/sermons/edit/{id}', [SermonController::class, 'editsermon']);
        Route::post('spiritual/sermons/edit', [SermonController::class, 'editmysermon']);
        Route::post('spiritual/sermons/delete/{id}', [SermonController::class, 'deletesermon']);
    });

    // Testimonials Module
    Route::group(['middleware' => 'module:testimonials'], function () {
        Route::get('spiritual/testimonials', [TestimonialController::class, 'index'])->name('testimonials');
        Route::post('spiritual/testimonials/add', [TestimonialController::class, 'save']);
        Route::get('spiritual/testimonials/activate/{id}', [TestimonialController::class, 'activate']);
        Route::get('spiritual/testimonials/deactivate/{id}', [TestimonialController::class, 'deactivate']);
        Route::get('spiritual/testimonials/{id}', [TestimonialController::class, 'testimonial']);
    });
    
    // Prayer Requests Module
    Route::group(['middleware' => 'module:prayer_requests'], function () {
        Route::get('prayer-requests', [PrayerRequestController::class, 'index']);
        Route::get('prayer-requests/datatable', [PrayerRequestController::class, 'datatable']);
        Route::post('prayer-requests/add', [PrayerRequestController::class, 'store']);
        Route::post('prayer-requests/{id}/prayed', [PrayerRequestController::class, 'markPrayed']);
        Route::post('prayer-requests/{id}/status', [PrayerRequestController::class, 'updateStatus']);
    });

    //events
    Route::get('events_and_notices/events', [EventsController::class, 'index']);
    Route::post('events_and_notices/events/add', [EventsController::class, 'addevent']);
    Route::get('events_and_notices/events/{id}', [EventsController::class, 'getevent']);
    Route::post('events_and_notices/semiars/events/delete/{id}', [EventsController::class, 'removeEvent']);

    //notices
    Route::get('events_and_notices/notices', [NoticesController::class, 'index']);
    Route::post('events_and_notices/notices/add', [NoticesController::class, 'addnotice']);
    Route::get('events_and_notices/notices/{id}', [NoticesController::class, 'getnotice']);
    Route::post('events_and_notices/notices/delete/{id}', [NoticesController::class, 'deletenotice']);
    /*
    Route::get('/departments', 'DepartmentsController@index');
    Route::post('/adddepartment', 'DepartmentsController@adddepartment');
    Route::get('/departments/{id}', 'DepartmentsController@getdepartment');
    Route::get('/deletedepartment/{id}', 'DepartmentsController@deletedepartment');*/
    //seminors
    Route::get("events_and_notices/seminars", [SeminarController::class, "index"]);
    Route::post("events_and_notices/seminars/add", [SeminarController::class, "addseminar"]);
    Route::get("events_and_notices/seminars/{id}", [SeminarController::class, "getSeminar"]);
    //attendance
    Route::get("events_and_notices/attendance", [AttendanceController::class, "index"]);


    //people
    Route::get('people/new', [PeopleController::class, 'newpeople']);
    Route::get('people/users', [PeopleController::class, 'users']);
    Route::get('people/edit/{category}/{id}', [PeopleController::class, 'newpeople']);
    Route::get('people/members/{id}', [PeopleController::class, 'members']);
    Route::get('people/members/datatable/{id}', [PeopleController::class, 'getMembersDataTable']);
    Route::post('people/members/add', [PeopleController::class, 'addmembers']);
    Route::post('people/add', [PeopleController::class, 'addpeoplegroups']);
    Route::get('/people/communities', [PeopleController::class, 'people']);
    Route::get('/people/departments', [PeopleController::class, 'people']);
    Route::post('/people/member/activate/{id}', [PeopleController::class, 'activate']);
    Route::post('/people/member/deactivate/{id}', [PeopleController::class, 'deactivate']);
    Route::post('/people/member/remove/{id}', [PeopleController::class, 'remove']);

    //prayers (legacy → redirect to new Prayer Requests module)
    Route::get('/spiritual/prayers', function () { return redirect()->to('dashboard/prayer-requests'); });
    Route::get('spiritual/prayers/{id}', [PrayersController::class, 'getprayer']);
    Route::post('spiritual/prayers/add', [PrayersController::class, 'addprayer']);
    Route::post('spiritual/prayers/delete/{id}', [PrayersController::class, 'deleteprayer']);

    // Shop (requires shop module)
    Route::group(['middleware' => 'module:shop'], function () {
    Route::get("shop", [ShopController::class, "index"]);
    Route::get("shop/products", [ShopController::class, "products"]);
    Route::get("shop/products/{id}", [ShopController::class, "product"]);
    Route::post("shop/products/add", [ShopController::class, "addproduct"]);
    Route::post("shop/products/save", [ShopController::class, "saveproduct"]);
    Route::post("shop/products/image/edit", [ShopController::class, 'editproductimage']);
    Route::post("shop/products/edit", [ShopController::class, "editproduct"]);
    Route::post("shop/products/remove/{id}", [ShopController::class, "removeproduct"]);

    Route::get("shop/purchases", [ShopController::class, "purchases"]);
    }); // End shop module group

    //update contacts
    Route::get("user/contacts/update", [UsersController::class, "updateMyContacts"]);

    // Attendance (Core feature - no module required)
    Route::get("attendance", [AttendanceController::class, "index"]);
    
    // Children's Check-in Module
    Route::group(['middleware' => 'module:children_checkin'], function () {
        Route::get('children', [AttendanceController::class, 'children']);
        Route::get('children/view/{id}', [AttendanceController::class, 'child']);
        Route::get('children/events/search', [AttendanceController::class, 'searchChildrenEvents']);
        Route::get('children/parents/search', [AttendanceController::class, 'searchChildrenParents']);
        Route::get('children/datatables/guardians/{id}', [AttendanceController::class, 'getChildGuardians']);
        Route::post('children/parent/add', [AttendanceController::class, 'addParent']);
        Route::get('datatables/children', [AttendanceController::class, 'getChildren']);
        Route::post('children/upload', [AttendanceController::class, 'importChildren']);
        Route::get("children/attendance", [AttendanceController::class, "childrenAttendance"]);
        Route::get("children/datatable/events", [AttendanceController::class, "getChildrenEvents"]);
        Route::post('children/save/checkin', [AttendanceController::class, 'saveChildrenCheckin']);
        Route::get("children/attendance/{id}", [AttendanceController::class, "showChildrenAttendance"]);
        Route::post('children/save/attendance', [AttendanceController::class, 'saveChildrenAttendance']);
        Route::post('children/save', [AttendanceController::class, 'saveChild']);
        Route::get('children/datatable/attendance/{id}', [AttendanceController::class, 'getChildrenAttendance']);
        Route::post('children/checkout/{id}', [AttendanceController::class, 'checkOut']);
    });

    Route::get("ajax/attendance/{group}/{time}", [AttendanceController::class, "ajaxAttendance"]);
    Route::get("ajax/events", [AttendanceController::class, "ajaxEvents"]);
    Route::get("ajax/seminars", [AttendanceController::class, "ajaxSeminars"]);
    Route::get("ajax/services", [AttendanceController::class, "ajaxServices"]);
    Route::get("datatables/attendance", [AttendanceController::class, "datatablesAttendance"]);
    Route::get("events_and_notices/attendance/groups", [AttendanceController::class, "attendancegroups"]);
    Route::post("events_and_notices/attendance/group/add", [AttendanceController::class, "addAttendanceGroup"]);
    Route::post("events_and_notices/attendance/add", [AttendanceController::class, "addAttendance"]);
    Route::get("events_and_notices/attendance/new", [AttendanceController::class, "newAttendance"]);
    Route::post("events_and_notices/attendance/remove/{id}", [AttendanceController::class, "removeAttendance"]);
    Route::post("events_and_notices/attendance/groups/remove/{id}", [AttendanceController::class, "removeAttendanceGroup"]);

    //pastors
    Route::get('people/pastors', [PastorsController::class, 'pastors']);
    Route::post('/addpastor', [PastorsController::class, 'addpastor']);
    Route::post('/pastors/update-title', [PastorsController::class, 'updateTitle']);
    Route::post('/pastors/remove', [PastorsController::class, 'removePastor']);

    // Articles Module
    Route::group(['middleware' => 'module:articles'], function () {
        Route::get('/articles', [ArticlesController::class, 'index']);
        Route::get('/articles/datatable', [ArticlesController::class, 'getArticlesDataTable']);
        Route::get('/articles/new', [ArticlesController::class, 'newarticle']);
        Route::get('/articles/edit/{id}', [ArticlesController::class, 'editarticle']);
        Route::post('/articles/add', [ArticlesController::class, 'addarticle']);
        Route::post('/articles/activate/{id}', [ArticlesController::class, 'activate']);
        Route::post('/articles/deactivate/{id}', [ArticlesController::class, 'deactivate']);
        Route::post('/articles/remove/{id}', [ArticlesController::class, 'removearticle']);
        Route::post('/articles/toggle-featured/{id}', [ArticlesController::class, 'toggleFeatured']);
        Route::get('/articles/categories', [ArticlesController::class, 'categories']);
        Route::post('/articles/categories/add', [ArticlesController::class, 'addCategory']);
        Route::post('/articles/categories/delete/{id}', [ArticlesController::class, 'deleteCategory']);
    });

    /*Route::get('/profile', 'UsersController@profile');
    Route::post('/updateprofile', 'UsersController@updateprofile');
    Route::post('/profileimage', 'UsersController@profileimage');
    Route::post('/contacts', 'UsersController@addcontacts');*/

    //Notifications
    Route::get('notifications', [NotificationsController::class, 'index']);
    Route::get('datatable/notifications', [NotificationsController::class, 'getNotifications']);
    Route::get('notifications/view/{id}', [NotificationsController::class, 'notification']);


    //emails
    /*Route::get('emails', [EmailController::class, 'index']);
    Route::get('datatable/emails', [EmailController::class, 'getEmails']);
    Route::get('emails/send', [EmailController::class, 'email']);
    Route::post('emails/send', [EmailController::class, 'sendMail']);
    Route::get('emails/view/{id}', [EmailController::class, 'viewEmail']);*/

    //communication
    Route::get("communication", function () { return redirect()->to('dashboard/communication/sms'); });
    Route::get("communication/emails", [EmailController::class, "index"]);
    Route::get("communication/emails/datatable", [EmailController::class, "getEmailsDataTable"]);
    Route::get("communication/emails/users", [EmailController::class, "getemails"]);
    Route::get('communication/emails/scheduled/datatable', [EmailController::class, 'getScheduledEmailsDataTable']);
    Route::post('communication/emails/schedule/cancel', [EmailController::class, 'cancelScheduledEmail']);
    Route::get("communication/emails/view/{id}", [EmailController::class, "email"]);
    Route::get("communication/emails/json/{id}", [EmailController::class, "emailJson"]);
    Route::post('communication/emails/delete/{id}', [EmailController::class, 'removeemail']);
    Route::post('communication/emails/send', [EmailController::class, 'html_email']);

    //sms
    Route::get("communication/sms", [SMSController::class, "sms"]);
    Route::get("communication/sms/credits-balance", [SMSController::class, "getCreditsBalance"]);
    Route::get("communication/sms/datatable", [SMSController::class, "getSmsDataTable"]);
    Route::get("communication/sms/summary", [SMSController::class, "getSmsSummary"]);
    Route::post('communication/sms/send', [SMSController::class, 'sendSms'])->middleware('throttle:10,1');
    Route::get('communication/sms/scheduled/datatable', [SMSController::class, 'getScheduledDataTable']);
    Route::post('communication/sms/schedule/cancel', [SMSController::class, 'cancelSchedule']);
    Route::get('communication/sms/birthday/settings', [SMSController::class, 'getBirthdaySettings']);
    Route::post('communication/sms/birthday/settings', [SMSController::class, 'saveBirthdaySettings']);
    Route::get('communication/sms/mpesa/settings', [SMSController::class, 'getMpesaSettings']);
    Route::post('communication/sms/mpesa/settings', [SMSController::class, 'saveMpesaSettings']);
    Route::get('communication/sms/manual-tithe/settings', [SMSController::class, 'getManualTitheSettings']);
    Route::post('communication/sms/manual-tithe/settings', [SMSController::class, 'saveManualTitheSettings']);
    Route::get('communication/sms/phone_numbers', [SMSController::class, "phoneNumbers"]);
    Route::get('communication/sms/phone_numbers/{search}', [SMSController::class, "phoneNumbers"]);
    Route::get('communication/sms/view/{id}', [SMSController::class, 'readsms']);
    Route::get('communication/sms/json/{id}', [SMSController::class, 'readsmsJson']);
    Route::post('communication/sms/remove/{id}', [SMSController::class, 'removesms']);
    
    // SMS Resend functionality (requires Resend SMS permission)
    Route::get('communication/sms/{id}/resend-data', [SMSController::class, 'getSmsForResend'])
        ->middleware('permission:Resend SMS');
    Route::post('communication/sms/resend', [SMSController::class, 'resendSms'])
        ->middleware(['permission:Resend SMS', 'throttle:5,1'])
        ->name('communication.sms.resend');
    
    // Bulk SMS Actions
    Route::post('communication/sms/bulk-resend', [SMSController::class, 'bulkResend'])
        ->middleware(['permission:Resend SMS', 'throttle:5,1'])
        ->name('communication.sms.bulk-resend');
    Route::post('communication/sms/bulk-delete', [SMSController::class, 'bulkDelete'])
        ->name('communication.sms.bulk-delete');

    // Legacy scheduling redirects
    Route::get("communication/schedule/sms", function () { return redirect()->to('dashboard/communication/sms'); });
    Route::get("communication/schedule/sms/{id}", function () { return redirect()->to('dashboard/communication/sms'); });
    Route::post("communication/schedule/sms/cancel/{id}", [ScheduleController::class, "cancelschedule"]);
    /*
    Route::get('user/phone/{id}', 'SMSController@getPhone');
    Route::post('users/sendsms', 'SMSController@sendsinglesms');
    Route::get('smsexample', function () {
        return view('admin.example');
    });*/
    //settings
    Route::get('settings/sms', [SMSSettingsController::class, 'index']);
    Route::post('settings/sms/add', [SMSSettingsController::class, 'addSmsSettings']);

    Route::get('settings/email', [EmailSettingsController::class, 'index']);
    Route::post('settings/email/add', [EmailSettingsController::class, 'addEmailSettings']);

    //tags
    Route::get('settings/tags', [TagsController::class, 'index']);
    Route::get('settings/tags/datatable', [TagsController::class, 'getTags']);
    Route::post('settings/tags/add', [TagsController::class, 'addTag']);
    Route::post('settings/tags/delete/{id}', [TagsController::class, 'deleteTag']);
    Route::get('settings/tags/search', [TagsController::class, 'searchTags']);
    Route::post('settings/tags/assign', [TagsController::class, 'assignTag']);
    Route::post('settings/tags/remove', [TagsController::class, 'removeUserTag']);

    //lookups (sunday school classes & residences)
    Route::get('settings/lookups', [SettingsLookupController::class, 'index']);
    Route::get('settings/sunday-school-classes/datatable', [SettingsLookupController::class, 'getSundaySchoolClasses']);
    Route::post('settings/sunday-school-classes/add', [SettingsLookupController::class, 'addSundaySchoolClass']);
    Route::post('settings/sunday-school-classes/delete/{id}', [SettingsLookupController::class, 'deleteSundaySchoolClass']);
    Route::get('settings/sunday-school-classes/search', [SettingsLookupController::class, 'searchSundaySchoolClasses']);
    Route::get('settings/residences/datatable', [SettingsLookupController::class, 'getResidences']);
    Route::post('settings/residences/add', [SettingsLookupController::class, 'addResidence']);
    Route::post('settings/residences/delete/{id}', [SettingsLookupController::class, 'deleteResidence']);
    Route::get('settings/residences/search', [SettingsLookupController::class, 'searchResidences']);

    //integrations
    Route::get('settings/integrations', [IntegrationsController::class, 'index']);
    Route::get('settings/integrations/datatable', [IntegrationsController::class, 'datatable']);
    Route::get('settings/integrations/schema', [IntegrationsController::class, 'getFieldSchema']);
    Route::get('settings/integrations/s', [IntegrationsController::class, 'store']);
    Route::post('settings/integrations/delete/{id}', [IntegrationsController::class, 'delete']);
    Route::post('settings/integrations/toggle/{id}', [IntegrationsController::class, 'toggleActive']);
    Route::post('settings/integrations/default/{id}', [IntegrationsController::class, 'setDefault']);

    //users
    Route::get('users/all', [UsersController::class, 'index']);
    Route::get('users/datatable', [UsersController::class, 'getUsers']);
    Route::post('users/add', [UsersController::class, 'addUser']);
    Route::post('users/import', [UsersController::class, 'importUsers']);
    // Duplication Checker Module
    Route::group(['middleware' => 'module:duplication_checker'], function () {
        Route::get('users/duplicates', [UsersController::class, 'duplicates']);
        Route::get('users/duplicates/scan', [UsersController::class, 'scanDuplicates']);
    });

    // Link Shortener
    Route::get('links', [LinkShortenerController::class, 'index']);
    Route::get('links/datatable', [LinkShortenerController::class, 'datatable']);
    Route::post('links/store', [LinkShortenerController::class, 'store']);
    Route::post('links/update', [LinkShortenerController::class, 'update']);
    Route::post('links/delete', [LinkShortenerController::class, 'delete']);
    Route::get('links/stats', [LinkShortenerController::class, 'stats']);
    Route::get('users/view/{id}', [UsersController::class, 'viewUser']);
    Route::post('users/alternative-phones', [UsersController::class, 'addAlternativePhone']);
    Route::delete('users/alternative-phones/{id}', [UsersController::class, 'removeAlternativePhone']);
    Route::post('users/update/basic', [UsersController::class, 'updateBasic']);
    Route::post('users/update/contacts', [UsersController::class, 'updateContacts']);
    Route::post('users/update/church', [UsersController::class, 'updateChurch']);
    Route::post('users/update/family', [UsersController::class, 'updateFamily']);
    Route::post('users/update/profession', [UsersController::class, 'updateProfession']);
    Route::post('users/update/education', [UsersController::class, 'updateEducation']);
    Route::post('users/sendsms', [UsersController::class, 'sendUserSms'])->middleware('throttle:10,1');
    Route::get('users/non-members/datatable', [UsersController::class, 'nonMembersDataTable']);
    Route::post('users/non-members/sms', [UsersController::class, 'sendNonMemberSms'])->middleware('throttle:10,1');
    Route::post('users/invite', [UsersController::class, 'inviteUser'])->middleware('throttle:10,1');
    Route::post('users/check-invite-phone', [UsersController::class, 'checkInvitePhone']);
    Route::get('users/invitations', [UsersController::class, 'invitations']);
    Route::post('users/bulk-verify', [UsersController::class, 'bulkInviteVerification']);
    Route::get('users/invitations/datatable', [UsersController::class, 'invitationsDataTable']);
    Route::post('users/invitations/resend', [UsersController::class, 'resendInvitation'])->middleware('throttle:5,1');
    Route::post('users/toggle-verification', [UsersController::class, 'toggleVerification']);
    Route::post('users/send-verification-request', [UsersController::class, 'sendVerificationRequest'])->middleware('throttle:5,1');
    Route::post('users/merge', [UsersController::class, 'mergeUsers']);
    Route::post('users/quick-edit', [UsersController::class, 'quickEditUser']);
    Route::post('users/archive', [UsersController::class, 'archiveUser']);
    Route::post('users/unarchive', [UsersController::class, 'unarchiveUser']);
    Route::get('users/hash-coverage', [UsersController::class, 'hashCoverage']);
    Route::post('users/rehash', [UsersController::class, 'rehashUser']);
    Route::post('users/delete', [UsersController::class, 'deleteUser']);
    Route::post('users/share/add', [UsersController::class, 'addShare']);
    Route::get('users/view/datatable/shares/{id}', [UsersController::class, 'getShares']);
    Route::get('users/roles', [RolesController::class, 'index']);
    Route::get('users/datatable/roles', [RolesController::class, 'getRoles']);
    Route::post('users/roles/add', [RolesController::class, 'addRole']);
    Route::get('users/roles/view/{id}', [RolesController::class, 'viewRole']);
    Route::post('users/roles/permissions/add', [RolesController::class, 'addPermissions']);
    //payment settings
    Route::get('payments/settings/banks', [PaymentSettingsController::class, 'index']);
    Route::get('payments/settings/datatable/banks', [PaymentSettingsController::class, 'getBanks']);
    Route::post('payments/settings/banks/add', [PaymentSettingsController::class, 'addBank']);

    Route::get('payments/settings/upi', [PaymentSettingsController::class, 'upi']);
    Route::get('payments/settings/datatable/upis', [PaymentSettingsController::class, 'getUPIs']);
    Route::post('payments/settings/upis/add', [PaymentSettingsController::class, 'addUPI']);

    //settings
    Route::get('settings/currencies', [CurrencySettingsController::class, 'index']);
    Route::get('settings/datatable/currencies', [CurrencySettingsController::class, 'getCurrencies']);
    Route::post('settings/currencies/add', [CurrencySettingsController::class, 'addCurrency']);

    Route::get('settings/notifications', [NotificationSettingsController::class, 'index']);
    Route::get('settings/datatable/notifications', [NotificationSettingsController::class, 'getNotifications']);
    Route::post('settings/notifications/add', [NotificationSettingsController::class, 'addNotification']);

    Route::get('settings/general', [GeneralSettingsController::class, 'index']);
    Route::post('settings/general/add', [GeneralSettingsController::class, 'addGeneralSettings']);

    // Reference Mappings Settings
    Route::get('settings/reference-mappings', [ReferenceMappingsSettingsController::class, 'index']);
    Route::get('settings/reference-mappings/datatable', [ReferenceMappingsSettingsController::class, 'getMappings']);
    Route::get('settings/reference-mappings/unmapped', [ReferenceMappingsSettingsController::class, 'getUnmappedReferences']);
    Route::post('settings/reference-mappings', [ReferenceMappingsSettingsController::class, 'store']);
    Route::put('settings/reference-mappings/{id}', [ReferenceMappingsSettingsController::class, 'update']);
    Route::delete('settings/reference-mappings/{id}', [ReferenceMappingsSettingsController::class, 'destroy']);
    Route::post('settings/reference-mappings/bulk-import', [ReferenceMappingsSettingsController::class, 'bulkImport']);

    //reports (requires reports module)
    Route::group(['middleware' => 'module:reports'], function () {
    Route::get('reports', [ReportsController::class, 'index']);
    Route::get('reports/mpesa-logs', [ReportsController::class, 'mpesaLogs']);
    Route::get('reports/mpesa-logs/datatable', [ReportsController::class, 'mpesaLogsDataTable']);
    Route::post('reports/mpesa-logs/rehash', [ReportsController::class, 'rehashTransaction']);
    Route::post('reports/mpesa-logs/bulk-rehash', [ReportsController::class, 'bulkRehash']);
    
    // MPESA Reference Type Management
    Route::get('reports/mpesa-logs/reference-types', [ReportsController::class, 'getReferenceTypes']);
    Route::get('reports/mpesa-logs/reference-mappings', [ReportsController::class, 'getReferenceMappings']);
    Route::post('reports/mpesa-logs/reference-mappings', [ReportsController::class, 'saveReferenceMapping']);
    Route::delete('reports/mpesa-logs/reference-mappings/{id}', [ReportsController::class, 'deleteReferenceMapping']);
    Route::get('reports/mpesa-logs/suggest-mappings', [ReportsController::class, 'suggestReferenceMappings']);
    Route::get('reports/mpesa-logs/unmapped-references', [ReportsController::class, 'getUnmappedReferences']);
    Route::post('reports/mpesa-logs/auto-discover', [ReportsController::class, 'autoDiscoverReferences']);
    Route::post('reports/mpesa-logs/inline-map', [ReportsController::class, 'inlineMapReference']);
    Route::get('reports/mpesa-logs/reference-suggestions', [ReportsController::class, 'getReferenceSuggestions']);
    
    // Summary Category Management
    Route::get('reports/mpesa-logs/summary-categories', [ReportsController::class, 'getSummaryCategories']);
    Route::post('reports/mpesa-logs/summary-categories', [ReportsController::class, 'saveSummaryCategory']);
    Route::delete('reports/mpesa-logs/summary-categories/{id}', [ReportsController::class, 'deleteSummaryCategory']);
    Route::post('reports/mpesa-logs/summary-categories/assign', [ReportsController::class, 'assignMappingsToCategory']);
    Route::post('reports/mpesa-logs/summary-categories/remove', [ReportsController::class, 'removeMappingsFromCategory']);
    Route::get('reports/mpesa-logs/category-summary', [ReportsController::class, 'getCategorySummary']);
    Route::post('reports/mpesa-logs/sync-fund-sources', [ReportsController::class, 'syncFundSourcesToCategories']);
    
    // Print MPESA Report
    Route::get('reports/mpesa-logs/print', [ReportsController::class, 'printMpesaReport'])->name('reports.mpesa.print');
    
    // Giving Statements (requires giving_statements module)
    Route::group(['middleware' => 'module:giving_statements'], function () {
        Route::get('reports/giving-statements', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'index']);
        Route::get('reports/giving-statements/generate', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'generate']);
        Route::post('reports/giving-statements/preview', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'preview']);
        Route::post('reports/giving-statements/download', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'downloadNew']);
        Route::get('reports/giving-statements/download/{logId}', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'download']);
        Route::post('reports/giving-statements/email', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'email']);
        Route::post('reports/giving-statements/bulk-email', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'bulkEmail']);
        Route::get('reports/giving-statements/settings', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'settings']);
        Route::post('reports/giving-statements/settings', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'updateSettings']);
        Route::post('reports/giving-statements/test-email', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'testEmail']);
        Route::post('reports/giving-statements/contributors', [\App\Http\Controllers\GivingStatements\GivingStatementController::class, 'getContributors']);
    }); // End giving_statements module group
    }); // End reports module group

    //prayer requests
    Route::get('prayer-requests', [PrayerRequestController::class, 'index']);
    Route::get('prayer-requests/datatable', [PrayerRequestController::class, 'datatable']);
    Route::get('prayer-requests/stats', [PrayerRequestController::class, 'stats']);
    Route::get('prayer-requests/{id}', [PrayerRequestController::class, 'show']);
    Route::post('prayer-requests', [PrayerRequestController::class, 'store']);
    Route::put('prayer-requests/{id}', [PrayerRequestController::class, 'update']);
    Route::post('prayer-requests/{id}/note', [PrayerRequestController::class, 'addNote']);
    Route::post('prayer-requests/{id}/assign', [PrayerRequestController::class, 'assign']);
    Route::post('prayer-requests/{id}/status', [PrayerRequestController::class, 'changeStatus']);
    Route::post('prayer-requests/{id}/moderate', [PrayerRequestController::class, 'moderate']);
    Route::post('prayer-requests/{id}/prayed', [PrayerRequestController::class, 'prayedFor']);
    Route::post('prayer-requests/{id}/delete', [PrayerRequestController::class, 'delete']);

    // File Manager Module
    Route::group(['middleware' => 'module:file_manager'], function () {
        Route::get('file-manager', [FileManagerController::class, 'index']);
        Route::get('file-manager/folders', [FileManagerController::class, 'getFolders']);
        Route::get('file-manager/files', [FileManagerController::class, 'getFiles']);
        Route::post('file-manager/folder/create', [FileManagerController::class, 'createFolder']);
        Route::post('file-manager/folder/update', [FileManagerController::class, 'updateFolder']);
        Route::post('file-manager/folder/delete', [FileManagerController::class, 'deleteFolder']);
        Route::post('file-manager/upload', [FileManagerController::class, 'uploadFiles']);
        Route::post('file-manager/file/delete', [FileManagerController::class, 'deleteFile']);
    });

    //profile
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile/change', [ProfileController::class, 'editProfile']);
    Route::post('profile/change/password', [ProfileController::class, 'changePassword']);
    Route::post('profile/upload/picture', [ProfileController::class, 'uploadProfilePicture']);
    Route::post('profile/verify-email', [ProfileController::class, 'sendEmailVerification']);
    Route::post('profile/confirm-email-otp', [ProfileController::class, 'verifyEmailOtp']);

    //Search
    Route::get('search/timezones', [SearchController::class, 'searchTimezones']);
    Route::get('search/maturities', [SearchController::class, 'searchMaturities']);
    Route::get('search/users', [SearchController::class, 'searchUsers']);
    Route::get('search/roles', [SearchController::class, 'searchRoles']);
    Route::get('search/days', [SearchController::class, 'searchDays']);
    Route::get('search/payments/{payment_method}/{user_id}', [SearchController::class, 'searchPaymentMethods']);

    // Billing & Subscription
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/upgrade', [BillingController::class, 'upgrade'])->name('billing.upgrade');
    Route::get('billing/module-locked', [BillingController::class, 'moduleLocked'])->name('billing.module_locked');
    
    // Module Marketplace (Tenant)
    Route::get('marketplace', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('marketplace/search', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'searchApi'])->name('marketplace.search');
    Route::get('marketplace/{moduleKey}', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'show'])->name('marketplace.show');
    Route::get('marketplace/{moduleKey}/install', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'installForm'])->name('marketplace.install-form');
    Route::post('marketplace/{moduleKey}/install', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'install'])->name('marketplace.install');
    Route::get('marketplace/installations/{subscription}/status', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'installationStatus'])->name('marketplace.installation-status');
    Route::get('marketplace/installations/{subscription}/payment', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'payment'])->name('marketplace.payment');
    Route::post('marketplace/installations/{subscription}/payment', [\App\Http\Controllers\Dashboard\MarketplaceController::class, 'processPayment'])->name('marketplace.process-payment');
    
    // My Modules (Tenant Module Management)
    Route::get('my-modules', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'index'])->name('my-modules.index');
    Route::get('my-modules/{subscription}/settings', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'settings'])->name('my-modules.settings');
    Route::post('my-modules/{subscription}/settings', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'updateSettings'])->name('my-modules.update-settings');
    Route::get('my-modules/{subscription}/billing', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'billing'])->name('my-modules.billing');
    Route::post('my-modules/{subscription}/billing', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'changeBillingCycle'])->name('my-modules.change-billing');
    Route::get('my-modules/{subscription}/cancel', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'cancelForm'])->name('my-modules.cancel-form');
    Route::post('my-modules/{subscription}/cancel', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'cancel'])->name('my-modules.cancel');
    Route::get('my-modules/{subscription}/usage', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'usage'])->name('my-modules.usage');
    Route::get('my-modules/{subscription}/progress', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'progress'])->name('my-modules.progress');
    Route::post('my-modules/{subscription}/features/{feature}/toggle', [\App\Http\Controllers\Dashboard\MyModulesController::class, 'toggleFeature'])->name('my-modules.toggle-feature');
    
    // Module Invoices (Billing History & Payments)
    Route::get('invoices', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'index'])->name('invoices.index');
    Route::get('invoices/upcoming', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'upcoming'])->name('invoices.upcoming');
    Route::get('invoices/history', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'history'])->name('invoices.history');
    Route::get('invoices/{invoiceItem}', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'show'])->name('invoices.show');
    Route::get('invoices/invoice/{invoiceNumber}', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'showByNumber'])->name('invoices.show-by-number');
    Route::get('invoices/invoice/{invoiceNumber}/download', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'download'])->name('invoices.download');
    Route::post('invoices/{invoiceItem}/pay', [\App\Http\Controllers\Dashboard\InvoicesController::class, 'pay'])->name('invoices.pay');
});

// ── Tenant Status Pages (outside dashboard group for suspended/cancelled tenants) ──
Route::get('account/suspended', function () {
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    return view('errors.tenant_suspended', compact('tenant'));
})->name('tenant.suspended');

Route::post('account/suspended/payment', [\App\Http\Controllers\Tenant\SuspensionController::class, 'processPayment'])
    ->name('tenant.suspended.payment');

Route::post('account/suspended/contact', [\App\Http\Controllers\Tenant\SuspensionController::class, 'submitContact'])
    ->name('tenant.suspended.contact');

Route::get('account/suspended/payment-methods', [\App\Http\Controllers\Tenant\SuspensionController::class, 'getPaymentMethods'])
    ->name('tenant.suspended.payment-methods');

Route::get('account/cancelled', function () {
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    return view('errors.tenant_cancelled', compact('tenant'));
})->name('tenant.cancelled');

// ── Payment Gateway Webhooks ─────────────────────────────────────────────────
// PayStack Webhooks (no auth required - called by PayStack)
Route::post('webhooks/paystack', [\App\Http\Controllers\Webhooks\PayStackWebhookController::class, 'handle'])
    ->name('webhooks.paystack');

// PayStack Payment Callback (after customer completes payment)
Route::get('payments/callback', [\App\Http\Controllers\Webhooks\PayStackWebhookController::class, 'callback'])
    ->name('payments.callback');

// ── Phase 3: Tenant File Storage ──────────────────────────────────────────────
// Serve files from the current tenant's storage directory.
// URL:  /tenant-assets/{path}
// Maps: storage/app/tenants/{tenant_id}/{path}
Route::get('tenant-assets/{path}', function (string $path) {
    $fullPath = tenant_storage_path($path);

    if (!file_exists($fullPath) || !is_file($fullPath)) {
        abort(404);
    }

    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

    return response()->stream(function () use ($fullPath) {
        readfile($fullPath);
    }, 200, [
        'Content-Type'   => $mime,
        'Cache-Control'  => 'public, max-age=86400',
        'Content-Length' => filesize($fullPath),
    ]);
})->where('path', '.*')->middleware(['auth'])->name('tenant.assets');
