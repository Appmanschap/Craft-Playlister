<?php

namespace appmanschap\youtubeplaylistimporter;

use appmanschap\youtubeplaylistimporter\base\PluginTrait;
use appmanschap\youtubeplaylistimporter\base\Routes;
use appmanschap\youtubeplaylistimporter\models\Settings;
use appmanschap\youtubeplaylistimporter\services\PlaylistImport;
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
 * Youtube Playlist Importer plugin
 *
 * @method static YoutubePlaylistImporter getInstance()
 * @method Settings getSettings()
 * @author Appmanschap <info@appmanschap.nl>
 * @copyright Appmanschap
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read PlaylistImport $playlistImport
 */
class YoutubePlaylistImporter extends Plugin
{
    use PluginTrait;
    use Routes;

    /**
     * @var YoutubePlaylistImporter|null
     */
    public static ?YoutubePlaylistImporter $plugin;

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

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
        return $response->redirect(UrlHelper::cpUrl('youtube-playlist/settings'));
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
        return Craft::$app->view->renderTemplate('youtube-playlist-importer/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }
}
