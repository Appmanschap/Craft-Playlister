<?php

namespace appmanschap\youtubeplaylistimporter\controllers;

use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use appmanschap\youtubeplaylistimporter\jobs\ImportPlaylistJob;
use appmanschap\youtubeplaylistimporter\supports\Cast;
use Craft;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Exception;
use Throwable;
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
     * @param int $elementId
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws HttpException
     * @throws SiteNotFoundException
     */
    public function actionEdit(int $elementId): Response
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

    /**
     * @throws ForbiddenHttpException
     * @throws MethodNotAllowedHttpException
     * @throws BadRequestHttpException
     * @throws Throwable
     */
    public function actionSave(): ?Response
    {
        $this->requireCpRequest();

        $this->requirePostRequest();

        $request = $this->request;

        if ($request->getParam('id') !== null) {
            $this->requirePermission('youtube-playlist-importer:playlist:update');
            $playlist = Craft::$app->getElements()->getElementById(Cast::mixedToInt($request->getParam('id')), PlaylistElement::class);

            if (!$playlist) {
                throw new \yii\base\Exception('No playlist found with that id.');
            }
        } else {
            $this->requirePermission('youtube-playlist-importer:playlist:create');
            $playlist = new PlaylistElement();
        }

        $url_parts = parse_url(Cast::mixedToString($request->getParam('youtubeUrl')));
        parse_str($url_parts['query'] ?? '', $query_parts);

        if (!isset($query_parts['list']) || !is_string($query_parts['list'])) {
            throw new Exception('Playlist could not be found from the url.');
        }

        $playlist->playlistId = $query_parts['list'];
        $playlist->youtubeUrl = Cast::mixedToString($request->getParam('youtubeUrl'));
        $playlist->name = Cast::mixedToString($request->getParam('name'));
        $playlist->refreshInterval = Cast::mixedToInt($request->getParam('refreshInterval'));
        $playlist->limit = Cast::mixedToInt($request->getParam('limit'));

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

        $this->setSuccessFlash(Craft::t('youtubeplaylistimporter', 'Playlist saved.'));

        return $this->redirectToPostedUrl($playlist);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws HttpException
     */
    public function actionStartJob(): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:playlist:update');

        $playlistId = Cast::mixedToInt($this->request->getParam('id'));
        $playlist = Craft::$app->getElements()->getElementById($playlistId, PlaylistElement::class);

        if (is_null($playlist)) {
            throw new HttpException(404);
        }

        Craft::$app->getQueue()->push(new ImportPlaylistJob(['playlist' => $playlist]));

        $this->setSuccessFlash(Craft::t('youtubeplaylistimporter', 'Job scheduled.'));

        return $this->redirectToPostedUrl($playlist);
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
