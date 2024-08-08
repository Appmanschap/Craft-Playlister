<?php

namespace appmanschap\youtubeplaylistimporter\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * Playlist Item condition
 */
class PlaylistItemCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::conditionRuleTypes(), [
            // ...
        ]);
    }
}
