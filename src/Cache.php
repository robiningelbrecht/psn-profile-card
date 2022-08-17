<?php

namespace App;

class Cache
{
    private static function file(): string
    {
        return dirname(__DIR__) . '/api/cache';
    }

    public static function get(): string
    {
        if (!file_exists(static::file())) {
            throw new \RuntimeException('Cache is empty');
        }
        return file_get_contents(static::file());
    }

    public static function set(string $content): string
    {
        return file_put_contents(static::file(), $content);
    }
}