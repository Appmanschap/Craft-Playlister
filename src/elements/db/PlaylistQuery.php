<?php

namespace appmanschap\craftplaylister\elements\db;

use appmanschap\craftplaylister\collections\PlaylistCollection;
use appmanschap\craftplaylister\elements\Playlist;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection as YiiConnection;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * @template TKey of array-key
 * @template TElement of Playlist
 * @extends ElementQuery<TKey, TElement>
 */
class PlaylistQuery extends ElementQuery
{
    /**
     * @var string|null
     */
    public ?string $playlistId = null;

    /**
     * @return bool
     * @throws QueryAbortedException
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable("{{%playlister_playlists}}");

        $this->query?->select([
            "{{%playlister_playlists}}.*",
        ]);

        if ($this->playlistId) {
            $this->subQuery?->andWhere(Db::parseParam('{{%playlister_playlists}}.playlistId', $this->playlistId) ?? []);
        }

        return parent::beforePrepare();
    }

    /**
     * @param string $playlistId
     * @return $this
     */
    public function playlistId(string $playlistId): static
    {
        $this->playlistId = $playlistId;
        return $this;
    }

    /**
     * @param YiiConnection|null $db
     * @return PlaylistCollection<array-key, Playlist>
     */
    public function collect(?YiiConnection $db = null): PlaylistCollection
    {
        // NOTE: For now we can make an own collection by retrieving the items from ElementCollection.
        //          Adjust this when it's possible to use a custom ElementCollection in Craft.
        $collection = parent::collect($db);
        /** @var PlaylistCollection<array-key, Playlist> $playlistCollection */
        $playlistCollection = PlaylistCollection::make($collection->all());
        return $playlistCollection;
    }
}
