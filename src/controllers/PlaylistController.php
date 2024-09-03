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
            'title' => Craft::t('craftplaylister', 'New playlist'),
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

        $playlistId = Cast::mixedToInt($this->request->getParam('id'));
        $playlist = Craft::$app->getElements()->getElementById($playlistId, PlaylistElement::class);

        if (is_null($playlist)) {
            throw new HttpException(404);
        }

        $this->releaseJobs($playlist);

        Craft::$app->getQueue()->push(new ImportPlaylistJob(['playlist' => $playlist]));

        $this->setSuccessFlash(Craft::t('craftplaylister', 'Job scheduled.'));

        return $this->redirectToPostedUrl($playlist);
    }

    /**
     * @param PlaylistElement $playlist
     * @return void
     */
    private function releaseJobs(PlaylistElement $playlist): void
    {
        $playlistId = $playlist->playlistId;
        $playlistIdLength = strlen($playlistId);
        $uniqueJobPayload = 's:10:"playlistId";s:' . $playlistIdLength . ':"' . $playlistId . '";';

        (new Query())->from(Table::QUEUE)
            ->where(['like', 'job', ImportPlaylistJob::class])
            ->andWhere(['like', 'job', $uniqueJobPayload])
            ->collect()
            ->each(function($job) {
                if (!is_array($job) || !isset($job['id'])) {
                    return;
                }

                /** @var Queue $queue */
                $queue = Craft::$app->getQueue();
                $queue->release($job['id']);
            });
    }
}
