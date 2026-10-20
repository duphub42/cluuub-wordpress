<?php
/** Core plugin behavior. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Cluuub_Embed {
	const OPTION = 'cluuub_embed_settings';
	const CAPABILITY = 'manage_options';
	const DEFAULT_ORIGIN = 'https://cluuub.app';
	const CACHE_SECONDS = 300;
	const WIDGETS = array( 'angebote', 'trainer', 'hallen', 'vorstand', 'sponsoren' );

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_shortcode( 'cluuub', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_filter( 'wp_get_default_privacy_policy_content', array( __CLASS__, 'privacy_policy_content' ) );
	}

	public static function register_block() {
		register_block_type( dirname( __DIR__ ) . '/blocks/embed', array( 'render_callback' => array( __CLASS__, 'render_block' ) ) );
		wp_set_script_translations( 'cluuub-embed-editor-script', 'cluuub-embed', dirname( __DIR__ ) . '/languages' );
	}

	public static function render_block( $attributes ) {
		return self::shortcode( array(
			'club' => $attributes['club'] ?? '', 'type' => $attributes['type'] ?? 'angebote',
			'limit' => $attributes['limit'] ?? 12, 'filter' => $attributes['filter'] ?? '',
		) );
	}

	public static function defaults() { return array( 'origin' => self::DEFAULT_ORIGIN, 'show_credit' => 0 ); }

	public static function settings() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	public static function admin_menu() {
		add_options_page( __( 'cluuub Embed', 'cluuub-embed' ), __( 'cluuub Embed', 'cluuub-embed' ), self::CAPABILITY, 'cluuub-embed', array( __CLASS__, 'settings_page' ) );
	}

	public static function register_settings() {
		register_setting( 'cluuub_embed', self::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ), 'default' => self::defaults() ) );
	}

	public static function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		$origin = isset( $input['origin'] ) ? esc_url_raw( trim( $input['origin'] ), array( 'https' ) ) : self::DEFAULT_ORIGIN;
		$parts = wp_parse_url( $origin );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || 'https' !== ( $parts['scheme'] ?? '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || ( isset( $parts['path'] ) && '/' !== $parts['path'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) { $origin = self::DEFAULT_ORIGIN; }
		return array( 'origin' => untrailingslashit( $origin ), 'show_credit' => empty( $input['show_credit'] ) ? 0 : 1 );
	}

	public static function settings_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) { return; }
		$s = self::settings();
		?>
		<div class="wrap"><h1><?php echo esc_html__( 'cluuub Embed', 'cluuub-embed' ); ?></h1>
		<p><?php echo esc_html__( 'Display your club’s published cluuub information in the WordPress block editor or with a shortcode.', 'cluuub-embed' ); ?></p>
		<form action="options.php" method="post"><?php settings_fields( 'cluuub_embed' ); ?>
		<table class="form-table"><tbody><tr><th><label for="cluuub-origin"><?php esc_html_e( 'cluuub origin', 'cluuub-embed' ); ?></label></th><td><input class="regular-text" id="cluuub-origin" name="<?php echo esc_attr( self::OPTION ); ?>[origin]" type="url" value="<?php echo esc_attr( $s['origin'] ); ?>" required><p class="description"><?php esc_html_e( 'HTTPS address of your cluuub environment. Default: https://cluuub.app', 'cluuub-embed' ); ?></p></td></tr>
		<tr><th><?php esc_html_e( 'Credit link', 'cluuub-embed' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[show_credit]" value="1" <?php checked( 1, (int) $s['show_credit'] ); ?>> <?php esc_html_e( 'Show a “Provided by cluuub” link below the public widget.', 'cluuub-embed' ); ?></label><p class="description"><?php esc_html_e( 'Optional. Disabled by default.', 'cluuub-embed' ); ?></p></td></tr></tbody></table><?php submit_button(); ?></form>
		<h2><?php esc_html_e( 'Embedding', 'cluuub-embed' ); ?></h2><p><code>[cluuub club="club-slug" type="angebote"]</code></p>
		<p><?php esc_html_e( 'Types: angebote, trainer, hallen, vorstand, and sponsoren. Find the club slug in its public cluuub URL.', 'cluuub-embed' ); ?></p>
		<p><?php esc_html_e( 'Public data is fetched by the server and cached for five minutes. Visitors’ browsers do not connect directly to cluuub to display the widget.', 'cluuub-embed' ); ?></p></div>
		<?php
	}

	public static function register_assets() { wp_register_style( 'cluuub-embed', plugins_url( '../assets/embed.css', __FILE__ ), array(), CLUUUB_EMBED_VERSION ); }

	private static function fetch_showcase( $club ) {
		$origin = self::settings()['origin'];
		$cache_key = 'cluuub_showcase_' . md5( $origin . '|' . $club );
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) { return $cached; }
		$url = trailingslashit( $origin ) . 'api/v1/public/' . rawurlencode( $club ) . '/showcase';
		$response = wp_safe_remote_get( $url, array( 'timeout' => 8, 'redirection' => 0, 'limit_response_size' => 1048576, 'headers' => array( 'Accept' => 'application/json' ) ) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { return new WP_Error( 'cluuub_unavailable', __( 'cluuub content is currently unavailable.', 'cluuub-embed' ) ); }
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) || empty( $decoded['data'] ) || ! is_array( $decoded['data'] ) ) { return new WP_Error( 'cluuub_invalid_response', __( 'cluuub returned no valid public content.', 'cluuub-embed' ) ); }
		set_transient( $cache_key, $decoded['data'], self::CACHE_SECONDS );
		return $decoded['data'];
	}

	private static function list_for_widget( $data, $type ) {
		$map = array( 'angebote' => 'groups', 'trainer' => 'trainers', 'hallen' => 'venues', 'vorstand' => 'boardMembers', 'sponsoren' => 'sponsors' );
		$visibility = array( 'trainer' => 'showTrainers', 'hallen' => 'showVenues', 'vorstand' => 'showBoard', 'sponsoren' => 'showSponsors' );
		if ( isset( $visibility[ $type ] ) && empty( $data['settings'][ $visibility[ $type ] ] ) ) { return array(); }
		$items = isset( $data[ $map[ $type ] ] ) && is_array( $data[ $map[ $type ] ] ) ? $data[ $map[ $type ] ] : array();
		return array_values( array_filter( $items, 'is_array' ) );
	}

	private static function item_search_text( $item, $type ) {
		$keys = array( 'angebote' => array( 'name', 'sport', 'description' ), 'trainer' => array( 'fullName', 'bio' ), 'hallen' => array( 'name', 'address' ), 'vorstand' => array( 'fullName', 'roleTitle' ), 'sponsoren' => array( 'companyName', 'services' ) );
		$text = '';
		foreach ( $keys[ $type ] as $key ) { if ( isset( $item[ $key ] ) && is_scalar( $item[ $key ] ) ) { $text .= ' ' . (string) $item[ $key ]; } }
		return $text;
	}

	private static function card( $title, $details = array() ) {
		$html = '<article class="cluuub-card"><h3>' . esc_html( is_scalar( $title ) ? (string) $title : '' ) . '</h3>';
		foreach ( $details as $detail ) { if ( is_scalar( $detail ) && '' !== trim( (string) $detail ) ) { $html .= '<p>' . esc_html( (string) $detail ) . '</p>'; } }
		return $html . '</article>';
	}

	private static function render_item( $item, $type ) {
		switch ( $type ) {
			case 'angebote':
				$age = isset( $item['ageMin'] ) && is_numeric( $item['ageMin'] ) ? sprintf( __( 'Ages %s and up', 'cluuub-embed' ), absint( $item['ageMin'] ) ) : __( 'All ages', 'cluuub-embed' );
				$details = array( $item['sport'] ?? '', $item['level'] ?? '', $age, $item['description'] ?? '' );
				foreach ( is_array( $item['schedules'] ?? null ) ? $item['schedules'] : array() as $schedule ) { if ( is_array( $schedule ) ) { $details[] = trim( (string) ( $schedule['startTime'] ?? '' ) . ' · ' . (string) ( $schedule['location'] ?? '' ) ); } }
				return self::card( $item['name'] ?? '', $details );
			case 'trainer': return self::card( $item['fullName'] ?? '', array_merge( is_array( $item['roles'] ?? null ) ? $item['roles'] : array(), array( $item['bio'] ?? '' ) ) );
			case 'hallen': return self::card( $item['name'] ?? '', array( $item['address'] ?? '', $item['accessibilityInfo'] ?? '', $item['parkingInfo'] ?? '' ) );
			case 'vorstand': return self::card( $item['fullName'] ?? '', array( $item['roleTitle'] ?? '' ) );
			case 'sponsoren': return self::card( $item['companyName'] ?? '', array( $item['services'] ?? '' ) );
		}
		return '';
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'club' => '', 'type' => 'angebote', 'limit' => 12, 'filter' => '' ), $atts, 'cluuub' );
		$club = is_string( $atts['club'] ) ? strtolower( trim( sanitize_text_field( $atts['club'] ) ) ) : '';
		$type = is_string( $atts['type'] ) ? sanitize_key( $atts['type'] ) : '';
		if ( ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $club ) || ! in_array( $type, self::WIDGETS, true ) ) { return current_user_can( self::CAPABILITY ) ? '<p class="cluuub-error">' . esc_html__( 'Please check the cluuub club slug and widget type.', 'cluuub-embed' ) . '</p>' : ''; }
		$data = self::fetch_showcase( $club );
		if ( is_wp_error( $data ) ) { return current_user_can( self::CAPABILITY ) ? '<p class="cluuub-error">' . esc_html( $data->get_error_message() ) . '</p>' : ''; }
		$items = self::list_for_widget( $data, $type );
		$filter = is_string( $atts['filter'] ) ? strtolower( sanitize_text_field( $atts['filter'] ) ) : '';
		if ( '' !== $filter ) { $items = array_values( array_filter( $items, static function ( $item ) use ( $filter, $type ) { return false !== strpos( strtolower( self::item_search_text( $item, $type ) ), $filter ); } ) ); }
		$items = array_slice( $items, 0, max( 1, min( 50, absint( $atts['limit'] ) ?: 12 ) ) );
		wp_enqueue_style( 'cluuub-embed' );
		$titles = array( 'angebote' => __( 'Sports activities', 'cluuub-embed' ), 'trainer' => __( 'Coaches', 'cluuub-embed' ), 'hallen' => __( 'Venues', 'cluuub-embed' ), 'vorstand' => __( 'Board contacts', 'cluuub-embed' ), 'sponsoren' => __( 'Sponsors', 'cluuub-embed' ) );
		$html = '<section class="cluuub-widget" aria-label="' . esc_attr( $titles[ $type ] ) . '"><h2>' . esc_html( $titles[ $type ] ) . '</h2>';
		if ( empty( $items ) ) { $html .= '<p>' . esc_html__( 'There is no matching published content yet.', 'cluuub-embed' ) . '</p>'; }
		else { $html .= '<div class="cluuub-grid">'; foreach ( $items as $item ) { $html .= self::render_item( $item, $type ); } $html .= '</div>'; }
		if ( ! empty( self::settings()['show_credit'] ) ) { $html .= '<p class="cluuub-attribution"><a href="' . esc_url( self::settings()['origin'] ) . '" rel="nofollow">' . esc_html__( 'Provided by cluuub', 'cluuub-embed' ) . '</a></p>'; }
		return $html . '</section>';
	}

	public static function privacy_policy_content( $content ) {
		return $content . "\n\n" . __( 'This plugin requests published club information server-side from the configured cluuub environment and stores it in the WordPress cache for up to five minutes. The WordPress server IP address and requested club slug are sent to cluuub. Visitors’ browsers do not connect directly to cluuub to display this widget. Site owners should describe this processing and refer to the cluuub privacy information in their privacy policy.', 'cluuub-embed' );
	}
}
