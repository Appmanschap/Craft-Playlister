<?php

namespace appmanschap\youtubeplaylistimporter\fields;

use appmanschap\youtubeplaylistimporter\elements\db\PlaylistQuery;
use appmanschap\youtubeplaylistimporter\elements\db\VideoQuery;
use appmanschap\youtubeplaylistimporter\elements\Video as VideoElement;
use appmanschap\youtubeplaylistimporter\YoutubePlaylistImporter;
use Craft;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\ElementCollection;
use craft\fields\BaseRelationField;

/**
 * Playlists field type
 */
class Videos extends BaseRelationField
{
    public static function displayName(): string
    {
        return Craft::t('youtube-playlist-importer', 'YouTube Video ({pluginName})', ['pluginName' => YoutubePlaylistImporter::$plugin?->name]);
    }

    public static function icon(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><svg width="29px" height="20px" viewBox="0 0 29 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="i-youtube" transform="translate(0.001361, 0.001376)" fill="#FF0000" fill-rule="nonzero">
            <path d="M14.4295903,-0.00111948195 C14.4533552,-0.00105528227 14.4798805,-0.000975032665 14.5090611,-0.000874720662 L14.8405462,0.000678109137 C15.7238954,0.00581408367 17.5433211,0.0237899945 19.4582746,0.0867056825 L20.0347859,0.106960682 C20.1310686,0.110571914 20.2273799,0.114303521 20.3236149,0.118159514 L20.8992676,0.142820217 C22.8094506,0.230212033 24.6050241,0.370568587 25.4454392,0.595989719 C26.6754392,0.925246719 27.6421392,1.89185372 27.9713392,3.12186372 C28.5193655,5.17321372 28.5646667,9.27957757 28.5684039,9.91516539 L28.5680424,10.1449326 C28.5621198,10.9506459 28.505855,14.8826658 27.9713392,16.8754237 C27.6421392,18.1054237 26.6754392,19.0720237 25.4454392,19.4012237 C24.4789618,19.6604728 22.2492875,19.8072175 20.0347859,19.8902806 L19.4582746,19.9105367 C17.2560781,19.9828939 15.180217,19.9958148 14.5090611,19.9981221 L14.0582202,19.9981221 C12.6575649,19.9933069 5.13894692,19.9422652 3.12186918,19.4012237 C1.89186918,19.0720237 0.925262183,18.1054237 0.596005183,16.8754237 C0.0929601304,14.9998869 0.0135319642,11.4065262 0.00099067477,10.3185393 L-0.000664078692,10.1449326 C-0.000838263267,10.1212352 -0.000968901698,10.1002421 -0.00106688052,10.0820824 L-0.00106688052,9.91516539 C-0.000968901698,9.89700573 -0.000838263267,9.8760128 -0.000664078692,9.85231545 L0.00099067477,9.67870958 C0.0135319642,8.59072754 0.0929601304,4.99738372 0.596005183,3.12186372 C0.925262183,1.89185372 1.89186918,0.925246719 3.12186918,0.595989719 C5.01287956,0.0887921722 11.739228,0.0122340518 13.7267392,0.000678109137 L14.0582202,-0.000874720662 C14.0874005,-0.000975032665 14.1139254,-0.00105528227 14.1376899,-0.00111948195 Z M11.4239392,5.71395372 L11.4239392,14.2840237 L18.8463392,9.99902372 L11.4239392,5.71395372 Z" id="Combined-Shape"></path>
        </g>
    </g>
</svg>';
    }

    public static function elementType(): string
    {
        return VideoElement::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('youtubeplaylistimporter', 'Add a YouTube video');
    }

    /**
     * @return string
     */
    public static function phpType(): string
    {
        return sprintf('\\%s|\\%s<\\%s>', VideoQuery::class, ElementCollection::class, VideoElement::class);
    }

    /**
     * @return ElementConditionInterface
     */
    protected function createSelectionCondition(): ElementConditionInterface
    {
        $condition = VideoElement::createCondition();
        $condition->queryParams = ['id', 'videoId'];
        return $condition;
    }
}
