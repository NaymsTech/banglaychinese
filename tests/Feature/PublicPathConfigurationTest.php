<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class PublicPathConfigurationTest extends TestCase
{
    private ?string $webRoot = null;

    protected function tearDown(): void
    {
        if ($this->webRoot !== null) {
            $this->deleteDirectory($this->webRoot);
            $this->clearPublicPathOverride();
        }

        parent::tearDown();
    }

    public function test_configured_web_root_hosts_the_public_path_and_vite_manifest(): void
    {
        $deployRoot = rtrim(sys_get_temp_dir(), '/\\').'/web-root-config-'.uniqid();
        $this->webRoot = $deployRoot.'/public_html';

        mkdir($this->webRoot.'/build', 0777, true);

        file_put_contents($this->webRoot.'/build/manifest.json', json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app-test-123.js',
                'isEntry' => true,
                'src' => 'resources/js/app.js',
            ],
        ], JSON_THROW_ON_ERROR));

        $this->setPublicPathOverride($this->webRoot);
        $this->refreshApplication();

        // The production layout (laravel-app/ next to public_html/) makes the
        // framework resolve every public-path consumer into the sibling web root.
        $this->assertSame($this->webRoot, public_path());
        $this->assertSame($this->webRoot, $this->app->make('path.public'));
        $this->assertSame($this->webRoot.'/storage', public_path('storage'));

        // Regression: Vite must read build/manifest.json from the configured web
        // root instead of <base>/public/build/manifest.json, which caused the
        // HTTP 500 (ViteManifestNotFoundException) on the live site.
        $this->assertStringContainsString(
            '/build/assets/app-test-123.js',
            Vite::asset('resources/js/app.js'),
        );
    }

    public function test_default_layout_keeps_the_standard_public_directory(): void
    {
        $this->assertSame($this->app->basePath().'/public', public_path());
    }

    private function setPublicPathOverride(string $path): void
    {
        putenv('APP_PUBLIC_PATH='.$path);
        $_ENV['APP_PUBLIC_PATH'] = $path;
        $_SERVER['APP_PUBLIC_PATH'] = $path;
    }

    private function clearPublicPathOverride(): void
    {
        putenv('APP_PUBLIC_PATH');
        unset($_ENV['APP_PUBLIC_PATH'], $_SERVER['APP_PUBLIC_PATH']);
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }
}
