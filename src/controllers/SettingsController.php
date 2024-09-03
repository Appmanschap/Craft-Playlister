<?php

namespace appmanschap\youtubeplaylistimporter\controllers;

use appmanschap\youtubeplaylistimporter\YoutubePlaylistImporter;
use Craft;
use craft\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class SettingsController extends Controller
{
    protected array|bool|int $allowAnonymous = [];

    /**
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionPlugin(): mixed
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user?->can('youtube-playlist-importer:plugin-settings')) {
            throw new ForbiddenHttpException('You do not have permission to edit Youtube Playlist Importer plugin settings.');
        }

        $general = Craft::$app->getConfig()->getGeneral();
        if (!$general->allowAdminChanges) {
            throw new ForbiddenHttpException('Unable to edit plugin settings because admin changes are disabled in this environment.');
        }

        // Render the template
        return YoutubePlaylistImporter::$plugin?->getSettingsResponse(render: true);
    }
}
