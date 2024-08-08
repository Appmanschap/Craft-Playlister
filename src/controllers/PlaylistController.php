<?php

namespace appmanschap\youtubeplaylistimporter\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class PlaylistController extends Controller
{
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = [];

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:playlist');

        return $this->renderTemplate('youtube-playlist-importer/playlist/_index', [
            'title' => Craft::t('youtubeplaylistimporter', 'Playlists'),
            'selectedSubnavItem' => 'playlists',
            'fullPageForm' => true,
            'elementType' => '', // @TODO update the elementType
            'crumbs' => $this->getCrumbs([
                'label' => 'Playlists',
                'url' => UrlHelper::cpUrl("youtube-playlist/playlists"),
            ]),
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionNew(): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:playlist:create');

        return $this->renderTemplate('youtube-playlist-importer/playlist/_new', [
            'title' => Craft::t('youtubeplaylistimporter', 'New playlist'),
            'selectedSubnavItem' => 'playlists',
            'fullPageForm' => true,
        ]);
    }

    /**
     * @param  array<string, string>  $currentCrumb
     * @return array<int, array<string, string>>
     */
    private function getCrumbs(array $currentCrumb): array
    {
        $pluginName = 'Youtube Playlist Importer';

        $crumbs = [
            [
                'label' => $pluginName,
                'url' => UrlHelper::cpUrl('youtube-playlist'),
            ],
        ];

        if (!empty($currentCrumb)) {
            $crumbs[] = $currentCrumb;
        }

        return $crumbs;
    }
}
