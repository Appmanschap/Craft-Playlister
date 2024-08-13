<?php

namespace appmanschap\youtubeplaylistimporter\base;

use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use yii\base\Event;

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
                $event->rules['youtube-playlist/playlists/new'] = 'youtube-playlist-importer/playlist/new';
                $event->rules['youtube-playlist/playlists/<elementId:\\d+>'] = 'youtube-playlist-importer/playlist/single';
                $event->rules['youtube-playlist/playlists/edit/<elementId:\\d+>'] = 'youtube-playlist-importer/playlist/edit';

                $event->rules['youtube-playlist/settings'] = 'youtube-playlist-importer/settings/plugin';

//                Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
//                    $event->rules['playlists'] = ['template' => 'youtube-playlist-importer/playlists/_index.twig'];
//                    $event->rules['playlists/<elementId:\\d+>'] = 'elements/edit';
//                });
//                $event->rules['playlist-items'] = ['template' => 'youtube-playlist-importer/playlist-items/_index.twig'];
//                $event->rules['playlist-items/<elementId:\\d+>'] = 'elements/edit';
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
                    'settings' => [
                        'url' => 'youtube-playlist/settings',
                        'label' => 'Settings',
                    ],
                ],
            ];
        });
    }
}
