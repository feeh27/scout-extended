<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\LaravelScoutExtended\Settings;

use function is_string;

/**
 * @internal
 */
final class SettingsFactory
{
    /**
     * @var \Algolia\LaravelScoutExtended\Settings\SettingsDiscover
     */
    private $settingsDiscover;

    /**
     * @var string[]
     */
    private static $customRankingKeys = [
        'id',
        'id_*',
        '*_id',
    ];

    /**
     * @var string[]
     */
    private static $unsearchableAttributesKeys = [
        '*image*',
        '*url*',
        '*link*',
        '*password*',
        '*token*',
        '*hash*',
    ];

    /**
     * @var string[]
     */
    private static $attributesForFacetingKeys = [
        '*category*',
        '*list*',
        '*country*',
        '*city*',
        '*type*',
    ];

    /**
     * @var string[]
     */
    private static $unretrievableAttributes = [
        '*password*',
        '*token*',
        '*secret*',
    ];

    /**
     * @var string[]
     */
    private static $unsearchableAttributesValues = [
        'http://*',
        'https://*',
    ];

    /**
     * @var string[]
     */
    private static $disableTypoToleranceOnAttributesKeys = [
        'id',
        'id_*',
        '*_id',
        '*code*',
        '*sku*',
        '*reference*',
    ];

    /**
     * SettingsFactory constructor.
     *
     * @param \Algolia\LaravelScoutExtended\Settings\SettingsDiscover $settingsDiscover
     */
    public function __construct(SettingsDiscover $settingsDiscover)
    {
        $this->settingsDiscover = $settingsDiscover;
    }

    /**
     * Creates settings for the given model.
     *
     * @param string $model
     *
     * @return \Algolia\LaravelScoutExtended\Settings\Settings
     */
    public function create(string $model): Settings
    {
        $instance = factory($model)->make();

        $attributes = array_intersect_key($instance->toArray(), $instance->toSearchableArray());
        $searchableAttributes = [];
        $attributesForFaceting = [];
        $customRanking = [];
        $disableTypoToleranceOnAttributes = [];
        $unretrievableAttributes = [];
        foreach ($attributes as $key => $value) {
            if ($this->isSearchableAttributes($key, $value)) {
                $searchableAttributes[] = $key;
            }

            if ($this->isAttributesForFaceting($key, $value)) {
                $attributesForFaceting[] = $key;
            }

            if ($this->isCustomRanking($key, $value)) {
                $customRanking[] = "desc({$key})";
            }

            if ($this->isDisableTypoToleranceOnAttributes($key, $value)) {
                $disableTypoToleranceOnAttributes[] = $key;
            }

            if ($this->isUnretrievableAttributes($key, $value)) {
                $unretrievableAttributes[] = $key;
            }
        }

        $detectedSettings = [
            'searchableAttributes' => $searchableAttributes,
            'attributesForFaceting' => ! empty($attributesForFaceting) ? $attributesForFaceting : null,
            'customRanking' => ! empty($customRanking) ? $customRanking : null,
            'disableTypoToleranceOnAttributes' => ! empty($disableTypoToleranceOnAttributes) ? $disableTypoToleranceOnAttributes : null,
            'unretrievableAttributes' => $unretrievableAttributes,
        ];

        return new Settings($detectedSettings, $this->settingsDiscover->defaults());
    }

    /**
     * Checks if the given key/value is a 'searchableAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isSearchableAttributes(string $key, $value): bool
    {
        return ! str_is(self::$unsearchableAttributesKeys, $key) && ! str_is(self::$unsearchableAttributesValues, $value);
    }

    /**
     * Checks if the given key/value is a 'attributesForFaceting'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isAttributesForFaceting(string $key, $value): bool
    {
        return str_is(self::$attributesForFacetingKeys, $key);
    }

    /**
     * Checks if the given key/value is a 'customRanking'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isCustomRanking(string $key, $value): bool
    {
        return str_is(self::$customRankingKeys, $key);
    }

    /**
     * Checks if the given key/value is a 'disableTypoToleranceOnAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isDisableTypoToleranceOnAttributes(string $key, $value): bool
    {
        return is_string($key) && str_is(self::$disableTypoToleranceOnAttributesKeys, $key);
    }

    /**
     * Checks if the given key/value is a 'unretrievableAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isUnretrievableAttributes(string $key, $value): bool
    {
        return is_string($key) && str_is(self::$unretrievableAttributes, $key);
    }
}
