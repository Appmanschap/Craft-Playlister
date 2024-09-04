<?php

namespace appmanschap\craftplaylister\jobs;

use appmanschap\craftplaylister\elements\Playlist as PlaylistElement;
use appmanschap\craftplaylister\Playlister;
use Craft;
use craft\queue\BaseJob;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
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
            Playlister::getInstance()->playlistImport->import($this->playlist);
        } catch (\Throwable $exception) {
            Craft::error(
                sprintf(
                    'Playlister task failed %s, %s',
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
        return Craft::t('craft-playlister', 'Import YouTube playlist: "{title}"', ['title' => $this->playlist->name]);
    }
}
