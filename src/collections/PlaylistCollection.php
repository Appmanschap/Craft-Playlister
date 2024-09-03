<?php

namespace appmanschap\craftplaylister\collections;

use appmanschap\craftplaylister\elements\Playlist as PlaylistElement;
use appmanschap\craftplaylister\elements\Video as VideoElement;
use craft\base\ElementInterface;
use craft\elements\ElementCollection;
use Illuminate\Support\Collection;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
 * @template TKey of array-key
 * @template TElement of ElementInterface
 * @extends ElementCollection<TKey,TElement>
 */
class PlaylistCollection extends ElementCollection
{
    /**
     * Returns all videos in a Collection, default sorted by datePublished descending.
     *
     * @param bool|null $embeddable
     * @return Collection<TKey, ElementInterface>
     */
    public function getAllVideos(?bool $embeddable = null): Collection
    {
        return $this->flatMap(static fn(PlaylistElement $playlist) => $playlist->getVideos($embeddable))
            ->sortBy(static fn(VideoElement $video) => $video->datePublished, SORT_REGULAR, true);
    }
}
