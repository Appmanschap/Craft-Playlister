<?php

namespace appmanschap\youtubeplaylistimporter\elements\conditions;

use appmanschap\youtubeplaylistimporter\elements\conditions\rules\EmbeddableConditionRule;
use craft\elements\conditions\ElementCondition;
use craft\errors\InvalidTypeException;

/**
 * Video condition
 */
class VideoCondition extends ElementCondition
{
    /**
     * @throws InvalidTypeException
     */
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            EmbeddableConditionRule::class,
        ]);
    }
}
