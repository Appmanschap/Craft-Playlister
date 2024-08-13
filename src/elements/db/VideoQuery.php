<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use Craft;
use craft\elements\db\ElementQuery;

/**
 * Video query
 */
class VideoQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        $this->joinElementTable("{{%youtube_playlist_videos}}");

        $this->query->select([
            "{{%youtube_playlist_videos}}.*",
        ]);

        // todo: apply any custom query params
        // ...

        return parent::beforePrepare();
    }
}
