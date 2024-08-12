<?php

namespace appmanschap\youtubeplaylistimporter\elements\conditions;

use craft\elements\conditions\ElementCondition;

/**
 * Playlist condition
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
