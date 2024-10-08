<?php

namespace appmanschap\craftplaylister\elements\db;

use craft\base\ElementInterface;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 *
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
        $this->joinElementTable("{{%playlister_videos}}");

        $this->query?->select([
            "{{%playlister_videos}}.*",
        ]);

        if ($this->playlistId) {
            $this->subQuery?->andWhere(Db::parseParam('{{%playlister_videos}}.playlistId', $this->playlistId) ?? []);
        }

        if (!is_null($this->embeddable)) {
            $this->subQuery?->andWhere(Db::parseBooleanParam('{{%playlister_videos}}.embeddable', $this->embeddable));
        }

        if ($this->tags) {
            array_map(fn($tag) => $this->subQuery?->andWhere(Db::parseParam('{{%playlister_videos}}.playlistId', $tag, 'like') ?? []), $this->tags);
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
