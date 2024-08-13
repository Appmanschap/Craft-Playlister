<?php

namespace appmanschap\youtubeplaylistimporter\controllers;

use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use appmanschap\youtubeplaylistimporter\YoutubePlaylistImporter;
use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
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
            'canHaveDrafts' => true,
            'elementType' => PlaylistElement::class,
            'crumbs' => $this->getCrumbs([
                'label' => 'Playlists',
                'url' => UrlHelper::cpUrl("youtube-playlist/playlists"),
            ]),
        ]);
    }

    /**
     * @param  PlaylistElement|null  $playlist
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionNew(PlaylistElement $playlist = null): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:playlist:create');

        if (is_null($playlist)) {
            $playlist = new PlaylistElement();
        }

        return $this->renderTemplate('youtube-playlist-importer/playlist/_form', [
            'title' => Craft::t('youtubeplaylistimporter', 'New playlist'),
            'selectedSubnavItem' => 'playlists',
            'fullPageForm' => true,
            'playlist' => $playlist,
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     * @throws MethodNotAllowedHttpException
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function actionSave(): ?Response
    {
        $this->requireCpRequest();

        $this->requirePostRequest();

        $request = $this->request;

        if ($request->getParam('id') !== null) {
            $this->requirePermission('youtube-playlist-importer:playlist:update');
            $playlist = Craft::$app->getElements()->getElementById($request->getParam('id'), PlaylistElement::class, null);
        } else {
            $this->requirePermission('youtube-playlist-importer:playlist:create');
            $playlist = new PlaylistElement();
        }

        $playlist->youtubeUrl = $request->getParam('youtubeUrl');
        $playlist->name = $request->getParam('name');
        $playlist->refreshInterval = $request->getParam('refreshInterval');

        $playlist->validate();

        if ($playlist->hasErrors()) {
            return $this->asModelFailure(
                $playlist, // Model, passed back under the key, below...
                Craft::t('youtubeplaylistimporter', 'Something went wrong!'), // Flash message
                'playlist', // Route param key
            );
        }

        try {
            Craft::$app->getElements()->saveElement($playlist);
        } catch (Exception $e) {
            Craft::error(
                sprintf('Couldn\'t save playlist element because of the following exception: %s', $e->getMessage()),
                __METHOD__
            );
        }

        $this->setSuccessFlash(Craft::t('youtubeplaylistimporter', 'Form saved.'));

        return $this->redirectToPostedUrl($playlist);
    }

    public function actionEdit(int $elementId)
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:playlist:update');

        $playlist = PlaylistElement::findOne($elementId);

        if ($playlist === null) {
            throw new HttpException(404);
        }

        return $this->renderTemplate('youtube-playlist-importer/playlist/_form', [
            'title' => $playlist->title,
            'selectedSubnavItem' => 'playlists',
            'fullPageForm' => true,
            'playlist' => $playlist,
            'elementId' => $elementId,
            'site' => Craft::$app->getSites()->getCurrentSite(),
        ]);
    }

    public function actionStartJob(int $playlistId): void
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:playlist:update');

        $playlist = Craft::$app->getElements()->getElementById($playlistId);

        if (is_null($playlist)) {
            throw new HttpException(404);
        }

        YoutubePlaylistImporter::getInstance()->playlistImport->import($playlist);
//        Craft::$app->getQueue()->push(new ImportPlaylistJob(['playlist' => $playlist]));

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
