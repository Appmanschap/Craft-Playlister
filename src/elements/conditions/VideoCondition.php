<?php

namespace appmanschap\youtubeplaylistimporter\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * Video condition
 */
class VideoCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::conditionRuleTypes(), [
            // ...
        ]);
    }
}
