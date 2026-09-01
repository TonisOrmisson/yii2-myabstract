<?php

namespace andmemasin\myabstract;

class SourceBoundaryTest extends \Codeception\Test\Unit
{
    public function testProductionDoesNotReferenceMyArrayHelper(): void
    {
        $sourcePath = dirname(__DIR__, 2) . '/src';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourcePath)) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            $this->assertIsString($source);
            $this->assertStringNotContainsString(
                'andmemasin\\helpers\\MyArrayHelper',
                $source,
                $file->getPathname()
            );
        }
    }

    public function testHelpersDependencyRemainsForDateHelper(): void
    {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/traits/MyActiveTrait.php');

        $this->assertArrayHasKey('andmemasin/yii2-helpers', $composer['require']);
        $this->assertStringContainsString('andmemasin\\helpers\\DateHelper', $source);
    }
}
