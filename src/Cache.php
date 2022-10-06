<?php

namespace App;

class Cache
{
    private const SVG_FILE = 'cache-svg';
    private const SVG_FILE_MINIMAL = 'cache-svg-minimal';
    private const DEBUG_FILE = 'cache-debug';
    private string $file;

    private function __construct(
        string $file
    )
    {
        $this->file = dirname(__DIR__) . '/api/' . $file;
    }

    public function get(): string
    {
        if (!file_exists($this->file)) {
            throw new \RuntimeException('Cache is empty');
        }
        return file_get_contents($this->file);
    }

    public function set(string $content): string
    {
        return file_put_contents($this->file, $content);
    }

    public static function forSvg(): self
    {
        return new self(self::SVG_FILE);
    }


    public static function forSvgMinimal(): self
    {
        return new self(self::SVG_FILE_MINIMAL);
    }

    public static function forDebug(): self
    {
        return new self(self::DEBUG_FILE);
    }
}