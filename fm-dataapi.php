<?php
/*
Plugin Name: Integration with FileMaker Data API
Plugin URI: https://msdev.co.uk/wp-fm-data-api
Description: Pull data directly from FileMaker into your WordPress installation using the FileMaker Data API
Version: 0.5.2
Author: Steve Winter - Matatiro Solutions
Author URI: https://msdev.co.uk
License: GPL2 or later
*/

// Only run as part of WordPress
defined( 'ABSPATH' ) or die( 'Access denied - this plugin must be run as part of WordPress!' );
define('FM_DATA_API_SETTINGS', 'fm-dataapi_settings');
define('FM_DATA_API_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define('FM_DATA_API_BASENAME', plugin_basename( __FILE__ ) );

require_once FM_DATA_API_PLUGIN_DIR . '/src/Locales.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/FileMakerDataAPI.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Plugin.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Admin.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/Settings.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ScriptFilter.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ScriptParameter.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeBase.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeField.php';
require_once FM_DATA_API_PLUGIN_DIR . '/src/ShortCodeTable.php';

new FMDataAPI\Plugin();
