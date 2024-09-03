<?php

namespace appmanschap\youtubeplaylistimporter\services;

use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use appmanschap\youtubeplaylistimporter\elements\Video as VideoElement;
use appmanschap\youtubeplaylistimporter\enums\VideoThumbnailSize;
use appmanschap\youtubeplaylistimporter\services\clients\PlaylistClient;
use appmanschap\youtubeplaylistimporter\services\clients\YoutubeClient;
use Craft;
use DateTime;
use Google\Service\Exception;
use Google\Service\YouTube\PlaylistItem as YouTubePlaylistItem;
use Google\Service\YouTube\Video as YoutubeVideo;
use Throwable;
use yii\base\Component;

/**
 * Playlist Import service
 */
class PlaylistImport extends Component
{
    /**
     * @var PlaylistClient
     */
    private PlaylistClient $client;

    /**
     * @var string
     */
    private string $playlistId;

    /**
     * @var string|null
     */
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
    private int $retrievedAmount = 0;

    /**
     * @var int
     */
    private int $limit = 50;

    /**
     * @param  PlaylistElement  $playlist
     * @return void
     * @throws Exception
     */
    public function import(PlaylistElement $playlist): void
    {
        $this->playlistId = $playlist->playlistId;
        $this->limit = $playlist->limit ?? 50;

        $this->setClient();

        $playlistItems = $this->getPlaylistItems();
        $videos = $this->getVideosByPlaylistItems($playlistItems);

        $this->createVideoElements($playlist, $playlistItems, $videos);

        $this->firstImport = false;

        while ($this->canImport()) {
            $this->import($playlist);
        }
    }

    private function canImport(): bool
    {
        return $this->firstImport
            || ($this->firstImport === false && $this->nextPageToken && $this->retrievedAmount < $this->limit);
    }

    /**
     * @return void
     */
    private function setClient(): void
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
            'playlistId' => $this->playlistId,
            'maxResults' => $this->maxResults,
        ];

        if ($this->nextPageToken) {
            $options['pageToken'] = $this->nextPageToken;
        }

        $results = $this->client->getYouTubeService()->playlistItems->listPlaylistItems('contentDetails', $options);

        $this->nextPageToken = $results->getNextPageToken();
        $items = $results->getItems();

        $this->retrievedAmount += count($items);

        return $items;
    }

    /**
     * @param  array<int, YouTubePlaylistItem>  $playlistItems
     * @return array<int, YoutubeVideo>
     * @throws Exception
     */
    private function getVideosByPlaylistItems(array $playlistItems): array
    {
        $videoListResponse = $this->client->getYouTubeService()->videos->listVideos([
            'contentDetails',
            'localizations',
            'snippet',
            'status',
        ], [
            'id' => array_map(static fn($playlistItem) => $playlistItem->getContentDetails()->getVideoId(), $playlistItems),
        ]);

        return $videoListResponse->getItems();
    }

    /**
     * @param  PlaylistElement  $playlist
     * @param  array<int, YouTubePlaylistItem>  $playlistItems
     * @param  array<int, YoutubeVideo>  $videos
     * @return void
     */
    private function createVideoElements(PlaylistElement $playlist, array $playlistItems, array $videos)
    {
        foreach ($playlistItems as $playlistItem) {
            /** @var YouTubePlaylistItem $playlistItem */
            $videoId = $playlistItem->getContentDetails()->getVideoId();

            /** @var YoutubeVideo[] $youtubeVideos */
            $youtubeVideos = array_values(array_filter($videos, static fn(YoutubeVideo $video) => $video->id === $videoId));

            if (empty($youtubeVideos)) {
                continue;
            }

            $youtubeVideoSnippet = $youtubeVideos[0]->getSnippet();
            $youtubeStatus = $youtubeVideos[0]->getStatus();

            /** @var VideoElement|null $video */
            $video = VideoElement::find()->where([
                'videoId' => $videoId,
            ])->one();

            if (null === $video) {
                $video = new VideoElement();
            }

            $thumbnails = array_keys(json_decode(json_encode($youtubeVideoSnippet->getThumbnails()->toSimpleObject()), true) ?? []);
            $tags = empty($youtubeVideoSnippet->getTags()) ? [] : $youtubeVideoSnippet->getTags();

            $video->videoId = $videoId;
            $video->title = $youtubeVideoSnippet->getTitle();
            $video->description = $youtubeVideoSnippet->getDescription();
            $video->datePublished = new DateTime($youtubeVideoSnippet->getPublishedAt());
            $video->playlistId = $this->playlistId;
            $video->channelId = $youtubeVideoSnippet->getChannelId();
            $video->channelTitle = $youtubeVideoSnippet->getChannelTitle();
            $video->defaultAudioLanguage = $youtubeVideoSnippet->getDefaultAudioLanguage();
            $video->defaultLanguage = $youtubeVideoSnippet->getDefaultLanguage();
            $video->embeddable = $youtubeStatus->getEmbeddable();
            $video->thumbnail = VideoThumbnailSize::tryFrom(array_slice($thumbnails, -1)[0] ?? '') ?? VideoThumbnailSize::DEFAULT;
            $video->tags = implode(', ', $tags);

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
