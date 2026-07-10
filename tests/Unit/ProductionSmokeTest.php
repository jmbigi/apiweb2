<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductionSmokeTest extends TestCase
{
    private string $baseUrl = 'https://web2.faristol.net';
    private string $apiBaseUrl = 'https://ios.faristol.net';
    private int $timeout = 15;

    private function httpGet(string $url, ?int $timeout = null): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_TIMEOUT => $timeout ?? $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ProductionSmokeTest/1.0',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $error = curl_error($ch);
        curl_close($ch);

        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        return [
            'code' => $httpCode,
            'content_type' => $contentType,
            'headers' => $headers,
            'body' => $body,
            'time' => $totalTime,
            'error' => $error,
        ];
    }

    private function assertHttp200(string $url, string $label): void
    {
        $result = $this->httpGet($url);
        $this->assertEquals(200, $result['code'], "$label returned {$result['code']}, expected 200");
        $this->assertEmpty($result['error'], "$label curl error: {$result['error']}");
    }

    public function test_homepage_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('Faristol', $result['body']);
        $this->assertStringContainsString('flutter_target', $result['body']);
        $this->assertStringContainsString('flutter.js', $result['body']);
        $this->assertNotEmpty($result['body']);
    }

    public function test_homepage_has_correct_meta_tags(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertStringContainsString('og:title', $result['body']);
        $this->assertStringContainsString('og:description', $result['body']);
        $this->assertStringContainsString('og:image', $result['body']);
        $this->assertStringContainsString('description', $result['body']);
        $this->assertStringContainsString('keywords', $result['body']);
        $this->assertStringContainsString('canonical', $result['body']);
    }

    public function test_homepage_has_manifest_link(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertStringContainsString('manifest.json', $result['body']);
    }

    public function test_homepage_response_time(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertLessThan(12.0, $result['time'], 'Homepage took too long to respond');
    }

    public function test_homepage_no_curl_errors(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertEmpty($result['error']);
    }

    public function test_flutter_js_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/flutter.js");
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('application/javascript', $result['content_type']);
        $this->assertStringContainsString('_flutter', $result['body']);
    }

    public function test_flutter_service_worker_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/flutter_service_worker.js");
        $this->assertEquals(200, $result['code']);
        $this->assertNotEmpty($result['body']);
    }

    public function test_main_dart_js_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/main.dart.js");
        $this->assertEquals(200, $result['code']);
        $this->assertNotEmpty($result['body']);
        $this->assertGreaterThan(100000, strlen($result['body']), 'main.dart.js seems too small');
    }

    public function test_manifest_json_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/manifest.json");
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('application/json', $result['content_type']);
        $this->assertStringContainsString('website', $result['body']);
    }

    public function test_favicon_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/favicon.png");
        $this->assertEquals(200, $result['code']);
        $this->assertNotEmpty($result['body']);
    }

    public function test_canvaskit_wasm_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/canvaskit/skwasm_heavy.wasm");
        $this->assertEquals(200, $result['code']);
        $this->assertGreaterThan(100000, strlen($result['body']));
    }

    public function test_canvaskit_js_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/canvaskit/wimp.js");
        $this->assertEquals(200, $result['code']);
        $this->assertNotEmpty($result['body']);
    }

    public function test_icons_return_200(): void
    {
        $this->assertHttp200("{$this->baseUrl}/web/icons/Icon-192.png", 'Icon-192.png');
        $this->assertHttp200("{$this->baseUrl}/web/icons/Icon-512.png", 'Icon-512.png');
    }

    public function test_flutter_bootstrap_returns_200(): void
    {
        $this->assertHttp200("{$this->baseUrl}/web/flutter_bootstrap.js", 'flutter_bootstrap.js');
    }

    public function test_version_json_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/web/version.json");
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('application/json', $result['content_type']);
        $this->assertStringContainsString('app_name', $result['body']);
    }

    public function test_score_page_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/score/Granada%20Op.%2047%20n%C2%BA1");
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('Granada', $result['body']);
    }

    public function test_sitemap_returns_200(): void
    {
        $this->assertHttp200("{$this->baseUrl}/sitemap", 'sitemap');
    }

    public function test_sitemap_list_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/list", 15);
        $this->assertEquals(200, $result['code']);
        $this->assertNotEmpty($result['body']);
        $this->assertStringContainsString('pdf', $result['body']);
    }

    public function test_styles_page_returns_200(): void
    {
        $result = $this->httpGet(
            "{$this->baseUrl}/styles/Nationalism/Granada%20Op.%2047%20n%C2%BA1"
        );
        $this->assertEquals(200, $result['code']);
    }

    public function test_instruments_page_returns_200(): void
    {
        $result = $this->httpGet(
            "{$this->baseUrl}/instruments/Alto%20Saxophone/Granada%20Op.%2047%20n%C2%BA1"
        );
        $this->assertEquals(200, $result['code']);
    }

    public function test_pdf_endpoint_returns_200(): void
    {
        $result = $this->httpGet(
            "{$this->baseUrl}/pdf/en/Granada%20Op.%2047%20n%C2%BA1",
            30
        );
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('application/pdf', $result['content_type']);
    }

    public function test_image_endpoint_returns_200(): void
    {
        $result = $this->httpGet(
            "{$this->baseUrl}/image/en/Granada%20Op.%2047%20n%C2%BA1"
        );
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('image/', $result['content_type']);
    }

    public function test_security_headers_present(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $headers = $result['headers'];

        $this->assertStringContainsString('samesite=lax', $headers);
        $this->assertStringContainsString('XSRF-TOKEN', $headers);
    }

    public function test_rate_limiting_headers_present(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertStringContainsString('X-RateLimit-Limit', $result['headers']);
        $this->assertStringContainsString('X-RateLimit-Remaining', $result['headers']);
    }

    public function test_ignition_endpoint_not_accessible(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/_ignition/health-check");
        $this->assertEquals(404, $result['code'], 'Ignition health endpoint should be disabled in production');
    }

    public function test_welcome_page_consecutive_requests(): void
    {
        $r1 = $this->httpGet("{$this->baseUrl}/", 15);
        $this->assertEquals(200, $r1['code'], 'First request failed');
        $r2 = $this->httpGet("{$this->baseUrl}/", 15);
        $this->assertEquals(200, $r2['code'], 'Second request failed');
    }

    public function test_api_style_music_list(): void
    {
        $result = $this->httpGet(
            "{$this->apiBaseUrl}/api/music-score/allmusic?type=musicStyle",
            15
        );
        if ($result['code'] === 200) {
            $json = json_decode($result['body'], true);
            $this->assertNotNull($json, 'API response is not valid JSON');
        } else {
            $this->markTestSkipped("API at {$this->apiBaseUrl} returned {$result['code']}");
        }
    }

    public function test_api_instruments_list(): void
    {
        $result = $this->httpGet(
            "{$this->apiBaseUrl}/api/instruments/list",
            15
        );
        if ($result['code'] === 200) {
            $json = json_decode($result['body'], true);
            $this->assertNotNull($json, 'API response is not valid JSON');
        } else {
            $this->markTestSkipped("API at {$this->apiBaseUrl} returned {$result['code']}");
        }
    }

    public function test_api_style_music_list_returns_200(): void
    {
        $this->assertHttp200(
            "{$this->apiBaseUrl}/api/style-music/list",
            'API style-music/list'
        );
    }

    public function test_static_html_page_returns_200(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/page/en/Granada%20Op.%2047%20n%C2%BA1");
        $this->assertEquals(200, $result['code']);
        $this->assertNotEmpty($result['body']);
    }

    public function test_all_static_assets_return_200(): void
    {
        $assets = [
            '/web/version.json',
            '/web/favicon.png',
            '/web/faristol_splash.jpg',
            '/web/manifest.json',
            '/web/index.html',
        ];
        foreach ($assets as $path) {
            $result = $this->httpGet("{$this->baseUrl}{$path}");
            $this->assertEquals(
                200,
                $result['code'],
                "$path returned {$result['code']}, expected 200"
            );
        }
    }

    public function test_no_php_errors_in_html(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertStringNotContainsString('PHP Warning', $result['body']);
        $this->assertStringNotContainsString('PHP Fatal error', $result['body']);
        $this->assertStringNotContainsString('PHP Notice', $result['body']);
        $this->assertStringNotContainsString('Fatal error', $result['body']);
        $this->assertStringNotContainsString('<b>Warning</b>', $result['body']);
        $this->assertStringNotContainsString('<b>Fatal error</b>', $result['body']);
    }

    public function test_app_debug_not_exposed(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/");
        $this->assertStringNotContainsString('APP_DEBUG', $result['body']);
        $this->assertStringNotContainsString('APP_KEY', $result['body']);
        $this->assertStringNotContainsString('DB_PASSWORD', $result['body']);
        $this->assertStringNotContainsString('DB_USERNAME', $result['body']);
    }

    public function test_non_existent_page_returns_404(): void
    {
        $result = $this->httpGet("{$this->baseUrl}/this-page-does-not-exist-12345");
        $this->assertEquals(404, $result['code']);
    }
}
