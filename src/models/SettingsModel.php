<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\spark\models;

use craft\base\Model;

class SettingsModel extends Model
{
    /**
     * Whether to register the Datastar script on the front-end.
     */
    public bool $registerScript = true;

    /**
     * The name of the store variable that will be injected into Spark templates.
     */
    public string $storeVariableName = 'store';

    /**
     * The fragment options to override the Datastar defaults. Null values will be ignored.
     * https://data-star.dev/reference/plugins_backend#datastar-fragment
     *
     */
    public array $defaultFragmentOptions = [
        'selector' => null,
        'merge' => null,
        'settle' => null,
        'vt' => null,
    ];
}
