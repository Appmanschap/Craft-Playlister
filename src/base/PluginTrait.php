<?php

namespace appmanschap\craftplaylister\base;

use appmanschap\craftplaylister\controllers\PlaylistController;
use appmanschap\craftplaylister\elements\Playlist;
use appmanschap\craftplaylister\elements\Video;
use appmanschap\craftplaylister\fields\Playlists;
use appmanschap\craftplaylister\fields\Videos;
use appmanschap\craftplaylister\variables\Playlister as PlaylisterVariable;
use Craft;
use craft\controllers\ElementsController;
use craft\events\DefineElementEditorHtmlEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\i18n\PhpMessageSource;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Event;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
trait PluginTrait
{
    /**
     * @return void
     */
    public function _registerI18nTranslations(): void
    {
        Craft::$app->i18n->translations['craftplaylister'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => $this->getBasePath() . DIRECTORY_SEPARATOR . 'translations/',
            'allowOverrides' => true,
        ];
    }

    /**
     * @return void
     */
    public function _registerTemplateRoots(): void
    {
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $event) {
            $event->roots[$this->id] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates/';
        });
    }

    /**
     * @return void
     */
    public function _registerEditorContents(): void
    {
        Event::on(PlaylistController::class, ElementsController::EVENT_DEFINE_EDITOR_CONTENT, function(DefineElementEditorHtmlEvent $event) {
            $event->html = Craft::$app->getView()->renderTemplate('craft-playlister/playlist/_form', [
                'title' => $event->element->title,
                'playlist' => $event->element,
            ]);
        });
    }

    /**
     * @return void
     */
    public function _registerCpPermissions(): void
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[] = [
                'heading' => Craft::t('craftplaylister', 'Playlister'),
                'permissions' => [
                    'playlister:playlist' => [
                        'label' => Craft::t('craftplaylister', 'View playlists'),
                        'nested' => [
                            'playlister:playlist:create' => [
                                'label' => Craft::t('craftplaylister', 'Create playlists'),
                            ],
                            'playlister:playlist:update' => [
                                'label' => Craft::t('craftplaylister', 'Manage playlists'),
                            ],
                            'playlister:playlist:delete' => [
                                'label' => Craft::t('craftplaylister', 'Delete playlists'),
                            ],
                        ],
                    ],
                    'playlister:plugin-settings' => [
                        'label' => Craft::t('craftplaylister', 'Edit Plugin Settings'),
                    ],
                ],
            ];
        });
    }

    /**
     * @return void
     */
    public function _registerElementTypes(): void
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = Playlist::class;
                $event->types[] = Video::class;
            }
        );
    }

    /**
     * @return void
     */
    public function _registerFieldTypes(): void
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Playlists::class;
            $event->types[] = Videos::class;
        });
    }

    /**
     * @return void
     */
    public function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('playlister', PlaylisterVariable::class);
        });
    }
}
