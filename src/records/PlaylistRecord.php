<?php

namespace appmanschap\craftplaylister\records;

use appmanschap\craftplaylister\elements\Playlist as PlaylistElement;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\Element;
use yii\base\Exception;
use yii\db\ActiveQueryInterface;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * Playlist Record record
 *
 * @property int|null $id
 * @property string $playlistId
 * @property string $youtubeUrl
 * @property string $name
 * @property int $refreshInterval
 * @property int $limit
 * @property string|null $uid
 */
class PlaylistRecord extends ActiveRecord
{
//    use SoftDeleteTrait;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%playlister_playlists}}';
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
        $this->playlistId = $playlistElement->playlistId;
        $this->youtubeUrl = $playlistElement->youtubeUrl;
        $this->name = $playlistElement->name;
        $this->refreshInterval = $playlistElement->refreshInterval;
        $this->limit = $playlistElement->limit;
        $this->uid = $playlistElement->uid;
        return $this;
    }
}
