<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use Craft;
use craft\elements\db\ElementQuery;

/**
 * Playlist Item query
 */
class PlaylistItemQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        // todo: join the `playlistitems` table
        // $this->joinElementTable('playlistitems');

        // todo: apply any custom query params
        // ...

        return parent::beforePrepare();
    }
}
