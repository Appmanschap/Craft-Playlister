<?php

namespace appmanschap\craftplaylister\controllers;

use appmanschap\craftplaylister\elements\Video as VideoElement;
use Craft;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class VideoController extends Controller
{
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = [];

    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('playlister:video');

        return $this->renderTemplate('craft-playlister/videos/_index', [
            'title' => Craft::t('craftplaylister', 'Videos'),
            'selectedSubnavItem' => 'videos',
            'fullPageForm' => false,
            'canHaveDrafts' => false,
            'elementType' => VideoElement::class,
            'crumbs' => $this->getCrumbs([
                'label' => 'Videos',
                'url' => UrlHelper::cpUrl("craft-playlister/videos"),
            ]),
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

        $this->requirePermission('playlister:video:update');

        $video = VideoElement::findOne($elementId);

        if ($video === null) {
            throw new HttpException(404);
        }

        return $this->renderTemplate('craft-playlister/videos/_form', [
            'title' => $video->title,
            'selectedSubnavItem' => 'videos',
            'fullPageForm' => false,
            'video' => $video,
            'elementId' => $elementId,
            'site' => Craft::$app->getSites()->getCurrentSite(),
        ]);
    }

    /**
     * @param  array<string, string>  $currentCrumb
     * @return array<int, array<string, string>>
     */
    private function getCrumbs(array $currentCrumb): array
    {
        $pluginName = 'Playlister';

        $crumbs = [
            [
                'label' => $pluginName,
                'url' => UrlHelper::cpUrl('playlister'),
            ],
        ];

        if (!empty($currentCrumb)) {
            $crumbs[] = $currentCrumb;
        }

        return $crumbs;
    }
}
