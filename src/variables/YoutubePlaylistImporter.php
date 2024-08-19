<?php

namespace appmanschap\youtubeplaylistimporter\variables;

use appmanschap\youtubeplaylistimporter\elements\db\PlaylistQuery;
use appmanschap\youtubeplaylistimporter\elements\db\VideoQuery;
use appmanschap\youtubeplaylistimporter\elements\Playlist;
use appmanschap\youtubeplaylistimporter\elements\Video;
use Craft;
use yii\base\InvalidConfigException;

class YoutubePlaylistImporter
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
