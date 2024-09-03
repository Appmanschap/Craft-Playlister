<?php

namespace appmanschap\youtubeplaylistimporter\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%youtube_playlists}}');
        $this->dropTableIfExists('{{%youtube_playlist_videos}}');

        return true;
    }

    /**
     * @return void
     */
    private function createTables(): void
    {
        $this->archiveTableIfExists('{{%youtube_playlists}}');
        $this->createTable('{{%youtube_playlists}}', [
            'id' => $this->primaryKey(),
            'playlistId' => $this->string()->notNull(),
            'youtubeUrl' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'refreshInterval' => $this->integer()->unsigned()->defaultValue(5),
            'limit' => $this->integer()->unsigned()->defaultValue(50),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%youtube_playlist_videos}}');
        $this->createTable('{{%youtube_playlist_videos}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->mediumText()->notNull(),
            'datePublished' => $this->dateTime()->notNull(),

            'videoId' => $this->string()->notNull(),
            'playlistId' => $this->string()->notNull(),
            'channelId' => $this->string()->notNull(),
            'channelTitle' => $this->string()->notNull(),
            'defaultAudioLanguage' => $this->string()->null(),
            'defaultLanguage' => $this->string()->null(),
            'thumbnail' => $this->string(),
            'embeddable' => $this->boolean()->defaultValue(false),
            'tags' => $this->mediumText()->notNull(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);
    }
}
