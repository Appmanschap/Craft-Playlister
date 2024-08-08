<?php

namespace appmanschap\youtubeplaylistimporter\models;

use craft\base\Model;

/**
 * Youtube Playlist Importer settings
 */
class Settings extends Model
{
    public string|null $youtubeApiKey = null;

    /**
     * @return array<int, array<int|string, string>>
     */
    public function rules(): array
    {
        return [
            ['youtubeApiKey', 'string'],
            ['youtubeApiKey', 'default', 'value' => ''],
        ];
    }
}
