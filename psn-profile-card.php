<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Ahc\Cli\Application;
use App\CacheRebuilder;
use App\Twig\Base64TwigExtension;
use Psr\Container\ContainerInterface;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    FilesystemLoader::class => DI\create(FilesystemLoader::class)->constructor(__DIR__ . '/templates'),
    TwigEnvironment::class => function (ContainerInterface $container) {
        $twig = new TwigEnvironment($container->get(FilesystemLoader::class));
        $twig->addFunction(new TwigFunction('image64', [Base64TwigExtension::class, 'image']));
        $twig->addFunction(new TwigFunction('font64', [Base64TwigExtension::class, 'font']));

        return $twig;
    },
]);
$container = $builder->build();

$app = new Application('PSN Profile card', '0.0.1');

$app
    ->command('cache:rebuild', 'Rebuilds cache of PSN Profile card')
    ->action(function () use ($container) {
        $cacheRebuilder = $container->get(CacheRebuilder::class);
        $cacheRebuilder->rebuild();
    });

$app->handle($_SERVER['argv']);