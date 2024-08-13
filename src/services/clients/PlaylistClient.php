<?php
namespace appmanschap\youtubeplaylistimporter\services\clients;

use Google\Service\YouTube;
use Google\Service\YouTube\Playlist;
use Psr\Http\Message\RequestInterface;

interface PlaylistClient
{
    /**
     * Setup the PlaylistClient by setting a $this->client property which can be retrieved.
     *
     * @return void
     */
    public function setupClient(): void;

    /**
     * @return mixed
     */
    public function getClient(): mixed;

    /**
     * @return YouTube
     */
    public function getYouTubeService(): YouTube;
}