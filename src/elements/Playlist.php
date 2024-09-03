<?php

namespace appmanschap\craftplaylister\elements;

use appmanschap\craftplaylister\elements\conditions\PlaylistCondition;
use appmanschap\craftplaylister\elements\db\PlaylistQuery;
use appmanschap\craftplaylister\records\PlaylistRecord;
use appmanschap\craftplaylister\supports\Cast;
use Craft;
use craft\base\Element;
use craft\elements\actions\Restore;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\ElementCollection;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\web\CpScreenResponseBehavior;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * Playlist element type
 */
class Playlist extends Element
{
    /**
     * @var string
     */
    public string $playlistId = '';

    /**
     * @var string
     */
    public string $youtubeUrl = '';

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var int
     */
    public int $refreshInterval = 5;

    /**
     * @var int
     */
    public int $limit = 50;

    /**
     * @var ElementCollection<array-key, Video>|null
     */
    private ?ElementCollection $_videos = null;
    
    public function __toString(): string
    {
        return $this->name;
    }

    public static function displayName(): string
    {
        return Craft::t('craftplaylister', 'Playlist');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('craftplaylister', 'playlist');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('craftplaylister', 'Playlists');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('craftplaylister', 'playlists');
    }

    public static function refHandle(): ?string
    {
        return 'playlist';
    }

    public static function trackChanges(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return false;
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
     * @return PlaylistQuery<array-key, Playlist>
     * @throws InvalidConfigException
     */
    public static function find(): PlaylistQuery
    {
        return Craft::createObject(PlaylistQuery::class, [static::class]);
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(PlaylistCondition::class, [static::class]);
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
                'label' => Craft::t('craftplaylister', 'All playlists'),
            ],
        ];
    }

    /**
     * @param  string  $source
     * @return array<int, string>
     */
    protected static function defineActions(string $source): array
    {
        // List any bulk element actions here
        return [
            Restore::class,
        ];
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
            'playlistId' => ['label' => Craft::t('craftplaylister', 'YouTube Playlist ID')],
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
            'playlistId',
            'dateUpdated',
            // ...
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            ['id', 'integer'],
            [['youtubeUrl', 'refreshInterval'], 'required', 'when' => fn() => !$this->getIsDraft()],
            ['youtubeUrl', 'url', 'defaultScheme' => 'https'],
            [['youtubeUrl', 'name'], 'string'],
            ['refreshInterval', 'in', 'range' => [5, 10, 15]],
        ]);
    }

    public function getUriFormat(): ?string
    {
        // If playlists should have URLs, define their URI format here
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

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }

        return $user->can('playlister:playlist');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('playlister:playlist:update');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }

        return $user->can('playlister:playlist:update');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('playlister:playlist:delete');
    }

    public static function hasDrafts(): bool
    {
        return true;
    }

    public function canCreateDrafts(User $user): bool
    {
        return true;
    }

    protected function cpEditUrl(): string
    {
        if ($this->trashed) {
            return '';
        }

        return sprintf('playlister/playlists/%s', $this->getCanonicalId());
    }

    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl('playlister/playlists');
    }

    public function getFieldLayout(): ?FieldLayout
    {
        return new FieldLayout();
    }

    public function prepareEditScreen(Response $response, string $containerId): void
    {
        /** @var CpScreenResponseBehavior $response */
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('playlists'),
            ],
        ]);
    }

    public function beforeSave(bool $isNew): bool
    {
        if (!$this->getIsDraft()) {
            $url_parts = parse_url(Cast::mixedToString($this->youtubeUrl));
            parse_str($url_parts['query'] ?? '', $query_parts);

            if (!isset($query_parts['list']) || !is_string($query_parts['list'])) {
                throw new Exception('Playlist could not be found from the url.');
            }

            $this->playlistId = $query_parts['list'];
        }


        return parent::beforeSave($isNew);
    }

    /**
     * @param  bool  $isNew
     * @return void
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {
            if (!$isNew) {
                $record = PlaylistRecord::findOrFail($this->id ?? 0);
            } else {
                $record = new PlaylistRecord();
                $record->id = $this->id;
            }

            $record->fillByElement($this)->save(false);
        }

        parent::afterSave($isNew);
    }

    /**
     * @return void
     * @throws Throwable
     * @throws \yii\base\Exception
     * @throws StaleObjectException
     */
    public function afterDelete(): void
    {
        $record = PlaylistRecord::findOrFail($this->id ?? 0);

        if ($this->hardDelete) {
            $record->delete();
        }

        $this->getVideos()?->each(function(Video $video) {
            Craft::$app->elements->deleteElement($video, $this->hardDelete);
        });

        parent::afterDelete();
    }

    public function afterRestore(): void
    {
        Craft::$app->elements->restoreElements(Video::find()->playlistId($this->playlistId)->trashed()->all());

        parent::afterRestore();
    }

    /**
     * @param bool|null $embeddable
     * @return ElementCollection<array-key, Video>|null
     * @throws InvalidConfigException
     */
    public function getVideos(?bool $embeddable = null): ?ElementCollection
    {
        if (!$this->_videos && $this->playlistId) {
            /** @var ElementCollection<array-key, Video> $videos */
            $videos = Video::find()->playlistId($this->playlistId)->embeddable($embeddable)->collect();
            $this->_videos = $videos;
        }

        return $this->_videos;
    }
}
