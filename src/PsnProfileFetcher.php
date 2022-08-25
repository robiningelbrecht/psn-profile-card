<?php

namespace App;

use GuzzleHttp\Client;

class PsnProfileFetcher
{
    public function __construct(
        private readonly Client $client,
    )
    {
    }

    public function getProfile(string $psnProfile): array
    {
        $response = $this->client->get('https://psnprofiles.com/' . $psnProfile);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Could not fetch profile ' . $psnProfile);
        }

        $content = $response->getBody()->getContents();

        $regexes = [
            'level' => '/<li class="icon-sprite level">(?<value>[\d]*)<\/li>/im',
            'levelProgress' => '/<div class="progress-bar level">[\s\S]*<div style="width: (?<value>[\d]*)%;"><\/div>[\s\S]*<\/div>/imU',
            'trophiesTotal' => '/<li class="total">[\s]*<span class="icon-sprite"><\/span>[\s]*(?<value>[\S]*)[\s]*<\/li>/im',
            'trophiesPlatinum' => '/<li class="platinum">[\s]*<span class="icon-sprite"><\/span>[\s]*(?<value>[\S]*)[\s]*<\/li>/im',
            'trophiesGold' => '/<li class="gold">[\s]*<span class="icon-sprite"><\/span>[\s]*(?<value>[\S]*)[\s]*<\/li>/im',
            'trophiesSilver' => '/<li class="silver">[\s]*<span class="icon-sprite"><\/span>[\s]*(?<value>[\S]*)[\s]*<\/li>/im',
            'trophiesBronze' => '/<li class="bronze">[\s]*<span class="icon-sprite"><\/span>[\s]*(?<value>[\S]*)[\s]*<\/li>/im',
        ];

        $matches = [];
        foreach ($regexes as $field => $regex) {
            if (!preg_match($regex, $content, $match)) {
                continue;
            }

            $matches[$field] = $match['value'];
        }

