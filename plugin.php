<?php
/**
 * Plugin Name: Altis Reusable Blocks
 * Version: 1.0.0
 * Description: Adds functionality to reusable blocks to make their usage more robust.
 * Author: Human Made Inc.
 * Author URI: https://humanmade.com
 * Text Domain: altis-reusable-blocks
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Altis\ReusableBlocks;

defined( 'ABSPATH' ) || die;

const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/inc/namespace.php';
require_once __DIR__ . '/inc/categories.php';
require_once __DIR__ . '/inc/connections.php';
require_once __DIR__ . '/inc/rest-api/relationships/class-rest-endpoint.php';
require_once __DIR__ . '/inc/rest-api/search/class-rest-endpoint.php';
require_once __DIR__ . '/inc/rest-api/namespace.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap', 99 );
