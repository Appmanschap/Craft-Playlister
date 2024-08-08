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
                $event->rules['youtube-playlist/settings'] = 'youtube-playlist-importer/settings/plugin';
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
