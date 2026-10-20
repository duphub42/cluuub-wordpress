<?php
define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {
	private $message;
	public function __construct( $code, $message ) { $this->message = $message; }
	public function get_error_message() { return $this->message; }
}

$GLOBALS['cluuub_mock_transients'] = array();
$GLOBALS['cluuub_mock_options'] = array();
$GLOBALS['cluuub_remote_calls'] = 0;

function __( $text ) { return $text; }
function esc_html__( $text ) { return esc_html( $text ); }
function esc_html_e( $text ) { echo esc_html( $text ); }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function esc_url( $url ) { return esc_html( $url ); }
function esc_url_raw( $url, $protocols = array() ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : ''; }
function wp_parse_url( $url ) { return parse_url( $url ); }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function get_option( $key, $default = false ) { return $GLOBALS['cluuub_mock_options'][ $key ] ?? $default; }
function checked( $checked, $current, $echo = true ) { return (string) $checked === (string) $current ? 'checked="checked"' : ''; }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) ); }
function shortcode_atts( $pairs, $atts, $shortcode = '' ) { return array_merge( $pairs, is_array( $atts ) ? $atts : array() ); }
function current_user_can( $capability ) { return true; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_transient( $key ) { return $GLOBALS['cluuub_mock_transients'][ $key ] ?? false; }
function set_transient( $key, $value, $expiration ) { $GLOBALS['cluuub_mock_transients'][ $key ] = $value; }
function wp_safe_remote_get( $url, $args = array() ) {
	$GLOBALS['cluuub_remote_calls']++;
	if ( false === strpos( $url, 'https://cluuub.app/api/v1/public/test-club/showcase' ) ) { return new WP_Error( 'unexpected_url', 'Unexpected URL' ); }
	return array( 'response' => array( 'code' => 200 ), 'body' => json_encode( array( 'data' => array(
		'settings' => array(),
		'groups' => array( array( 'name' => '<img src=x onerror=alert(1)>', 'sport' => 'Rudern', 'ageMin' => 7, 'schedules' => array() ) ),
		'trainers' => array(), 'venues' => array(), 'boardMembers' => array(), 'sponsors' => array(),
	) ) ) );
}
function wp_remote_retrieve_response_code( $response ) { return $response['response']['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function wp_enqueue_style( $handle ) {}
function plugins_url( $path, $file ) { return 'https://example.test/wp-content/plugins/cluuub/' . $path; }

require_once __DIR__ . '/../includes/class-cluuub-embed.php';

$invalid = Cluuub_Embed::shortcode( array( 'club' => '../other', 'type' => 'angebote' ) );
if ( 0 !== $GLOBALS['cluuub_remote_calls'] || false === strpos( $invalid, 'club slug' ) ) {
	fwrite( STDERR, "FAIL invalid slug must not trigger a request\n" );
	exit( 1 );
}

$html = Cluuub_Embed::shortcode( array( 'club' => 'test-club', 'type' => 'angebote' ) );
if ( 1 !== $GLOBALS['cluuub_remote_calls'] || false === strpos( $html, '&lt;img src=x onerror=alert(1)&gt;' ) || false !== strpos( $html, '<img src=x' ) ) {
	fwrite( STDERR, "FAIL API text must be escaped in output\n" );
	exit( 1 );
}
if ( false !== strpos( $html, 'cluuub-attribution' ) ) {
	fwrite( STDERR, "FAIL attribution must be disabled by default\n" );
	exit( 1 );
}

Cluuub_Embed::shortcode( array( 'club' => 'test-club', 'type' => 'angebote' ) );
if ( 1 !== $GLOBALS['cluuub_remote_calls'] ) {
	fwrite( STDERR, "FAIL response should be reused from transient cache\n" );
	exit( 1 );
}

$bad_origin = Cluuub_Embed::sanitize_settings( array( 'origin' => 'http://example.com' ) );
if ( 'https://cluuub.app' !== $bad_origin['origin'] ) {
	fwrite( STDERR, "FAIL non-HTTPS origin should fall back to cluuub.app\n" );
	exit( 1 );
}

$GLOBALS['cluuub_mock_options'][ Cluuub_Embed::OPTION ] = array( 'origin' => 'https://cluuub.app', 'show_credit' => 1 );
$credited_html = Cluuub_Embed::shortcode( array( 'club' => 'test-club', 'type' => 'angebote' ) );
if ( false === strpos( $credited_html, 'cluuub-attribution' ) || false === strpos( $credited_html, 'Provided by cluuub' ) ) {
	fwrite( STDERR, "FAIL attribution should appear when explicitly enabled\n" );
	exit( 1 );
}

echo "PASS PHP behavior smoke tests\n";
