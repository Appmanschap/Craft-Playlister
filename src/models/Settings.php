<?php

namespace appmanschap\craftplaylister\models;

use craft\base\Model;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * Playlister settings
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
