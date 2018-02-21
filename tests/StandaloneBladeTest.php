<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mejta\StandaloneBlade;
use Mejta\StandaloneBladeException;

final class StandaloneBladeTest extends TestCase
{
    private $views = __DIR__ . '/views';
    private $cache = __DIR__ . '/cache';

    protected function setUp() {
        $this->mkdir($this->cache);
    }

    protected function tearDown() {
        $this->rmdir($this->cache);
    }

    public function testCanCreateNewInstance() {
        $this->assertInstanceOf(
            StandaloneBlade::class,
            new StandaloneBlade([$this->views], $this->cache)
        );
    }

    public function testChecksValidViewDirectory() {
        $this->expectException(StandaloneBladeException::class);
        new StandaloneBlade(['/wrong/view/directory'], $this->cache);
    }

    public function testChecksValidCacheDirectory() {
        $this->expectException(StandaloneBladeException::class);
        new StandaloneBlade([$this->views], '/wrong/cache/directory');
    }

    public function testCanRenderTemplate() {
        $blade = new StandaloneBlade([$this->views], $this->cache);

        $this->assertEquals(
            'test-basic',
            trim($blade->render('test-basic'))
        );
    }

    public function testCanRenderTemplateWithParam() {
        $blade = new StandaloneBlade([$this->views], $this->cache);

        $this->assertEquals(
            'test-param',
            trim($blade->render('test-param', ['param' => 'param']))
        );
    }

    public function testCanCreateCustomDirective() {
        $blade = new StandaloneBlade([$this->views], $this->cache);

        $blade->directive('test', function($expression) {
            return "<?php echo $expression; ?>";
        });

        $this->assertEquals(
            'test-directive',
            trim($blade->render('test-directive'))
        );
    }

    public function testCanCreateCustomIf() {
        $blade = new StandaloneBlade([$this->views], $this->cache);

        $blade->if('test', function($expression) {
            return ($expression === 'test-1');
        });

        $this->assertEquals(
            'test-1',
            trim($blade->render('test-if'))
        );
    }

    public function testCanCreateCustomElseIf() {
        $blade = new StandaloneBlade([$this->views], $this->cache);

        $blade->if('test', function($expression) {
            return ($expression === 'test-2');
        });

        $this->assertEquals(
            'test-2',
            trim($blade->render('test-if'))
        );
    }

    public function testCanCreateCustomElse() {
        $blade = new StandaloneBlade([$this->views], $this->cache);

        $blade->if('test', function($expression) {
            return ($expression === 'test-3');
        });

        $this->assertEquals(
            'test-else',
            trim($blade->render('test-if'))
        );
    }

    public function testCanShareVariables() {
        $blade = new StandaloneBlade([$this->views], $this->cache);
        $blade->share('shared', 'shared-value');

        $this->assertEquals(
            'test-shared-value',
            trim($blade->render('test-share'))
        );
    }

    private function mkdir($dirPath) {
        mkdir($dirPath, 0777, true);
    }

    private function rmdir($dirPath) {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        
        $files = glob($dirPath . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dirPath);
    }
}

