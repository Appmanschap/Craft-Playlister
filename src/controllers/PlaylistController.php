<?php

namespace appmanschap\craftplaylister\controllers;

use appmanschap\craftplaylister\elements\Playlist as PlaylistElement;
use appmanschap\craftplaylister\jobs\ImportPlaylistJob;
use appmanschap\craftplaylister\supports\Cast;
use Craft;
use craft\controllers\ElementsController;
use craft\db\Query;
use craft\db\Table;
use craft\queue\Queue;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class PlaylistController extends ElementsController
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

        $this->requirePermission('playlister:playlist');

        return $this->renderTemplate('craft-playlister/playlist/_index');
    }

    /**
     * @param PlaylistElement|null $playlist
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionNew(PlaylistElement $playlist = null): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('playlister:playlist:create');

        if (is_null($playlist)) {
            $playlist = new PlaylistElement();
        }

        return $this->renderTemplate('craft-playlister/playlist/_form', [
            'title' => Craft::t('craft-playlister', 'New playlist'),
            'selectedSubnavItem' => 'playlists',
            'fullPageForm' => true,
            'playlist' => $playlist,
        ]);
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

        $this->requirePermission('playlister:playlist:update');

        $playlistId = Cast::mixedToInt($this->request->getParam('playlistId'));
        /** @var ?PlaylistElement $playlist */
        $playlist = Craft::$app->getElements()->getElementById($playlistId, PlaylistElement::class);

        if (is_null($playlist)) {
            throw new HttpException(404);
        }

        $playlist->refreshJobs();

        $this->setSuccessFlash(Craft::t('craft-playlister', 'Job scheduled.'));

        return $this->redirectToPostedUrl($playlist);
    }
}
