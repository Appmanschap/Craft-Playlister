<?php

namespace appmanschap\youtubeplaylistimporter\variables;

use appmanschap\youtubeplaylistimporter\elements\db\PlaylistQuery;
use appmanschap\youtubeplaylistimporter\elements\db\VideoQuery;
use appmanschap\youtubeplaylistimporter\elements\Playlist;
use appmanschap\youtubeplaylistimporter\elements\Video;
use yii\base\InvalidConfigException;

class YoutubePlaylistImporter
{
    /**
     * @return PlaylistQuery<array-key, Playlist>
     */
    public function playlists(): PlaylistQuery
    {
        return Playlist::find();
    }

    /**
     * @param array<array-key, string> $tags
     * @return VideoQuery<array-key, Video>
     * @throws InvalidConfigException
     */
    public function videos(array $tags = []): VideoQuery
    {
        return Video::find()->tags($tags);
    }
}
