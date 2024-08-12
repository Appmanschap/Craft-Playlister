<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;

/**
 * @template TKey of array-key
 * @template TElement of ElementInterface
 * @extends ElementQuery<TKey, TElement>
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