        return [
            'level' => (int)$matches['level'],
            'levelProgress' => (int)$matches['levelProgress'],
            'trophiesTotal' => (int)str_replace(',', '', $matches['trophiesTotal']),
            'trophiesPlatinum' => (int)str_replace(',', '', $matches['trophiesPlatinum']),
            'trophiesGold' => (int)str_replace(',', '', $matches['trophiesGold']),
            'trophiesSilver' => (int)str_replace(',', '', $matches['trophiesSilver']),
            'trophiesBronze' => (int)str_replace(',', '', $matches['trophiesBronze']),
        ];
    }

    public function getLatestTrophies(string $psnProfile): array
    {
        $latestTrophies = [];
        $response = $this->client->get('https://psnprofiles.com/' . $psnProfile . '/log');

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Could not fetch profile ' . $psnProfile);
        }

        preg_match_all('/<tr.*?>(?<trophies>[\s\S]*)<\/tr>/imU', $response->getBody()->getContents(), $rows);

        foreach ($rows['trophies'] as $trophy) {
            $regexes = [
                'title' => '/<a class="title" href="[\S]*">(?<value>.*?)<\/a>/im',
                'thumb' => '/<img class="trophy" src="https:\/\/i.psnprofiles.com\/games\/(?<value>[\S]*)"[\s\S]*\/>/im',
                'game' => '/<img class="game" src="[\S]*" title="(?<value>.*?)">/im',
                'grade' => '/<img title="[\S]*" src="\/lib\/img\/icons\/[\d]*-(?<value>.*?).png">/im',
                'rarity' => '/<span class="typo-bottom"><nobr>(?<value>(?!Achievers|Owners).*?)<\/nobr>/im',
            ];

            $matches = [];
            foreach ($regexes as $field => $regex) {
                if (!preg_match($regex, $trophy, $match)) {
                    continue;
                }

                $matches[$field] = $match['value'];
            }

            preg_match('/<span class="typo-top-date"><nobr>(?<day>.*?)([\d]*)<sup>[\S\s]*<\/sup>(?<monthYear>.*?)<\/nobr><\/span>/imU', $trophy, $date);
            preg_match('/<span class="typo-bottom-date"><nobr>(?<time>.*?)<\/nobr><\/span>/imU', $trophy, $time);

            $matches['earnedOn'] = \DateTimeImmutable::createFromFormat('d M Y h:i:s A', $date['day'] . $date['monthYear'] . ' ' . $time['time']);

            $latestTrophies[] = [
                'title' => html_entity_decode($matches['title']),
                'thumbnail' => 'https://i.psnprofiles.com/games/' . $matches['thumb'],
                'game' => html_entity_decode($matches['game']),
                'grade' => $matches['grade'],
                'rarity' => $matches['rarity'],
                'earnedOn' => $matches['earnedOn'],
            ];
        }

        return $latestTrophies;
    }

    public function getTrophyCabinet(string $psnProfile): array
    {
        $trophyCabinet = [];
        $response = $this->client->get('https://psnprofiles.com/' . $psnProfile);
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Could not fetch profile ' . $psnProfile);
        }

        preg_match_all('/<tr.*?>(?<trophies>[\s\S]*)<\/tr>/imU', $response->getBody()->getContents(), $rows);

        foreach ($rows['trophies'] as $game) {
            $regexes = [
                'title' => '/<a class="small-title" href="[\S]*">(?<value>.*?)<\/a>/im',
                'thumb' => '/<img src="https:\/\/i.psnprofiles.com\/games\/(?<value>[\S]*)" \/>/im',
                'game' => '/<a href="[\S]*" rel="nofollow">(?<value>[\S\s]*)<\/a>/imU',
                'grade' => '/<img src="\/lib\/img\/icons\/[\S]*" title="(?<value>[\S]*)" alt="[\S]*" \/>/im',
                'rarity' => '/<span class="typo-bottom"><nobr>(?<value>[\S]*)<\/nobr><\/span>/im',
            ];

            $matches = [];
            foreach ($regexes as $field => $regex) {
                if (!preg_match($regex, $game, $match)) {
                    continue;
                }

                $matches[$field] = $match['value'];
            }

            if (count($matches) != 5) {
                continue;
            }

            $trophyCabinet[] = [
                'title' => html_entity_decode($matches['title']),
                'thumbnail' => 'https://i.psnprofiles.com/games/' . $matches['thumb'],
                'game' => html_entity_decode($matches['game']),
                'grade' => $matches['grade'],
                'rarity' => $matches['rarity'],
            ];
        }

        return $trophyCabinet;
    }

    public function getPlayedGames(string $psnProfile): array
    {
        $playedGames = [];
        $response = $this->client->get('https://psnprofiles.com/' . $psnProfile . '?ajax=1&page=0');

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Could not fetch profile ' . $psnProfile);
        }
        $content = json_decode($response->getBody()->getContents(), true);

        if (empty($content['html'])) {
            throw new \RuntimeException('Could not fetch profile ' . $psnProfile);
        }

        preg_match_all('/<tr.*?>(?<games>[\s\S]*)<\/tr>/imU', $content['html'], $rows);

        foreach ($rows['games'] as $game) {
            $regexes = [
                'id' => '/href=[\S]*"\/trophies\/(?<value>[0-9]*)-[\S]*"/im',
                'title' => '/<a class="title"[\s\S]*>(?<value>.*?)<\/a>/im',
                'thumb' => '/<img src="https:\/\/i.psnprofiles.com\/games\/(?<value>[\S]*)" \/>/im',
                'hasObtainedPlatinum' => '/Platinum[\s]*in <b>(?<value>.*?)<\/b>/im',
                'trophiesTotal' => '/All[\s]*<b>(?<value>[\d]+)<\/b> Trophies/imU',
                'trophiesGold' => '/<span class="icon-sprite gold"><\/span><span>(?<value>[\d]+)<\/span>/imU',
                'trophiesSilver' => '/<span class="icon-sprite silver"><\/span><span>(?<value>[\d]+)<\/span>/imU',
                'trophiesBronze' => '/<span class="icon-sprite bronze"><\/span><span>(?<value>[\d]+)<\/span>/imU',
                'progress' => '/<div class=\"progress-bar\">[\s]*<span>(?<value>[\d]*)%<\/span>/im',
            ];

            $matches = [];
            foreach ($regexes as $field => $regex) {
                if (!preg_match($regex, $game, $match)) {
                    continue;
                }

                $matches[$field] = $match['value'];
            }

            if (0 !== count(array_diff($this->getRequiredRegexMatches(), array_keys($matches)))) {
                // Not all required regexes were successful skip.
                continue;
            }


            $playedGames[] = [
                'id' => $matches['id'],
                'title' => html_entity_decode($matches['title']),
                'thumbnail' => 'https://i.psnprofiles.com/games/' . $matches['thumb'],
                'hasObtainedPlatinum' => !empty($matches['hasObtainedPlatinum']),
                'trophiesTotal' => (int)$matches['trophiesTotal'],
                'trophiesGold' => (int)$matches['trophiesGold'],
                'trophiesSilver' => (int)$matches['trophiesSilver'],
                'trophiesBronze' => (int)$matches['trophiesBronze'],
                'progress' => (int)$matches['progress'],
            ];
        }

        return $playedGames;
    }

    private function getRequiredRegexMatches(): array
    {
        return [
            'id',
            'title',
            'thumb',
            'trophiesTotal',
            'trophiesGold',
            'trophiesSilver',
            'trophiesBronze',
        ];
    }
}