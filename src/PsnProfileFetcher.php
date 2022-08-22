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

    public function getPlayedGames($psnProfile): array
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