<?php

namespace appmanschap\youtubeplaylistimporter\base;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\i18n\PhpMessageSource;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Event;

trait PluginTrait
{
    /**
     * @return void
     */
    public function _registerI18nTranslations(): void
    {
        Craft::$app->i18n->translations['youtubeplaylistimporter'] = [
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
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $event) {
            $event->roots[$this->id] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates/';
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
            static function (RegisterComponentTypesEvent $event) {
//                $event->types[] = Custom::class;
            }
        );
    }

    /**
     * @return void
     */
    public function _registerFieldTypes(): void
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
//            $event->types[] = Custom::class;
        });
    }

    /**
     * @return void
     */
    public function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
//            $variable->set('camelCaseCustom', Custom::class);
        });
    }
}