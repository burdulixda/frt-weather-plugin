<?php
/**
 * Plugin Name:       FRT Weather Plugin
 * Plugin URI:        https://github.com/burdulixda/frt-weather-plugin
 * Description:       Simple weather plugin based on OpenWeather API.
 * Version:           1.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Giorgi Burduli
 * Author URI:        https://github.com/burdulixda
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       frt-weather-plugin
 */

include(plugin_dir_path(__FILE__) . 'includes/frt-weather-settings.php');
include(plugin_dir_path(__FILE__) . 'includes/frt-weather-widget.php');

$settings = new \FRTWP\Settings;
$widget = new \FRTWP\Widget;
