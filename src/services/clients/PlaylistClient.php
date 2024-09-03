<?php

namespace appmanschap\craftplaylister\services\clients;

use Google\Service\YouTube;

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
