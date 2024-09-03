<?php

namespace appmanschap\craftplaylister\variables;

use appmanschap\craftplaylister\elements\db\PlaylistQuery;
use appmanschap\craftplaylister\elements\db\VideoQuery;
use appmanschap\craftplaylister\elements\Playlist;
use appmanschap\craftplaylister\elements\Video;
use Craft;
use yii\base\InvalidConfigException;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class Playlister
{
    /**
     * @param array<array-key, string> $criteria
     * @return PlaylistQuery<array-key, Playlist>
     * @throws InvalidConfigException
     */
    public function playlists(array $criteria = []): PlaylistQuery
    {
        $query = Playlist::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }

    /**
     * @param array<array-key, string> $criteria
     * @return VideoQuery<array-key, Video>
     * @throws InvalidConfigException
     */
    public function videos(array $criteria = []): VideoQuery
    {
        $query = Video::find();

        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }
}
