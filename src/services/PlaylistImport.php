<?php

namespace appmanschap\craftplaylister\services;

use appmanschap\craftplaylister\elements\Playlist as PlaylistElement;
use appmanschap\craftplaylister\elements\Video as VideoElement;
use appmanschap\craftplaylister\enums\VideoThumbnailSize;
use appmanschap\craftplaylister\services\clients\PlaylistClient;
use appmanschap\craftplaylister\services\clients\YoutubeClient;
use Craft;
use DateTime;
use Google\Service\Exception;
use Google\Service\YouTube\PlaylistItem as YouTubePlaylistItem;
use Google\Service\YouTube\Video as YoutubeVideo;
use Throwable;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
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
     * @var array<int, int>
     */
    private array $missingVideoIds = [];

    /**
     * @param PlaylistElement $playlist
     * @return void
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function import(PlaylistElement $playlist): void
    {
        $this->playlistId = $playlist->playlistId;
        $this->limit = $playlist->limit ?? 50;

        $this->setMissingVideoIds();

        $this->setClient();

        $playlistItems = $this->getPlaylistItems();
        $videos = $this->getVideosByPlaylistItems($playlistItems);

        $this->createVideoElements($playlist, $playlistItems, $videos);

        $this->firstImport = false;

        while ($this->canImport()) {
            $this->import($playlist);
        }

        if ($this->canImport() === false) {
            $this->deleteVideoElements();
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
     * @param array<int, YouTubePlaylistItem> $playlistItems
     * @return array<int, YoutubeVideo>
     * @throws Exception
     */
    private function getVideosByPlaylistItems(array $playlistItems): array
    {
        $videoIds = array_map(static fn($playlistItem) => $playlistItem->getContentDetails()->getVideoId(), $playlistItems);

        if (empty($videoIds)) {
            return [];
        }

        $videoListResponse = $this->client->getYouTubeService()->videos->listVideos([
            'contentDetails',
            'localizations',
            'snippet',
            'status',
        ], [
            'id' => $videoIds,
        ]);

        return $videoListResponse->getItems();
    }

    /**
     * @param PlaylistElement $playlist
     * @param array<int, YouTubePlaylistItem> $playlistItems
     * @param array<int, YoutubeVideo> $videos
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
            } else {
                $this->unsetFromMissingVideoIds($video->id ?? 0);
            }

            $encodedThumnails = json_encode($youtubeVideoSnippet->getThumbnails()->toSimpleObject()) ?: '';
            $thumbnails = json_decode($encodedThumnails, true) ?? [];
            $thumbnails = array_keys(is_array($thumbnails) ? $thumbnails : []);
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
            $video->privacyStatus = $youtubeStatus->getPrivacyStatus();
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

    private function deleteVideoElements(): void
    {
        try {
            array_map(static fn($videoId) => Craft::$app->getElements()->deleteElementById($videoId, VideoElement::class), $this->missingVideoIds);
        } catch (Throwable $e) {
            Craft::error(
                sprintf('Couldn\'t delete video element because of the following exception: %s', $e->getMessage()),
                __METHOD__
            );
        }
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    private function setMissingVideoIds(): void
    {
        if ($this->firstImport) {
            /** @var array<int, int> $missingVideoIds */
            $missingVideoIds = VideoElement::find()->where([
                'playlistId' => $this->playlistId,
            ])->collect()->pluck('id')->toArray();
            $this->missingVideoIds = $missingVideoIds;
        }
    }

    /**
     * @param int $videoId
     * @return void
     */
    private function unsetFromMissingVideoIds(int $videoId): void
    {
        if ($key = array_search($videoId, $this->missingVideoIds, true)) {
            unset($this->missingVideoIds[$key]);
        }
    }
}
