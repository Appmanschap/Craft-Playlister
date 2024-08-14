<?php

namespace appmanschap\youtubeplaylistimporter\records;

use appmanschap\youtubeplaylistimporter\elements\Video as VideoElement;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\Element;
use DateTime;
use yii\base\Exception;
use yii\db\ActiveQueryInterface;

/**
 * Video Record record
 *
 * @property int|null $id
 * @property string $title
 * @property string $description
 * @property DateTime $datePublished
 * @property string $videoId
 * @property string $playlistId
 * @property string $channelId
 * @property string $channelTitle
 * @property string $defaultAudioLanguage
 * @property string|null $defaultLanguage
 * @property bool $embeddable
 * @property string $tags
 * @property string|null $uid
 */
class VideoRecord extends ActiveRecord
{
    use SoftDeleteTrait;

    public static function tableName()
    {
        return '{{%youtube_playlist_videos}}';
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
     * @return VideoRecord
     * @throws Exception
     */
    public static function findOrFail(int $elementId): VideoRecord
    {
        $record = self::findOne(['id' => $elementId]);

        if (!$record) {
            throw new Exception("Invalid Element ID: {$elementId}");
        }

        return $record;
    }

    /**
     * @param  VideoElement  $videoElement
     * @return VideoRecord
     */
    public function fillByElement(VideoElement $videoElement): VideoRecord
    {
        $this->title = $videoElement->title ?? '';
        $this->description = $videoElement->description;
        $this->datePublished = $videoElement->datePublished;
        $this->videoId = $videoElement->videoId;
        $this->playlistId = $videoElement->playlistId;
        $this->channelId = $videoElement->channelId;
        $this->channelTitle = $videoElement->channelTitle;
        $this->defaultAudioLanguage = $videoElement->defaultAudioLanguage;
        $this->defaultLanguage = $videoElement->defaultLanguage;
        $this->embeddable = $videoElement->embeddable;
        $this->tags = $videoElement->tags;
        $this->uid = $videoElement->uid;
        return $this;
    }
}
