<?php

namespace appmanschap\craftplaylister\migrations;

use craft\db\Migration;

/**
 * m240924_091651_add_privacy_status_to_videos_table migration.
 */
class m240924_091651_add_privacy_status_to_videos_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%playlister_videos}}', 'privacyStatus')) {
            $this->addColumn('{{%playlister_videos}}', 'privacyStatus', $this->string()->defaultValue('public')->after('embeddable'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240924_091651_add_privacy_status_to_videos_table cannot be reverted.\n";
        return false;
    }
}
