<?php

namespace App\Twig;

class Base64TwigExtension
{
    public static function image(string $asset): string
    {
        $extension = pathinfo($asset, PATHINFO_EXTENSION);
        $binary = file_get_contents($asset);

        return sprintf('data:image/%s;base64,%s', $extension, base64_encode($binary));
    }

    public static function font(string $asset): string
    {
        $binary = file_get_contents($asset);

        return sprintf('data:application/font-woff;charset=utf-8;base64,%s', base64_encode($binary));
    }
}