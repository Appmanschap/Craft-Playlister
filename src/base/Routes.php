<?php

namespace appmanschap\youtubeplaylistimporter\base;

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
                $event->rules['youtube-playlist'] = 'youtube-playlist-importer/playlist/index';
                $event->rules['youtube-playlist/playlists'] = 'youtube-playlist-importer/playlist/index';
                $event->rules['youtube-playlist/playlists/new'] = 'youtube-playlist-importer/playlist/edit';
                $event->rules['youtube-playlist/playlists/<elementId:\\d+>'] = 'youtube-playlist-importer/playlist/edit';
                $event->rules['youtube-playlist/playlists/start-job/<playlistId:\\d+>'] = 'youtube-playlist-importer/playlist/start-job';

                $event->rules['youtube-playlist/settings'] = 'youtube-playlist-importer/settings/plugin';

                $event->rules['youtube-playlist/videos'] = 'youtube-playlist-importer/video/index';
                $event->rules['youtube-playlist/videos/<elementId:\\d+>'] = 'youtube-playlist-importer/video/edit';
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
                'url' => 'youtube-playlist',
                'label' => 'Youtube Playlists',
                'icon' => $this->getBasePath() . DIRECTORY_SEPARATOR . 'icon.svg',
                'subnav' => [
                    'playlists' => [
                        'url' => 'youtube-playlist/playlists',
                        'label' => 'Playlist',
                    ],
                    'videos' => [
                        'url' => 'youtube-playlist/videos',
                        'label' => 'Video',
                    ],
                    'settings' => [
                        'url' => 'youtube-playlist/settings',
                        'label' => 'Settings',
                    ],
                ],
            ];
        });
    }
}
