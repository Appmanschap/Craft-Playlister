<?php

namespace appmanschap\youtubeplaylistimporter\migrations;

use craft\db\Migration;

/**
 * m240812_112201_create_playlist_table migration.
 */
class m240812_112201_create_playlist_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
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

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240812_112201_create_playlist_table cannot be reverted.\n";
        return false;
    }
}
