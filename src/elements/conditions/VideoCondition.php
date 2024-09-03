<?php

namespace appmanschap\craftplaylister\elements\conditions;

use appmanschap\craftplaylister\elements\conditions\rules\EmbeddableConditionRule;
use craft\elements\conditions\ElementCondition;
use craft\errors\InvalidTypeException;

/**
 * Video condition
 *
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
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
