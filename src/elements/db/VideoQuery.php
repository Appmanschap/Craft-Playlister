<?php

namespace appmanschap\youtubeplaylistimporter\elements\db;

use craft\base\ElementInterface;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * @template TKey of array-key
 * @template TElement of ElementInterface
 * @extends ElementQuery<TKey, TElement>
 */
class VideoQuery extends ElementQuery
{
    /**
     * @var string|null
     */
    public ?string $playlistId = null;

    /**
     * @var bool|null
     */
    public ?bool $embeddable = null;

    /**
     * @var array<string-key, string>
     */
    public array $tags = [];

    /**
     * @return bool
     * @throws QueryAbortedException
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable("{{%youtube_playlist_videos}}");

        $this->query?->select([
            "{{%youtube_playlist_videos}}.*",
        ]);

        if ($this->playlistId) {
            $this->subQuery?->andWhere(Db::parseParam('{{%youtube_playlist_videos}}.playlistId', $this->playlistId) ?? []);
        }

        if (!is_null($this->embeddable)) {
            $this->subQuery?->andWhere(Db::parseBooleanParam('{{%youtube_playlist_videos}}.embeddable', $this->embeddable));
        }

        if ($this->tags) {
            array_map(fn($tag) => $this->subQuery?->andWhere(Db::parseParam('{{%youtube_playlist_videos}}.playlistId', $tag, 'like') ?? []), $this->tags);
        }

        return parent::beforePrepare();
    }

    /**
     * @param string $playlistId
     * @return $this
     */
    public function playlistId(string $playlistId): static
    {
        $this->playlistId = $playlistId;
        return $this;
    }

    /**
     * @param bool|null $embeddable
     * @return $this
     */
    public function embeddable(?bool $embeddable): static
    {
        $this->embeddable = $embeddable;
        return $this;
    }

    /**
     * @param array<array-key, string> $tags
     * @return $this
     */
    public function tags(array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }
}
