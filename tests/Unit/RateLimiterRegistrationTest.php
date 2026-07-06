<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimiterRegistrationTest extends TestCase
{
    public function test_global_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('global');
        $this->assertNotNull($limiter, 'global rate limiter no registrado');
    }

    public function test_sitemap_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('sitemap');
        $this->assertNotNull($limiter, 'sitemap rate limiter no registrado');
    }

    public function test_pdf_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('pdf');
        $this->assertNotNull($limiter, 'pdf rate limiter no registrado');
    }

    public function test_global_limiter_allows_120_per_minute(): void
    {
        $limiter = RateLimiter::limiter('global');
        $this->assertNotNull($limiter);
        $limit = $limiter(request());
        $this->assertInstanceOf(\Illuminate\Cache\RateLimiting\Limit::class, $limit);
        $this->assertEquals(120, $limit->maxAttempts);
    }

    public function test_sitemap_limiter_allows_30_per_minute(): void
    {
        $limiter = RateLimiter::limiter('sitemap');
        $this->assertNotNull($limiter);
        $limit = $limiter(request());
        $this->assertInstanceOf(\Illuminate\Cache\RateLimiting\Limit::class, $limit);
        $this->assertEquals(30, $limit->maxAttempts);
    }

    public function test_pdf_limiter_allows_30_per_minute(): void
    {
        $limiter = RateLimiter::limiter('pdf');
        $this->assertNotNull($limiter);
        $limit = $limiter(request());
        $this->assertInstanceOf(\Illuminate\Cache\RateLimiting\Limit::class, $limit);
        $this->assertEquals(30, $limit->maxAttempts);
    }

    public function test_limiter_keys_include_ip(): void
    {
        $request = request();
        $ip = $request->ip();

        $limiter = RateLimiter::limiter('global');
        $limit = $limiter($request);
        $this->assertStringContainsString($ip, $limit->key, 'La clave del limiter debe incluir la IP');
    }

    public function test_home_route_has_throttle_global_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $homeRoute = $routes->first(fn ($r) => $r->getName() === 'home');
        $this->assertNotNull($homeRoute);

        $middleware = $homeRoute->gatherMiddleware();
        $this->assertContains('throttle:global', $middleware, 'Route home debe tener throttle:global');
    }

    public function test_sitemap_route_has_throttle_sitemap_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $sitemapRoute = $routes->first(fn ($r) => $r->getName() === 'sitemap');
        $this->assertNotNull($sitemapRoute);

        $middleware = $sitemapRoute->gatherMiddleware();
        $this->assertContains('throttle:sitemap', $middleware, 'Route sitemap debe tener throttle:sitemap');
    }

    public function test_list_route_has_throttle_sitemap_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $listRoute = $routes->first(fn ($r) => $r->getName() === 'list');
        $this->assertNotNull($listRoute);

        $middleware = $listRoute->gatherMiddleware();
        $this->assertContains('throttle:sitemap', $middleware, 'Route list debe tener throttle:sitemap');
    }

    public function test_pdf_route_has_throttle_pdf_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $pdfRoute = $routes->first(fn ($r) => $r->getName() === 'getPdfByLangAndName');
        $this->assertNotNull($pdfRoute);

        $middleware = $pdfRoute->gatherMiddleware();
        $this->assertContains('throttle:pdf', $middleware, 'Route pdf debe tener throttle:pdf');
    }

    public function test_image_route_has_throttle_pdf_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $imageRoute = $routes->first(fn ($r) => $r->getName() === 'showImageByLangAndName');
        $this->assertNotNull($imageRoute);

        $middleware = $imageRoute->gatherMiddleware();
        $this->assertContains('throttle:pdf', $middleware, 'Route image debe tener throttle:pdf');
    }

    public function test_stats_route_has_auth_and_throttle_global_middleware(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $statsRoute = $routes->first(fn ($r) => $r->getName() === 'stats');
        $this->assertNotNull($statsRoute);

        $middleware = $statsRoute->gatherMiddleware();
        $this->assertContains('auth', $middleware, 'Route stats debe tener auth');
        $this->assertContains('throttle:global', $middleware, 'Route stats debe tener throttle:global');
    }
}
