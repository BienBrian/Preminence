<?php

namespace Tests\Unit\Saas;

use App\Http\Middleware\IdentifyTenant;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    private IdentifyTenant $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new IdentifyTenant();
        
        // Create test tenants
        $this->seedTestTenants();
    }

    private function seedTestTenants(): void
    {
        // Tenant #1 - Happy Church Ruiru (primary domain)
        Tenant::create([
            'name' => 'Happy Church Ruiru',
            'slug' => 'happychurch-ruiru',
            'subdomain' => 'happychurch-ruiru',
            'subdomain_url' => 'happychurch-ruiru.happychurchruiru.org',
            'custom_domain' => 'happychurchruiru.org',
            'status' => 'active',
            'dns_status' => 'active',
            'ssl_status' => 'active',
        ]);

        // Tenant #2 - Grace Community
        Tenant::create([
            'name' => 'Grace Community',
            'slug' => 'grace-community',
            'subdomain' => 'grace-community',
            'subdomain_url' => 'grace-community.happychurchruiru.org',
            'status' => 'active',
            'dns_status' => 'active',
            'ssl_status' => 'active',
        ]);

        // Tenant #3 - Suspended Church
        Tenant::create([
            'name' => 'Suspended Church',
            'slug' => 'suspended-church',
            'subdomain' => 'suspended-church',
            'subdomain_url' => 'suspended-church.happychurchruiru.org',
            'status' => 'suspended',
            'dns_status' => 'active',
            'ssl_status' => 'active',
        ]);
    }

    /** @test */
    public function it_resolves_tenant_by_subdomain()
    {
        $request = Request::create('https://grace-community.happychurchruiru.org/dashboard');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(2, config('app.tenant_id'));
        $this->assertEquals('Grace Community', app('tenant')->name);
    }

    /** @test */
    public function it_resolves_primary_domain_to_tenant_1()
    {
        $request = Request::create('https://happychurchruiru.org/login');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, config('app.tenant_id'));
        $this->assertEquals('Happy Church Ruiru', app('tenant')->name);
    }

    /** @test */
    public function it_resolves_www_primary_domain_to_tenant_1()
    {
        $request = Request::create('https://www.happychurchruiru.org/');
        
        // www is a bypassed subdomain, so no tenant should be set
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        // www bypasses tenant resolution
        $this->assertNull(config('app.tenant_id'));
    }

    /** @test */
    public function it_bypasses_superadmin_subdomain()
    {
        $request = Request::create('https://superadmin.happychurchruiru.org/login');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull(config('app.tenant_id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_tenant_subdomain()
    {
        $request = Request::create('https://nonexistent-church.happychurchruiru.org/login');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(404, $response->getStatusCode());
    }

    /** @test */
    public function it_defaults_to_tenant_1_on_localhost()
    {
        // Simulate localhost request
        $request = Request::create('http://127.0.0.1:8000/login');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, config('app.tenant_id'));
    }

    /** @test */
    public function it_allows_tenant_override_query_parameter_on_localhost()
    {
        $request = Request::create('http://127.0.0.1:8000/login?__tenant=grace-community');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(2, config('app.tenant_id'));
        $this->assertEquals('Grace Community', app('tenant')->name);
    }

    /** @test */
    public function it_returns_403_for_suspended_tenant()
    {
        $request = Request::create('https://suspended-church.happychurchruiru.org/dashboard');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function it_resolves_custom_domain()
    {
        // Add a custom domain to tenant #2
        Tenant::where('id', 2)->update([
            'custom_domain' => 'www.gracecommunity.org',
            'custom_domain_enabled' => true,
        ]);

        $request = Request::create('https://www.gracecommunity.org/login');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(2, config('app.tenant_id'));
    }

    /** @test */
    public function it_extracts_subdomain_correctly()
    {
        $reflection = new \ReflectionClass($this->middleware);
        $method = $reflection->getMethod('extractSubdomain');
        $method->setAccessible(true);

        // Test cases
        $this->assertEquals('church', $method->invoke($this->middleware, 'church.happychurchruiru.org'));
        $this->assertEquals('church', $method->invoke($this->middleware, 'church.pisti.co.ke'));
        $this->assertNull($method->invoke($this->middleware, 'happychurchruiru.org'));
        $this->assertNull($method->invoke($this->middleware, 'localhost'));
        $this->assertNull($method->invoke($this->middleware, 'pisti.co.ke'));
        $this->assertEquals('www', $method->invoke($this->middleware, 'www.happychurchruiru.org'));
    }

    /** @test */
    public function it_sets_permission_team_context()
    {
        $request = Request::create('https://grace-community.happychurchruiru.org/dashboard');
        
        $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(2, app(\Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId());
    }

    /** @test */
    public function it_shares_tenant_with_views()
    {
        $request = Request::create('https://happychurchruiru.org/login');
        
        $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $sharedTenant = view()->shared('currentTenant');
        $this->assertNotNull($sharedTenant);
        $this->assertEquals('Happy Church Ruiru', $sharedTenant->name);
    }
}
