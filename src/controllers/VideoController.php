<?php
namespace appmanschap\youtubeplaylistimporter\controllers;

use appmanschap\youtubeplaylistimporter\elements\Video as VideoElement;
use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\web\Response;

class VideoController extends Controller
{
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = [];

    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        $this->requirePermission('youtube-playlist-importer:video');

        return $this->renderTemplate('youtube-playlist-importer/videos/_index', [
            'title' => Craft::t('youtubeplaylistimporter', 'Videos'),
            'selectedSubnavItem' => 'videos',
            'fullPageForm' => false,
            'canHaveDrafts' => false,
            'elementType' => VideoElement::class,
            'crumbs' => $this->getCrumbs([
                'label' => 'Videos',
                'url' => UrlHelper::cpUrl("youtube-playlist/videos"),
            ]),
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