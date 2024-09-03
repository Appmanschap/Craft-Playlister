<?php

namespace appmanschap\craftplaylister\elements\conditions\rules;

use appmanschap\craftplaylister\elements\db\VideoQuery;
use appmanschap\craftplaylister\elements\Video;
use Craft;
use craft\base\conditions\BaseLightswitchConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class EmbeddableConditionRule extends BaseLightswitchConditionRule implements ElementConditionRuleInterface
{
    public function getLabel(): string
    {
        return Craft::t('craftplaylister', 'Embeddable');
    }

    public function getExclusiveQueryParams(): array
    {
        return ['embeddable'];
    }

    public function modifyQuery(ElementQueryInterface $query): void
    {
        /** @var VideoQuery<array-key, Video> $query */
        $query->embeddable($this->value);
    }

    public function matchElement(ElementInterface $element): bool
    {
        /** @var Video $element */
        return $this->matchValue($element->embeddable);
    }
}
