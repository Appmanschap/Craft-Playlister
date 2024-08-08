<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use Craft;
use craft\elements\db\ElementQuery;

/**
 * Playlist query
 */
class PlaylistQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        // todo: join the `playlists` table
        // $this->joinElementTable('playlists');

        // todo: apply any custom query params
        // ...

        return parent::beforePrepare();
    }
}
