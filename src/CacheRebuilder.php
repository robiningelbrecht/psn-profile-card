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
            'assets' => [
                'avatar' => dirname(__DIR__) . '/assets/avatar.png',
                'flag' => dirname(__DIR__) . '/assets/flag.png',
                'ps_plus' => dirname(__DIR__) . '/assets/ps-plus.webp',
                'trophy' => [
                    'platinum' => dirname(__DIR__) . '/assets/trophy-platinum.png',
                    'gold' => dirname(__DIR__) . '/assets/trophy-gold.png',
                    'silver' => dirname(__DIR__) . '/assets/trophy-silver.png',
                    'bronze' => dirname(__DIR__) . '/assets/trophy-bronze.png',
                ],
            ],
            'fonts' => [
                [
                    'family' => 'Poppins',
                    'style' => 'normal',
                    'weight' => 200,
                    'src' => dirname(__DIR__) . '/assets/fonts/poppins-normal-200.woff2',
                ],
            ],
            'latest_trophies' => [
                [
                    'icon' => 'https://playstation-trophy-cards.robiningelbrecht.be/assets/profile/trophies/2S69db6a.png',
                    'title' => '1000 Talismans Shot',
                    'description' => 'Shoot 1,000 talismans.',
                    'grade' => dirname(__DIR__) . '/assets/trophy-bronze.png',
                    'rarity' => 'https://playstation-trophy-cards.robiningelbrecht.be/assets/images/rarity-sprite.png',
                    'earned_on' => '12/24/2021 1:17PM',
                ],
                [
                    'icon' => 'https://playstation-trophy-cards.robiningelbrecht.be/assets/profile/trophies/2S69db6a.png',
                    'title' => '1000 Talismans Shot',
                    'description' => 'Shoot 1,000 talismans.',
                    'grade' => dirname(__DIR__) . '/assets/trophy-bronze.png',
                    'rarity' => 'https://playstation-trophy-cards.robiningelbrecht.be/assets/images/rarity-sprite.png',
                    'earned_on' => '12/24/2021 1:17PM',
                ],
            ],
        ]);

        Cache::set($render);
    }
}