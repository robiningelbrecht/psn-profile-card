<?php

namespace App;

use App\Twig\Base64TwigExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;

class CacheRebuilder
{
    public function __construct(
        private Environment $twig
    )
    {
    }

    public function rebuild(): void
    {
        $template = $this->twig->load('card.html.twig');
        $render = $template->render([
            'profile' => [
                'name' => 'Robin Ingelbrecht',
                'level' => 531,
                'level_progress' => 41,
                'trophies' => [
                    'total' => 6087,
                    'platinum' => 233,
                    'gold' => 2193,
                    'silver' => 1325,
                    'bronze' => 2336,
                ],
            ],
            'assets' => [
                'avatar' => dirname(__DIR__) . '/assets/avatar.png',
                'flag' => dirname(__DIR__) . '/assets/flag.png',
                'ps_plus' => dirname(__DIR__) . '/assets/ps-plus.webp',
                'trophy' => [
                    'platinum' => dirname(__DIR__) . '/assets/trophies/platinum.png',
                    'gold' => dirname(__DIR__) . '/assets/trophies/gold.png',
                    'silver' => dirname(__DIR__) . '/assets/trophies/silver.png',
                    'bronze' => dirname(__DIR__) . '/assets/trophies/bronze.png',
                ],
                'level' => dirname(__DIR__) . '/assets/levels/500-599.png',
            ],
            // https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap
            'fonts' => [
                [
                    'family' => 'Poppins',
                    'style' => 'normal',
                    'weight' => 200,
                    'src' => dirname(__DIR__) . '/assets/fonts/poppins-normal-200.woff2',
                ],
                [
                    'family' => 'Poppins',
                    'style' => 'normal',
                    'weight' => 400,
                    'src' => dirname(__DIR__) . '/assets/fonts/poppins-normal-400.woff2',
                ],
            ],
            'latest_trophies' => [
                [
                    'icon' => 'https://playstation-trophy-cards.robiningelbrecht.be/assets/profile/trophies/2S69db6a.png',
                    'title' => '1000 Talismans Shot',
                    'description' => 'Shoot 1,000 talismans.',
                    'game' => 'The Pathless',
                    'grade' => dirname(__DIR__) . '/assets/trophies/bronze.png',
                    'rarity' => dirname(__DIR__) . '/assets/rarity/ultra-rare.png',
                    'earned_on' => '12/24/2021 1:17PM',
                ],
                [
                    'icon' => 'https://playstation-trophy-cards.robiningelbrecht.be/assets/profile/trophies/2S69db6a.png',
                    'title' => '1000 Talismans Shot',
                    'description' => 'Shoot 1,000 talismans.',
                    'game' => 'The Pathless',
                    'grade' => dirname(__DIR__) . '/assets/trophies/bronze.png',
                    'rarity' => dirname(__DIR__) . '/assets/rarity/rare.png',
                    'earned_on' => '12/24/2021 1:17PM',
                ],
            ],
            'games_played' => [
                [
                    'icon' => 'https://i.psnprofiles.com/games/cae1c8/Mc61b6c.png',
                    'title' => 'Yakuza: Like A Dragon',
                    'progress' => 20,
                    'trophies' => [
                        'platinum' => 0,
                        'gold' => 1,
                        'silver' => 2,
                        'bronze' => 3,
                    ],
                ],
            ],
            'rarest_trophies' => [
                [
                    'icon' => 'https://i.psnprofiles.com/games/4d4c0b/trophies/17M070c1d.png',
                    'title' => 'Blue series clear',
                    'description' => 'Get a silver medal on every blue track.',
                    'game' => 'Track Mania: Turbo',
                    'grade' => dirname(__DIR__) . '/assets/trophies/silver.png',
                    'rarity' => dirname(__DIR__) . '/assets/rarity/ultra-rare.png',
                    'earned_on' => '12/24/2021 1:17PM',
                ],
            ],
        ]);

        Cache::set($render);
    }
}