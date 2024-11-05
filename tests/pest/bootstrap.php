<?php

define('PROJECT_VENDOR_DIR', getenv('PROJECT_VENDOR_DIR') ?: dirname(__DIR__, 2) . '/vendor');

require realpath(PROJECT_VENDOR_DIR . '/autoload.php');

// Manually require the Craft class
require realpath(PROJECT_VENDOR_DIR . '/craftcms/cms/src/Craft.php');
