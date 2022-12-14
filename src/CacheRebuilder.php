<?php

namespace App;

use App\Twig\Base64TwigExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;

class CacheRebuilder
{
    private const PROFILE_NAME = 'Fluttezuhher';
    private const ROWS_PER_SLIDE = 3;

    public function __construct(
        private Environment $twig,
        private PsnProfileFetcher $psnProfileFetcher,
    )
    {
    }

    public function rebuild(): void
    {
        $profile = $this->psnProfileFetcher->getProfile(self::PROFILE_NAME);
        $gamesPlayed = array_slice($this->psnProfileFetcher->getPlayedGames(self::PROFILE_NAME), 0, self::ROWS_PER_SLIDE);
        $latestTrophies = array_slice($this->psnProfileFetcher->getLatestTrophies(self::PROFILE_NAME), 0, self::ROWS_PER_SLIDE);
        $trophyCabinet = array_slice($this->psnProfileFetcher->getTrophyCabinet(self::PROFILE_NAME), 0, self::ROWS_PER_SLIDE);

        $templateVariables = [
            'profile' => [
                'name' => 'Robin Ingelbrecht',
                'level' => $profile['level'],
                'level_progress' => $profile['levelProgress'],
                'trophies' => [
                    'total' => $profile['trophiesTotal'],
                    'platinum' => $profile['trophiesPlatinum'],
                    'gold' => $profile['trophiesGold'],
                    'silver' => $profile['trophiesSilver'],
                    'bronze' => $profile['trophiesBronze'],
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
                'level' => $profile['level'] >= 999 ? dirname(__DIR__) . '/assets/levels/1000.png' : dirname(__DIR__) . '/assets/levels/' . (floor($profile['level'] / 100) * 100) . '-' . ((ceil($profile['level'] / 100) * 100) - 1) . '.png',
            ],
            // https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap
            'fonts' => [
                [
                    'family' => 'Poppins',
                    'style' => 'normal',
                    'weight' => 200,
                    'src' => dirname(__DIR__) . '/assets/fonts/poppins-normal-200.woff2',
                ],
            ],
            'latest_trophies' => array_map(fn(array $trophy) => [
                'icon' => $trophy['thumbnail'],
                'title' => $trophy['title'],
                'game' => $trophy['game'],
                'grade' => dirname(__DIR__) . '/assets/trophies/' . Helper::toValidImageName($trophy['grade']) . '.png',
                'rarity' => dirname(__DIR__) . '/assets/rarity/' . Helper::toValidImageName($trophy['rarity']) . '.png',
                'earned_on' => $trophy['earnedOn']->format('d-m-Y H:iA'),
            ], $latestTrophies),
            'games_played' => array_map(fn(array $game) => [
                'icon' => $game['thumbnail'],
                'title' => $game['title'],
                'progress' => $game['progress'],
                'trophies' => [
                    'platinum' => (int)$game['hasObtainedPlatinum'],
                    'gold' => $game['trophiesGold'],
                    'silver' => $game['trophiesSilver'],
                    'bronze' => $game['trophiesBronze'],
                ],
            ], $gamesPlayed),
            'trophy_cabinet' => array_map(fn(array $trophy) => [
                'icon' => $trophy['thumbnail'],
                'title' => $trophy['title'],
                'game' => $trophy['game'],
                'grade' => dirname(__DIR__) . '/assets/trophies/' . Helper::toValidImageName($trophy['grade']) . '.png',
                'rarity' => dirname(__DIR__) . '/assets/rarity/' . Helper::toValidImageName($trophy['rarity']) . '.png',
            ], $trophyCabinet),
        ];

        $template = $this->twig->load('svg.html.twig');
        $render = $template->render($templateVariables);
        Cache::forSvg()->set($render);

        $template = $this->twig->load('svg-minimal.html.twig');
        $render = $template->render($templateVariables);
        Cache::forSvgMinimal()->set($render);

        $template = $this->twig->load('debug.html.twig');
        $render = $template->render($templateVariables);
        Cache::forDebug()->set($render);
    }
}