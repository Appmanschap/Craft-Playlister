<?php

namespace appmanschap\craftplaylister\base;

use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
trait Routes
{
    /**
     * @return void
     */
    public function _registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $event) {
                $event->rules['playlister'] = 'craft-playlister/playlist/index';
                $event->rules['playlister/playlists'] = 'craft-playlister/playlist/index';
                $event->rules['playlister/playlists/new'] = 'craft-playlister/playlist/edit';
                $event->rules['playlister/playlists/<elementId:\\d+>'] = 'craft-playlister/playlist/edit';
                $event->rules['playlister/playlists/start-job/<playlistId:\\d+>'] = 'craft-playlister/playlist/start-job';

                $event->rules['playlister/settings'] = 'craft-playlister/settings/plugin';

                $event->rules['playlister/videos'] = 'craft-playlister/video/index';
                $event->rules['playlister/videos/<elementId:\\d+>'] = 'craft-playlister/video/edit';
            }
        );
    }

    /**
     * @return void
     */
    public function _registerNavItems(): void
    {
        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $event) {
            $event->navItems[] = [
                'url' => 'playlister',
                'label' => 'Playlists',
                'icon' => $this->getBasePath() . DIRECTORY_SEPARATOR . 'icon.svg',
                'subnav' => [
                    'playlists' => [
                        'url' => 'playlister/playlists',
                        'label' => Craft::t('craft-playlister','Playlist'),
                    ],
                    'videos' => [
                        'url' => 'playlister/videos',
                        'label' => Craft::t('craft-playlister','Video'),
                    ],
                    'settings' => [
                        'url' => 'playlister/settings',
                        'label' => Craft::t('app','Settings'),
                    ],
                ],
            ];
        });
    }
}
