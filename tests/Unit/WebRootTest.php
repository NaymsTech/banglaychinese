<?php

namespace Tests\Unit;

use App\Support\WebRoot;
use Tests\TestCase;

class WebRootTest extends TestCase
{
    private string $tempRoot;

    private string $appRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempRoot = rtrim(sys_get_temp_dir(), '/\\').'/web-root-'.uniqid();
        $this->appRoot = $this->tempRoot.'/laravel-app';

        mkdir($this->appRoot, 0777, true);
        mkdir($this->tempRoot.'/public_html', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->tempRoot);

        parent::tearDown();
    }

    public function test_absolute_override_is_used_verbatim(): void
    {
        $override = $this->tempRoot.'/custom-web-root';

        $this->assertSame($override, WebRoot::resolve($this->appRoot, $override));
    }

    public function test_relative_override_is_resolved_against_the_base_path(): void
    {
        $this->assertSame(
            $this->appRoot.'/../public_html',
            WebRoot::resolve($this->appRoot, '../public_html'),
        );
    }

    public function test_sibling_public_html_with_a_front_controller_is_detected(): void
    {
        file_put_contents($this->tempRoot.'/public_html/index.php', '<?php');

        $this->assertSame(
            $this->tempRoot.'/public_html',
            WebRoot::resolve($this->appRoot),
        );
    }

    public function test_sibling_public_html_without_a_front_controller_falls_back_to_the_public_directory(): void
    {
        $this->assertSame(
            $this->appRoot.'/public',
            WebRoot::resolve($this->appRoot),
        );
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
