<?php

namespace App;

class Helper
{
    public static function toValidImageName(string $name): string
    {
        return str_replace(' ', '-', strtolower($name));
    }
}