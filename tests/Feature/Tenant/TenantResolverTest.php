<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Services\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantResolverTest extends TestCase
{
    // resolve a clinic from a subdomain
    public function test_it_resolves_clinic_from_subdomain(): void
    {
        $slug = 'resolver-'.Str::uuid();
        $clinic = Clinic::factory()->create(['slug' => $slug, 'active' => true]);
        $request = Request::create("http://{$slug}.clinika.test", 'GET');
        $resolved = app(TenantResolver::class)->resolve($request);
        $this->assertNotNull($resolved);
        $this->assertSame($clinic->id, $resolved->id);
    }

    // return null if a subdomain doesn't exist test
    public function test_it_returns_null_when_subdomain_does_not_exist(): void
    {
        $slug = 'inesistente-'.Str::uuid();
        $request = Request::create("http://{$slug}.clinika.test", 'GET');
        $resolved = app(TenantResolver::class)->resolve($request);
        $this->assertNull($resolved);
    }

    // return null if it's the central domain
    public function test_it_returns_null_for_non_tenant_host(): void
    {
        $request = Request::create('http://clinika.test', 'GET');
        $resolved = app(TenantResolver::class)->resolve($request);
        $this->assertNull($resolved);
    }

    // soft delete clinic resolved
    public function test_it_can_resolve_soft_deleted_clinic(): void
    {
        $slug = 'resolver-deleted-'.Str::uuid();
        $clinic = Clinic::factory()->create(['slug' => $slug, 'active' => true]);
        $clinic->delete();
        $request = Request::create("http://{$slug}.clinika.test", 'GET');
        $resolved = app(TenantResolver::class)->resolve($request);
        $this->assertNotNull($resolved);
        $this->assertSame($clinic->id, $resolved->id);
        $this->assertTrue($resolved->trashed());
    }

    // resolve correct clinic where multiple tenant exists test
    public function test_it_resolves_the_correct_clinic_when_multiple_tenants_exist(): void
    {
        $slugA = 'tenant-a-'.Str::uuid();
        $slugB = 'tenant-b-'.Str::uuid();
        $clinicA = Clinic::factory()->create(['slug' => $slugA, 'active' => true]);
        $clinicB = Clinic::factory()->create(['slug' => $slugB, 'active' => true]);
        $requestA = Request::create("http://{$slugA}.clinika.test", 'GET');
        $requestB = Request::create("http://{$slugB}.clinika.test", 'GET');
        $resolver = app(TenantResolver::class);
        $resolvedA = $resolver->resolve($requestA);
        $resolvedB = $resolver->resolve($requestB);
        $this->assertNotNull($resolvedA);
        $this->assertNotNull($resolvedB);
        $this->assertSame($clinicA->id, $resolvedA->id);
        $this->assertSame($clinicB->id, $resolvedB->id);
        $this->assertNotSame($resolvedA->id, $resolvedB->id);
    }

    // resolve correct clinic for certain subdomain test (a -> a, b -> b, not a -> b)
    public function test_it_does_not_resolve_a_clinic_for_another_subdomain(): void
    {
        $clinicSlug = 'tenant-a-'.Str::uuid();
        $requestSlug = 'tenant-b-'.Str::uuid();
        Clinic::factory()->create(['slug' => $clinicSlug, 'active' => true]);
        $request = Request::create("http://{$requestSlug}.clinika.test", 'GET');
        $resolved = app(TenantResolver::class)->resolve($request);
        $this->assertNull($resolved);
    }

    // resolve inactive clinic when the slug matches test
    public function test_it_resolves_an_inactive_clinic_when_the_slug_matches(): void
    {
        $slug = 'tenant-inactive-'.Str::uuid();
        $clinic = Clinic::factory()->create(['slug' => $slug, 'active' => false]);
        $request = Request::create("http://{$slug}.clinika.test", 'GET');
        $resolved = app(TenantResolver::class)->resolve($request);
        $this->assertNotNull($resolved);
        $this->assertSame($clinic->id, $resolved->id);
        $this->assertFalse($resolved->active);
    }

    // return the same clinic when searched for same subdomain
    public function test_it_returns_the_same_clinic_for_repeated_resolution_of_the_same_subdomain(): void
    {
        $slug = 'tenant-test-'.Str::uuid();
        $clinic = Clinic::factory()->create(['slug' => $slug, 'active' => true]);
        $request = Request::create("http://{$slug}.clinika.test", 'GET');
        $resolver = app(TenantResolver::class);
        $firstResolved = $resolver->resolve($request);
        $secondResolved = $resolver->resolve($request);
        $this->assertNotNull($firstResolved);
        $this->assertNotNull($secondResolved);
        $this->assertSame($clinic->id, $firstResolved->id);
        $this->assertSame($clinic->id, $secondResolved->id);
        $this->assertSame($firstResolved->id, $secondResolved->id);
    }
}
