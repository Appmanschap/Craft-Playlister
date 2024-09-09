<?php

namespace appmanschap\craftplaylister\migrations;

use appmanschap\craftplaylister\elements\Playlist;
use appmanschap\craftplaylister\jobs\ImportPlaylistJob;
use Craft;
use craft\db\Migration;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class Install extends Migration
{
    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }

    public function safeDown(): bool
    {
        Playlist::find()->collect()->each(function(Playlist $playlist) {
            $playlist->releaseJobs(ImportPlaylistJob::class);
            Craft::$app->getElements()->deleteElement($playlist, true);
        });

        $this->dropTableIfExists('{{%playlister_playlists}}');
        $this->dropTableIfExists('{{%playlister_videos}}');

        return true;
    }

    /**
     * @return void
     */
    private function createTables(): void
    {
        $this->archiveTableIfExists('{{%playlister_playlists}}');
        $this->createTable('{{%playlister_playlists}}', [
            'id' => $this->primaryKey(),
            'playlistId' => $this->string()->notNull(),
            'youtubeUrl' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'refreshInterval' => $this->integer()->unsigned()->defaultValue(5),
            'limit' => $this->integer()->unsigned()->defaultValue(50),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%playlister_videos}}');
        $this->createTable('{{%playlister_videos}}', [
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
