<?php

namespace appmanschap\youtubeplaylistimporter\services;

use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use appmanschap\youtubeplaylistimporter\elements\Video as VideoElement;
use appmanschap\youtubeplaylistimporter\services\clients\PlaylistClient;
use appmanschap\youtubeplaylistimporter\services\clients\YoutubeClient;
use Craft;
use DateTime;
use Google\Service\Exception;
use Google\Service\YouTube\PlaylistItem as YouTubePlaylistItem;
use Google\Service\YouTube\Video as YoutubeVideo;
use Google\Service\YouTube\VideoSnippet as YoutubeVideoSnippet;
use Throwable;
use yii\base\Component;

/**
 * Playlist Import service
 */
class PlaylistImport extends Component
{
    private PlaylistClient $client;

    private ?string $nextPageToken = null;

    /**
     * @var bool
     */
    private bool $firstImport = true;

    /**
     * @var int
     */
    private int $maxResults = 50;

    /**
     * @var int
     */
    private int $limit = 100;

    /**
     * @param  PlaylistElement  $playlist
     * @return void
     * @throws Exception
     */
    public function import(PlaylistElement $playlist): void
    {
        if ($this->firstImport === false && !$this->nextPageToken) {
            # Stop importing when there is not a next page to retrieve
            return;
        }

        $this->setClient();

        $playlistItems = $this->getPlaylistItems();
        $videos = $this->getVideosByPlaylistItems($playlistItems);

        $this->createVideoElements($playlist, $playlistItems, $videos);

        $this->firstImport = false;

        while ($this->nextPageToken) {
            $this->import($playlist);
        }
    }

    private function setClient()
    {
        $this->client = new YoutubeClient();
    }

    /**
     * @return array<int, YouTubePlaylistItem>
     * @throws Exception
     */
    private function getPlaylistItems(): array
    {
        $options = [
            'playlistId' => 'PLMBgyT0oxOrsDdPyqKEVwdYt-yPgGyFs1', # @TODO: Replace by actual playlistId
            'maxResults' => $this->maxResults,
        ];

        if ($this->nextPageToken) {
            $options['pageToken'] = $this->nextPageToken;
        }

        $results = $this->client->getYouTubeService()->playlistItems->listPlaylistItems('contentDetails', $options);

        $this->nextPageToken = $results->getNextPageToken();

        return $results->getItems();
    }

    /**
     * @param  array<int, YouTubePlaylistItem>  $playlistItems
     * @throws Exception
     */
    private function getVideosByPlaylistItems(array $playlistItems): array
    {
        $videoListResponse = $this->client->getYouTubeService()->videos->listVideos([
            'contentDetails',
            'localizations',
            'snippet'
        ], [
            'id' => array_map(static fn ($playlistItem) => $playlistItem->getContentDetails()->getVideoId(), $playlistItems)
        ]);

        return $videoListResponse->getItems();
    }

    /**
     * @param  PlaylistElement  $playlist
     * @param  array<int, YouTubePlaylistItem>  $playlistItems
     * @param  array<int, YoutubeVideo>  $items
     * @return void
     */
    private function createVideoElements(PlaylistElement $playlist, array $playlistItems, array $videos)
    {
        foreach ($playlistItems as $playlistItem) {
            /** @var YouTubePlaylistItem $playlistItem */
            $videoId = $playlistItem->getContentDetails()->getVideoId();
            $youtubeVideos = array_values(array_filter($videos, static fn (YoutubeVideo $video) => $video->id === $videoId));

            if (empty($youtubeVideos)) {
                continue;
            }

            /** @var YoutubeVideoSnippet $youtubeVideoSnippet */
            $youtubeVideoSnippet = $youtubeVideos[0]->getSnippet();

            $video = VideoElement::find()->where([
                'videoId' => $videoId
            ])->one();

            if (null === $video) {
                $video = new VideoElement();
            }

            $video->videoId = $videoId;
            $video->title = $youtubeVideoSnippet->title;
            $video->description = $youtubeVideoSnippet->description;
            $video->datePublished = new DateTime($youtubeVideoSnippet->datePublished);
            $video->playlistId = 'PLMBgyT0oxOrsDdPyqKEVwdYt-yPgGyFs1'; # @TODO: Replace by actual playlistId
            $video->channelId = $youtubeVideoSnippet->channelId;
            $video->channelTitle = $youtubeVideoSnippet->channelTitle;
            $video->defaultAudioLanguage = $youtubeVideoSnippet->defaultAudioLanguage;
            $video->defaultLanguage = $youtubeVideoSnippet->defaultLanguage;
            $video->tags = implode(', ', $youtubeVideoSnippet->tags);

            try {
                Craft::$app->getElements()->saveElement($video);
            } catch (Throwable $e) {
                Craft::error(
                    sprintf('Couldn\'t save video element because of the following exception: %s', $e->getMessage()),
                    __METHOD__
                );
            }
        }
    }
}
