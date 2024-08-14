<?php

namespace appmanschap\youtubeplaylistimporter\console\controllers;

use appmanschap\youtubeplaylistimporter\elements\db\PlaylistQuery;
use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use appmanschap\youtubeplaylistimporter\YoutubePlaylistImporter;
use craft\console\Controller;
use craft\helpers\Console;
use yii\console\ExitCode;

/**
 * Playlist controller
 */
class PlaylistController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'import';

    /**
     * @var string|null
     */
    public ?string $playlistId = null;

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        return match ($actionID) {
            'import' => array_merge($options, ['playlistId']),
            default => $options,
        };
    }

    /**
     * youtube-playlist-importer/playlist command
     */
    public function actionImport(): int
    {
        if (empty($this->playlistId)) {
            $this->stderr('You must provide a --playlistId option.' . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        /** @var PlaylistQuery<array-key, PlaylistElement> $query */
        $query = PlaylistElement::find();
        $query->playlistId($this->playlistId);

        if ($query->count() === 0) {
            $this->stderr('No playlists exist for the given playlistId');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $query->collect()->each(function($playlist) {
            $this->stdout("Import videos for playlist '{$playlist->name}'..." . PHP_EOL, Console::FG_YELLOW);
            YoutubePlaylistImporter::getInstance()->playlistImport->import($playlist);
        });

        $this->stdout("Done." . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
