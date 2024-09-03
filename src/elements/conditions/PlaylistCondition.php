<?php

namespace appmanschap\craftplaylister\elements\conditions;

use craft\elements\conditions\ElementCondition;

/**
 * Playlist condition
 *
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class PlaylistCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            // ...
        ]);
    }
}
