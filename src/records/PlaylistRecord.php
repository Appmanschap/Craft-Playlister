<?php

namespace appmanschap\youtubeplaylistimporter\records;

use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\Element;
use yii\base\Exception;
use yii\db\ActiveQueryInterface;

/**
 * Playlist Record record
 *
 * @property int $id
 * @property string $youtubeUrl
 * @property string $name
 * @property int $refreshInterval
 * @property string|null $uid
 */
class PlaylistRecord extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%youtube_playlists}}';
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getElements(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    /**
     * @param  int  $elementId
     * @return PlaylistRecord
     * @throws Exception
     */
    public static function findOrFail(int $elementId): PlaylistRecord
    {
        $record = self::findOne(['id' => $elementId]);

        if (!$record) {
            throw new Exception("Invalid Element ID: {$elementId}");
        }

        return $record;
    }

    /**
     * @param  PlaylistElement  $playlistElement
     * @return $this
     */
    public function fillByElement(PlaylistElement $playlistElement): PlaylistRecord
    {
        $this->youtubeUrl = $playlistElement->youtubeUrl;
        $this->name = $playlistElement->name;
        $this->refreshInterval = $playlistElement->refreshInterval;
        $this->uid = $playlistElement->uid;
        return $this;
    }
}
