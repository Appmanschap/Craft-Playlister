<?php

namespace appmanschap\youtubeplaylistimporter\elements;

use appmanschap\youtubeplaylistimporter\elements\conditions\PlaylistCondition;
use appmanschap\youtubeplaylistimporter\elements\db\PlaylistQuery;
use appmanschap\youtubeplaylistimporter\records\PlaylistRecord;
use Craft;
use craft\base\Element;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\web\CpScreenResponseBehavior;
use yii\db\Exception;
use yii\web\Response;

/**
 * Playlist element type
 */
class Playlist extends Element
{
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

    public static function displayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'Playlist');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'playlist');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'Playlists');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'playlists');
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

    public static function find(): ElementQueryInterface
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
                'label' => Craft::t('youtube-playlist-importer', 'All playlists'),
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
            ['id', 'integer'],
            [['youtubeUrl', 'refreshInterval'], 'required'],
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


    /**
     * @return array<string|int, string|array<string, string|array<string, mixed>>>|string|null
     */
    protected function route(): array|string|null
    {
        // Define how playlists should be routed when their URLs are requested
        return [
            'templates/render',
            [
                'template' => 'site/template/path',
                'variables' => ['playlist' => $this],
            ],
        ];
    }

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:playlist');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:playlist:update');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:playlist:update');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }

        return $user->can('youtube-playlist-importer:playlist:delete');
    }

    public function canCreateDrafts(User $user): bool
    {
        return true;
    }

    protected function cpEditUrl(): ?string
    {
        return sprintf('youtube-playlist/playlists/edit/%s', $this->getCanonicalId());
    }

    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl($this->cpEditUrl());
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
                $record = PlaylistRecord::findOrFail($this->id);
            } else {
                $record = new PlaylistRecord();
                $record->id = $this->id;

            }

            $record->fillByElement($this)->save(false);
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
        $record = PlaylistRecord::findOrFail($this->id);

        if ($this->hardDelete) {
            $record->delete();
        } else {
            $record->softDelete();
        }

        parent::afterDelete();
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        return parent::validate($attributeNames, $clearErrors); // TODO: Change the autogenerated stub
    }
}
