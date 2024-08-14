<?php

namespace appmanschap\youtubeplaylistimporter\jobs;

use appmanschap\youtubeplaylistimporter\elements\Playlist as PlaylistElement;
use appmanschap\youtubeplaylistimporter\YoutubePlaylistImporter;
use Craft;
use craft\queue\BaseJob;

/**
 * Import Playlist Job queue job
 */
class ImportPlaylistJob extends BaseJob
{
    /**
     * @var PlaylistElement
     */
    public PlaylistElement $playlist;

    public function execute($queue): void
    {
        try {
            YoutubePlaylistImporter::getInstance()->playlistImport->import($this->playlist);
        } catch (\Throwable $exception) {
            Craft::error(
                sprintf(
                    'Youtube playlist import task failed %s, %s',
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                ),
                __METHOD__
            );
        } finally {
            // Queue up a new job
            Craft::$app->getQueue()->delay($this->playlist->refreshInterval * 60)->push(new ImportPlaylistJob(['playlist' => $this->playlist]));
        }
    }

    protected function defaultDescription(): ?string
    {
        return Craft::t('youtubeplaylistimporter', 'Import YouTube playlist: "{title}"', ['title' => $this->playlist->name]);
    }
}
