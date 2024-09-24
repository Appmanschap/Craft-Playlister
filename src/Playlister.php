<?php

namespace appmanschap\craftplaylister;

use appmanschap\craftplaylister\base\PluginTrait;
use appmanschap\craftplaylister\base\Routes;
use appmanschap\craftplaylister\models\Settings;
use appmanschap\craftplaylister\services\PlaylistImport;
use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\helpers\UrlHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * Playlister plugin
 *
 * @method static Playlister getInstance()
 * @method Settings getSettings()
 * @author Appmanschap <info@appmanschap.nl>
 * @copyright Appmanschap
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read PlaylistImport $playlistImport
 */
class Playlister extends Plugin
{
    use PluginTrait;
    use Routes;

    /**
     * @var Playlister|null
     */
    public static ?Playlister $plugin;

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.1';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @return array<string, array<string, class-string>>
     */
    public static function config(): array
    {
        return [
            'components' => ['playlistImport' => PlaylistImport::class],
        ];
    }

    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->_registerI18nTranslations();

            if (Craft::$app->getRequest()->getIsCpRequest()) {
                $this->_registerTemplateRoots();
                $this->_registerElementTypes();
                $this->_registerFieldTypes();
                $this->_registerEditorContents();
                $this->_registerNavItems();
                $this->_registerCpRoutes();
                $this->_registerCpPermissions();
            }

            if (Craft::$app->getRequest()->getIsSiteRequest()) {
                $this->_registerVariables();
            }
        });
    }

    /**
     * @return Model|null
     * @throws InvalidConfigException
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * @param  bool  $render
     * @return mixed
     * @throws InvalidRouteException
     */
    public function getSettingsResponse(bool $render = false): mixed
    {
        if ($render) {
            return parent::getSettingsResponse();
        }

        $response = Craft::$app->getResponse();
        if (!method_exists($response, 'redirect')) {
            return $response;
        }

        // Just redirect to the plugin settings page
        return $response->redirect(UrlHelper::cpUrl('playlister/settings'));
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('craft-playlister/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }
}
