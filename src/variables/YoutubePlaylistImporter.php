<?php
namespace appmanschap\youtubeplaylistimporter\variables;

use appmanschap\youtubeplaylistimporter\elements\db\PlaylistQuery;
use appmanschap\youtubeplaylistimporter\elements\db\VideoQuery;
use appmanschap\youtubeplaylistimporter\elements\Playlist;
use appmanschap\youtubeplaylistimporter\elements\Video;
use craft\elements\db\ElementQueryInterface;
use yii\base\InvalidConfigException;

class YoutubePlaylistImporter
{
    /**
     * @return PlaylistQuery|ElementQueryInterface
     */
    public function playlists(): PlaylistQuery
    {
        return Playlist::find();
    }

    /**
     * @param array $tags
     * @return VideoQuery
     * @throws InvalidConfigException
     */
    public function videos(array $tags = []): VideoQuery
    {
        return Video::find();
    }
}