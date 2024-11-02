<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\spark;

use Craft;
use nystudio107\autocomplete\events\DefineGeneratorValuesEvent;
use nystudio107\autocomplete\generators\AutocompleteTwigExtensionGenerator;
use putyourlightson\spark\assets\DatastarAssetBundle;
use putyourlightson\spark\models\SettingsModel;
use putyourlightson\spark\models\StoreModel;
use putyourlightson\spark\services\EventsService;
use putyourlightson\spark\services\ResponseService;
use putyourlightson\spark\twigextensions\SparkTwigExtension;
use yii\base\Event;
use yii\base\Module;

/**
 * @property-read EventsService $events
 * @property-read ResponseService $response
 * @property-read SettingsModel $settings
 */
class Spark extends Module
{
    public const DATASTAR_VERSION = '0.19.9';

    public const ID = 'spark-module';
    private ?SettingsModel $_settings = null;

    /**
     * The bootstrap process creates an instance of the module.
     */
    public static function bootstrap(): void
    {
        static::getInstance();
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): Spark
    {
        if ($module = Craft::$app->getModule(self::ID)) {
            /** @var Spark $module */
            return $module;
        }

        $module = new Spark(self::ID);
        static::setInstance($module);
        Craft::$app->setModule(self::ID, $module);
        Craft::setAlias('@putyourlightson/spark', __DIR__);

        return $module;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->registerComponents();
        $this->registerTwigExtension();
        $this->registerScript();
        $this->registerAutocompleteEvent();
    }

    public function getSettings(): SettingsModel
    {
        if ($this->_settings === null) {
            $this->_settings = new SettingsModel(Craft::$app->getConfig()->getConfigFromFile('spark'));
        }

        return $this->_settings;
    }

    private function registerComponents(): void
    {
        $this->setComponents([
            'events' => EventsService::class,
            'response' => ResponseService::class,
        ]);
    }

    private function registerTwigExtension(): void
    {
        Craft::$app->getView()->registerTwigExtension(new SparkTwigExtension());
    }

    private function registerScript(): void
    {
        if (!$this->settings->registerScript) {
            return;
        }

        $bundle = Craft::$app->getView()->registerAssetBundle(DatastarAssetBundle::class);

        // Register the JS file explicitly so that it will be output when using template caching.
        $url = Craft::$app->getView()->getAssetManager()->getAssetUrl($bundle, $bundle->js[0]);
        Craft::$app->getView()->registerJsFile($url, $bundle->jsOptions);
    }

    private function registerAutocompleteEvent(): void
    {
        if (!class_exists('nystudio107\autocomplete\generators\AutocompleteTwigExtensionGenerator')) {
            return;
        }

        Event::on(AutocompleteTwigExtensionGenerator::class,
            AutocompleteTwigExtensionGenerator::EVENT_BEFORE_GENERATE,
            function(DefineGeneratorValuesEvent $event) {
                $event->values[$this->settings->storeVariableName] = 'new \\' . StoreModel::class . '()';
            }
        );
    }
}
