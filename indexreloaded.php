<?php
/**
 * Plugin Name: IndexReloaded
 * Plugin URI: https://www.toctoc.ch/getindexreloaded/
 * Description:  Performs CSS/JS optimisations, improves webpage performance
 * Version: 1.2.1
 * Author: TocToc Internetmanagement, Gisele Wendl
 * Author URI: https://www.toctoc.ch/
 * Text Domain:       indexreloaded
 * Requires at least: 5.6.0
 * Tested up to: 6.6
 * Requires PHP: 7.0
 * Requires PHP Architecture: 64 bits
 * License:           GNU General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 *  @package Indexreloaded
 */

defined( 'ABSPATH' ) || die( '-1' );
define( 'IRLD_FILE', __FILE__ );
define( 'IRLD_PATH', __DIR__ );
define( 'IRLD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
require_once 'include/class-indexreloadedroot.php';
