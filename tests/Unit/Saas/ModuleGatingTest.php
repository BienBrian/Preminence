<?php

namespace Tests\Unit\Saas;

use App\Http\Middleware\CheckModule;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ModuleGatingTest extends TestCase
{
    use RefreshDatabase;

    private CheckModule $middleware;
    private ModuleService $moduleService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->moduleService = new ModuleService();
        $this->middleware = new CheckModule($this->moduleService);
        
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Church',
            'slug' => 'test-church',
            'status' => 'active',
        ]);

        config(['app.tenant_id' => $tenant->id]);

        // Enable some modules
        TenantModule::create([
            'tenant_id' => $tenant->id,
            'module' => 'finance',
            'is_enabled' => true,
        ]);

        TenantModule::create([
            'tenant_id' => $tenant->id,
            'module' => 'people',
            'is_enabled' => true,
        ]);

        // Disable some modules
        TenantModule::create([
            'tenant_id' => $tenant->id,
            'module' => 'shop',
            'is_enabled' => false,
        ]);
    }

    /** @test */
    public function it_allows_access_when_module_is_enabled()
    {
        $request = Request::create('https://test-church.happychurchruiru.org/dashboard/finance');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'finance');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_blocks_access_when_module_is_disabled()
    {
        $request = Request::create('https://test-church.happychurchruiru.org/dashboard/shop');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'shop');

        $this->assertEquals(302, $response->getStatusCode()); // Redirect to billing
        $this->assertStringContainsString('billing/module-locked', $response->headers->get('Location'));
    }

    /** @test */
    public function it_returns_json_403_for_api_requests()
    {
        $request = Request::create(
            'https://test-church.happychurchruiru.org/api/shop/products',
            'GET',
            [],
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'shop');

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('module_disabled', $data['error']);
        $this->assertEquals('shop', $data['module']);
    }

    /** @test */
    public function it_allows_access_when_no_tenant_context()
    {
        config(['app.tenant_id' => null]);
        
        $request = Request::create('https://superadmin.happychurchruiru.org/dashboard');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'finance');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function module_service_correctly_checks_enabled_status()
    {
        $this->assertTrue($this->moduleService->isEnabled('finance'));
        $this->assertTrue($this->moduleService->isEnabled('people'));
        $this->assertFalse($this->moduleService->isEnabled('shop'));
        $this->assertFalse($this->moduleService->isEnabled('nonexistent'));
    }

    /** @test */
    public function module_service_caches_results()
    {
        $tenantId = config('app.tenant_id');
        $cacheKey = "tenant_{$tenantId}_module_finance";
        
        // Clear cache
        cache()->forget($cacheKey);
        $this->assertFalse(cache()->has($cacheKey));
        
        // First call should cache
        $this->moduleService->isEnabled('finance');
        $this->assertTrue(cache()->has($cacheKey));
        
        // Subsequent calls should use cache
        $this->assertTrue($this->moduleService->isEnabled('finance'));
    }

    /** @test */
    public function module_service_flush_cache_works()
    {
        $tenantId = config('app.tenant_id');
        
        // Populate cache
        $this->moduleService->isEnabled('finance');
        $this->moduleService->isEnabled('people');
        
        // Flush specific module
        $this->moduleService->flushCache($tenantId, 'finance');
        $this->assertFalse(cache()->has("tenant_{$tenantId}_module_finance"));
        
        // Other module should still be cached
        $this->assertTrue(cache()->has("tenant_{$tenantId}_module_people"));
        
        // Flush all
        $this->moduleService->flushCache($tenantId);
        $this->assertFalse(cache()->has("tenant_{$tenantId}_module_people"));
    }

    /** @test */
    public function module_service_returns_enabled_modules_list()
    {
        $enabled = $this->moduleService->enabledModules();
        
        $this->assertContains('finance', $enabled);
        $this->assertContains('people', $enabled);
        $this->assertNotContains('shop', $enabled);
    }

    /** @test */
    public function global_helper_function_works()
    {
        $this->assertTrue(module('finance'));
        $this->assertTrue(module('people'));
        $this->assertFalse(module('shop'));
    }

    /** @test */
    public function it_returns_correct_module_labels()
    {
        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('moduleLabel');
        $method->setAccessible(true);

        $this->assertEquals('Finance & Giving', $method->invoke($this->middleware, 'finance'));
        $this->assertEquals('SMS Messaging', $method->invoke($this->middleware, 'sms'));
        $this->assertEquals('UnknownModule', $method->invoke($this->middleware, 'UnknownModule'));
    }

    /** @test */
    public function tenant_can_enable_module()
    {
        $tenant = Tenant::first();
        
        $this->assertFalse($tenant->hasModule('shop'));
        
        $tenant->enableModule('shop');
        
        $this->assertTrue($tenant->hasModule('shop'));
        $this->assertTrue($this->moduleService->isEnabled('shop'));
    }

    /** @test */
    public function tenant_can_disable_module()
    {
        $tenant = Tenant::first();
        
        $this->assertTrue($tenant->hasModule('finance'));
        
        $tenant->disableModule('finance');
        
        $this->assertFalse($tenant->hasModule('finance'));
        $this->assertFalse($this->moduleService->isEnabled('finance'));
    }

    /** @test */
    public function disabling_module_flushes_cache()
    {
        $tenant = Tenant::first();
        $tenantId = $tenant->id;
        
        // Populate cache
        $this->moduleService->isEnabled('finance');
        $this->assertTrue(cache()->has("tenant_{$tenantId}_module_finance"));
        
        // Disable module
        $tenant->disableModule('finance');
        
        // Cache should be flushed
        $this->assertFalse(cache()->has("tenant_{$tenantId}_module_finance"));
    }
}
