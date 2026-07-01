<?php

namespace App\Support;

use DateTimeZone;
use ResourceBundle;

class RegistrationOptions
{
    /**
     * CLDR also contains non-ISO regions used for grouping and localization.
     *
     * @var list<string>
     */
    private const NON_ISO_REGION_CODES = [
        'AC',
        'CP',
        'CQ',
        'DG',
        'EA',
        'EU',
        'EZ',
        'IC',
        'QO',
        'TA',
        'UN',
        'XA',
        'XB',
        'XK',
        'ZZ',
    ];

    /**
     * @return array<string, string>
     */
    public static function countries(): array
    {
        $resourceBundle = ResourceBundle::create('en', 'ICUDATA-region');

        if (! $resourceBundle instanceof ResourceBundle) {
            return [];
        }

        $countryNames = $resourceBundle->get('Countries');

        if (! $countryNames instanceof ResourceBundle) {
            return [];
        }

        $countries = [];

        foreach ($countryNames as $countryCode => $countryName) {
            if (
                ! is_string($countryCode)
                || ! is_string($countryName)
                || preg_match('/^[A-Z]{2}$/', $countryCode) !== 1
                || in_array($countryCode, self::NON_ISO_REGION_CODES, true)
            ) {
                continue;
            }

            $countries[$countryCode] = $countryName;
        }

        asort($countries, SORT_NATURAL | SORT_FLAG_CASE);

        return $countries;
    }

    /**
     * @return array<string, string>
     */
    public static function timezones(): array
    {
        $timezoneIdentifiers = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
        $timezones = array_combine($timezoneIdentifiers, $timezoneIdentifiers);

        return ['UTC' => 'UTC'] + $timezones;
    }
}
