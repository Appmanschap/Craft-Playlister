<?php

namespace appmanschap\craftplaylister\elements;

use appmanschap\craftplaylister\elements\conditions\VideoCondition;
use appmanschap\craftplaylister\elements\db\VideoQuery;
use appmanschap\craftplaylister\enums\VideoThumbnailSize;
use appmanschap\craftplaylister\records\VideoRecord;
use Craft;
use craft\base\Element;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\web\CpScreenResponseBehavior;
use DateTime;
use Throwable;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * Video element type
 */
class Video extends Element
{
    public string $description = '';
    public DateTime $datePublished;
    public string $videoId = '';
    public string $playlistId = '';
    public string $channelId = '';
    public string $channelTitle = '';
    public ?string $defaultAudioLanguage = 'en';
    public ?string $defaultLanguage = 'en';
    public bool $embeddable = false;
    public VideoThumbnailSize $thumbnail = VideoThumbnailSize::DEFAULT;
    public string $tags = '';

    public static function displayName(): string
    {
        return Craft::t('craftplaylister', 'Video');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('craftplaylister', 'video');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('craftplaylister', 'Videos');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('craftplaylister', 'videos');
    }

    public static function refHandle(): ?string
    {
        return 'video';
    }

    public static function trackChanges(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasUris(): bool
    {
        return true;
    }

    public static function isLocalized(): bool
    {
        return false;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @return VideoQuery<array-key, Video>
     * @throws \yii\base\InvalidConfigException
     */
    public static function find(): VideoQuery
    {
        return Craft::createObject(VideoQuery::class, [static::class]);
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(VideoCondition::class, [static::class]);
    }

    /**
     * @param  string  $context
     * @return array<array-key, array<array-key, string|array<array-key, string|int|null>>>
     */
    protected static function defineSources(string $context): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('craftplaylister', 'All videos'),
            ],
        ];

        /** @var Playlist[] $playlists */
        $playlists = Playlist::find()->all();

        array_map(function(Playlist $playlist) use (&$sources) {
            $sources["playlist:{$playlist->id}"] = [
                'key' => "playlist:{$playlist->id}",
                'label' => $playlist->name,
                'data' => [
                    'handle' => Playlist::refHandle(),
                ],
                'criteria' => ['playlistId' => $playlist->playlistId],
                'defaultSort' => ['youtube_playlist_videos.pushed_at' => SORT_DESC],
            ];
        }, $playlists);

        return $sources;
    }

    /**
     * @param  string  $source
     * @return array<int, array<string, string>>
     */
    protected static function defineActions(string $source): array
    {
        // List any bulk element actions here
        return [];
    }

    protected static function includeSetStatusAction(): bool
    {
        return true;
    }

    /**
     * @return array<int|string, string|array<string, string>>
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            [
                'label' => Craft::t('app', 'Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'YouTube Video ID'),
                'orderBy' => 'videoId',
                'attribute' => 'videoId',
                'defaultDir' => 'asc',
            ],
            [
                'label' => Craft::t('app', 'Published at'),
                'orderBy' => 'datePublished',
                'attribute' => 'datePublished',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'Embeddable'),
                'orderBy' => 'embeddable',
                'attribute' => 'embeddable',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'ID'),
                'orderBy' => 'elements.id',
                'attribute' => 'id',
            ],
            // ...
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'id' => ['label' => Craft::t('app', 'ID')],
            'videoId' => ['label' => Craft::t('craftplaylister', 'YouTube Video ID')],
            'embeddable' => ['label' => Craft::t('craftplaylister', 'Embeddable')],
            'uid' => ['label' => Craft::t('app', 'UID')],
            'datePublished' => ['label' => Craft::t('app', 'Published at')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
            // ...
        ];
    }

    /**
     * @param  string  $source
     * @return array<int, string>
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'videoId',
            'embeddable',
            'datePublished',
            'dateCreated',
            // ...
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    public function getUriFormat(): ?string
    {
        // If videos should have URLs, define their URI format here
        return null;
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function previewTargets(): array
    {
        $previewTargets = [];
        $url = $this->getUrl();
        if ($url) {
            $previewTargets[] = [
                'label' => Craft::t('app', 'Primary {type} page', [
                    'type' => self::lowerDisplayName(),
                ]),
                'url' => $url,
            ];
        }
        return $previewTargets;
    }

    /**
     * @return array<string|int, string|array<string, string|array<string, mixed>>>|string|null
     */
    protected function route(): array|string|null
    {
        // Define how videos should be routed when their URLs are requested
        return [
            'templates/render',
            [
                'template' => 'site/template/path',
                'variables' => ['video' => $this],
            ],
        ];
    }

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }

        return $user->can('playlister:videos');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('playlister:videos:update');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }

        return $user->can('playlister:videos:update');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('playlister:videos:delete');
    }

    public function canCreateDrafts(User $user): bool
    {
        return true;
    }

    protected function cpEditUrl(): ?string
    {
        return sprintf('playlister/videos/%s', $this->getCanonicalId());
    }

    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl('videos');
    }

    public function prepareEditScreen(Response $response, string $containerId): void
    {
        /** @var CpScreenResponseBehavior $response */
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('videos'),
            ],
        ]);
    }

    /**
     * @param bool $isNew
     * @return void
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {
            if (!$isNew) {
                $record = VideoRecord::findOrFail($this->id ?? 0);
            } else {
                $record = new VideoRecord();
                $record->id = $this->id;
            }

            $record->fillByElement($this)->save(false);
        }

        parent::afterSave($isNew);
    }

    /**
     * @return void
     * @throws Throwable
     * @throws Exception
     * @throws StaleObjectException
     */
    public function afterDelete(): void
    {
        $record = VideoRecord::findOrFail($this->id ?? 0);

        if ($this->hardDelete) {
            $record->delete();
        } else {
            if (method_exists($record, 'softDelete')) {
                $record->softDelete();
            }
        }

        parent::afterDelete();
    }

    /**
     * @param string|null $size
     * @return string
     */
    public function getThumbnail(?string $size = null): string
    {
        $size = $this->thumbnail->firstAvailableSize($size ?? '');

        return match ($size) {
            VideoThumbnailSize::DEFAULT->value => "https://i.ytimg.com/vi/{$this->videoId}/default.jpg",
            VideoThumbnailSize::MEDIUM->value => "https://i.ytimg.com/vi/{$this->videoId}/mqdefault.jpg",
            VideoThumbnailSize::HIGH->value => "https://i.ytimg.com/vi/{$this->videoId}/hqdefault.jpg",
            VideoThumbnailSize::MAXRES->value => "https://i.ytimg.com/vi/{$this->videoId}/maxresdefault.jpg",
            default => "https://i.ytimg.com/vi/{$this->videoId}/sddefault.jpg",
        };
    }
}
