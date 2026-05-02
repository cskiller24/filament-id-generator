<?php

namespace Cskiller\FilamentIdGenerator\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class DebugTest extends BaseTestCase
{
    public function test_no_debug_functions_are_used(): void
    {
        $srcDir = __DIR__ . '/../src';
        $debugFunctions = ['dd(', 'dump(', 'ray('];
        $violations = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcDir)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            foreach ($debugFunctions as $fn) {
                if (str_contains($content, $fn)) {
                    $violations[] = $file->getPathname() . " uses {$fn}";
                }
            }
        }

        $this->assertEmpty($violations, implode("\n", $violations));
    }
}
