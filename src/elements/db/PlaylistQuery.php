<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use craft\base\ElementInterface;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;

/**
 * @template TKey of array-key
 * @template TElement of ElementInterface
 * @extends ElementQuery<TKey, TElement>
 */
class PlaylistQuery extends ElementQuery
{
    /**
     * @return bool
     * @throws QueryAbortedException
     */
    protected function beforePrepare(): bool
    {
        // todo: join the `playlists` table
        // $this->joinElementTable('playlists');

        // todo: apply any custom query params
        // ...

        return parent::beforePrepare();
    }
}
