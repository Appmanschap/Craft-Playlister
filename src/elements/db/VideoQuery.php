<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;

/**
 * @template TKey of array-key
 * @template TElement of ElementInterface
 * @extends ElementQuery<TKey, TElement>
 */
class VideoQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        $this->joinElementTable("{{%youtube_playlist_videos}}");

        $this->query?->select([
            "{{%youtube_playlist_videos}}.*",
        ]);

        // todo: apply any custom query params
        // ...

        return parent::beforePrepare();
    }
}
