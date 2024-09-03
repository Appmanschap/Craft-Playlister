<?php

namespace appmanschap\craftplaylister\services\clients;

use appmanschap\craftplaylister\Playlister;
use Craft;
use craft\helpers\App;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\YouTube;

class YoutubeClient implements PlaylistClient
{
    /**
     * @var GoogleClient
     */
    private GoogleClient $client;

    /**
     * @throws \yii\base\Exception
     */
    public function __construct()
    {
        $this->setupClient();
    }

    /**
     * @throws \yii\base\Exception
     */
    public function setupClient(): void
    {
        $siteName = Craft::$app->sites->getCurrentSite()->name ?? 'Site-name';

        $this->client = new GoogleClient();
        $this->client->setApplicationName("CraftCMS:Playlister:{$siteName}");

        // Warn if the API key isn't set.
        $youtubeApiKeyValue = Playlister::$plugin?->getSettings()->youtubeApiKey;
        $youtubeApiKey = (string) App::parseEnv($youtubeApiKeyValue);

        if (empty($youtubeApiKey)) {
            throw new Exception('No Youtube API key is set.');
        }

        $this->client->setDeveloperKey($youtubeApiKey);
    }

    /**
     * @return GoogleClient
     */
    public function getClient(): GoogleClient
    {
        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function getYouTubeService(): YouTube
    {
        return new YouTube($this->getClient());
    }
}
