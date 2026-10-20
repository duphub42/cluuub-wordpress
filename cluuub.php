<?php
/**
 * Plugin Name: cluuub Embed
 * Description: Display public cluuub club information in WordPress with a Gutenberg block and shortcode.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: cluuub-embed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLUUUB_EMBED_VERSION', '0.2.0' );
define( 'CLUUUB_EMBED_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-cluuub-embed.php';

add_action( 'plugins_loaded', array( 'Cluuub_Embed', 'init' ) );
