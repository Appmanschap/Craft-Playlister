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
        // Jobs queued for drafts before this check existed have no (or a half-edited) playlist ID, don't run or re-queue them
        if ($this->playlist->getIsDraft() || $this->playlist->getIsRevision() || $this->playlist->playlistId === '') {
            Craft::warning(
                sprintf('Skipping import for playlist "%s", it is a draft/revision or has no playlist ID.', $this->playlist->name),
                __METHOD__
            );
            return;
        }

        try {
            Playlister::getInstance()->playlistImport->import($this->playlist);

            if ($this->playlist->refreshInterval > 0) {
                // Queue up a new job
                Craft::$app->getQueue()->delay($this->playlist->refreshInterval * 60)->push(new ImportPlaylistJob(['playlist' => $this->playlist]));
            }
        } catch (\Throwable $exception) {
            Craft::error(
                sprintf(
                    'Playlister task failed %s, %s',
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                ),
                __METHOD__
            );
            throw $exception;
        }
    }

    protected function defaultDescription(): ?string
    {
        return Craft::t('craft-playlister', 'Import YouTube playlist: "{title}"', ['title' => $this->playlist->name]);
    }
}
