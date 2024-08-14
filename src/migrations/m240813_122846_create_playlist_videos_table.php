<?php

namespace appmanschap\youtubeplaylistimporter\migrations;

use craft\db\Migration;

/**
 * m240813_122846_create_playlist_videos_table migration.
 */
class m240813_122846_create_playlist_videos_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%youtube_playlist_videos}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->mediumText()->notNull(),
            'datePublished' => $this->dateTime()->notNull(),

            'videoId' => $this->string()->notNull(),
            'playlistId' => $this->string()->notNull(),
            'channelId' => $this->string()->notNull(),
            'channelTitle' => $this->string()->notNull(),
            'defaultAudioLanguage' => $this->string()->notNull(),
            'defaultLanguage' => $this->string()->null(),
            'embeddable' => $this->boolean()->defaultValue(false),
            'tags' => $this->mediumText()->notNull(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240813_122846_create_playlist_videos_table cannot be reverted.\n";
        return false;
    }
}
