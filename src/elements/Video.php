<?php

namespace appmanschap\youtubeplaylistimporter\elements;

use appmanschap\youtubeplaylistimporter\elements\conditions\VideoCondition;
use appmanschap\youtubeplaylistimporter\elements\db\VideoQuery;
use appmanschap\youtubeplaylistimporter\records\VideoRecord;
use Craft;
use craft\base\Element;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\web\CpScreenResponseBehavior;
use DateTime;
use Throwable;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
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
    public string $defaultAudioLanguage = 'en';
    public ?string $defaultLanguage = 'en';
    public bool $embeddable = false;
    public string $tags = '';

    public static function displayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'Video');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'video');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'Videos');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'videos');
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

    public static function find(): ElementQueryInterface
    {
        return Craft::createObject(VideoQuery::class, [static::class]);
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(VideoCondition::class, [static::class]);
    }

    /**
     * @param  string  $context
     * @return array<int, array<string, string>>
     */
    protected static function defineSources(string $context): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('youtube-playlist-importer', 'All videos'),
            ],
        ];
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
            'slug' => Craft::t('app', 'Slug'),
            'uri' => Craft::t('app', 'URI'),
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
            'slug' => ['label' => Craft::t('app', 'Slug')],
            'uri' => ['label' => Craft::t('app', 'URI')],
            'link' => ['label' => Craft::t('app', 'Link'), 'icon' => 'world'],
            'id' => ['label' => Craft::t('app', 'ID')],
            'uid' => ['label' => Craft::t('app', 'UID')],
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
            'link',
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

        return $user->can('youtube-playlist-importer:videos');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:videos:update');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:videos:update');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:videos:delete');
    }

    public function canCreateDrafts(User $user): bool
    {
        return true;
    }

    protected function cpEditUrl(): ?string
    {
        return sprintf('youtube-playlist/videos/%s', $this->getCanonicalId());
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
}
