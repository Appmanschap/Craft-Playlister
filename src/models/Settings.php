<?php

namespace appmanschap\youtubeplaylistimporter\models;

use Craft;
use craft\base\Model;

/**
 * Youtube Playlist Importer settings
 */
class Settings extends Model
{
    public string|null $youtubeApiKey = null;

    public function rules(): array
    {
        return [
            ['youtubeApiKey', 'string'],
            ['youtubeApiKey', 'default', 'value' => ''],
        ];
    }
}
