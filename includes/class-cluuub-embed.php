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

	public static function defaults() { return array( 'origin' => self::DEFAULT_ORIGIN ); }

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
		return array( 'origin' => untrailingslashit( $origin ) );
	}

	public static function settings_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) { return; }
		$s = self::settings();
		?>
		<div class="wrap"><h1><?php echo esc_html__( 'cluuub Embed', 'cluuub-embed' ); ?></h1>
		<p><?php echo esc_html__( 'Zeigt veröffentlichte cluuub-Vereinsinformationen im WordPress-Blockeditor oder per Shortcode an.', 'cluuub-embed' ); ?></p>
		<form action="options.php" method="post"><?php settings_fields( 'cluuub_embed' ); ?>
		<table class="form-table"><tbody><tr><th><label for="cluuub-origin"><?php esc_html_e( 'cluuub-Adresse', 'cluuub-embed' ); ?></label></th><td><input class="regular-text" id="cluuub-origin" name="<?php echo esc_attr( self::OPTION ); ?>[origin]" type="url" value="<?php echo esc_attr( $s['origin'] ); ?>" required><p class="description"><?php esc_html_e( 'HTTPS-Adresse der cluuub-Umgebung. Standard: https://cluuub.app', 'cluuub-embed' ); ?></p></td></tr></tbody></table><?php submit_button(); ?></form>
		<h2><?php esc_html_e( 'Einbindung', 'cluuub-embed' ); ?></h2><p><code>[cluuub club="vereins-kurzname" type="angebote"]</code></p>
		<p><?php esc_html_e( 'Typen: angebote, trainer, hallen, vorstand, sponsoren und neuigkeiten. Der Vereins-Kurzname steht in der öffentlichen cluuub-Adresse.', 'cluuub-embed' ); ?></p>
		<p><?php esc_html_e( 'Öffentliche Daten werden serverseitig abgerufen und fünf Minuten zwischengespeichert. Besucherbrowser verbinden sich bei der Anzeige nicht direkt mit cluuub.', 'cluuub-embed' ); ?></p></div>
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
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { return new WP_Error( 'cluuub_unavailable', __( 'cluuub-Inhalte sind derzeit nicht erreichbar.', 'cluuub-embed' ) ); }
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) || empty( $decoded['data'] ) || ! is_array( $decoded['data'] ) ) { return new WP_Error( 'cluuub_invalid_response', __( 'cluuub hat keine gültigen öffentlichen Inhalte zurückgegeben.', 'cluuub-embed' ) ); }
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
				$age = isset( $item['ageMin'] ) && is_numeric( $item['ageMin'] ) ? sprintf( __( 'Ab %s Jahren', 'cluuub-embed' ), absint( $item['ageMin'] ) ) : __( 'Alle Altersstufen', 'cluuub-embed' );
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
		if ( ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $club ) || ! in_array( $type, self::WIDGETS, true ) ) { return current_user_can( self::CAPABILITY ) ? '<p class="cluuub-error">' . esc_html__( 'Bitte prüfen Sie den cluuub-Vereinskurznamen und den Widget-Typ.', 'cluuub-embed' ) . '</p>' : ''; }
		$data = self::fetch_showcase( $club );
		if ( is_wp_error( $data ) ) { return current_user_can( self::CAPABILITY ) ? '<p class="cluuub-error">' . esc_html( $data->get_error_message() ) . '</p>' : ''; }
		$items = self::list_for_widget( $data, $type );
		$filter = is_string( $atts['filter'] ) ? strtolower( sanitize_text_field( $atts['filter'] ) ) : '';
		if ( '' !== $filter ) { $items = array_values( array_filter( $items, static function ( $item ) use ( $filter, $type ) { return false !== strpos( strtolower( self::item_search_text( $item, $type ) ), $filter ); } ) ); }
		$items = array_slice( $items, 0, max( 1, min( 50, absint( $atts['limit'] ) ?: 12 ) ) );
		wp_enqueue_style( 'cluuub-embed' );
		$titles = array( 'angebote' => __( 'Sportangebote', 'cluuub-embed' ), 'trainer' => __( 'Trainerteam', 'cluuub-embed' ), 'hallen' => __( 'Sportstätten', 'cluuub-embed' ), 'vorstand' => __( 'Ansprechpersonen', 'cluuub-embed' ), 'sponsoren' => __( 'Sponsoren', 'cluuub-embed' ) );
		$html = '<section class="cluuub-widget" aria-label="' . esc_attr( $titles[ $type ] ) . '"><h2>' . esc_html( $titles[ $type ] ) . '</h2>';
		if ( empty( $items ) ) { $html .= '<p>' . esc_html__( 'Aktuell sind keine passenden Inhalte veröffentlicht.', 'cluuub-embed' ) . '</p>'; }
		else { $html .= '<div class="cluuub-grid">'; foreach ( $items as $item ) { $html .= self::render_item( $item, $type ); } $html .= '</div>'; }
		return $html . '<p class="cluuub-attribution"><a href="' . esc_url( self::settings()['origin'] ) . '" rel="nofollow">' . esc_html__( 'Bereitgestellt mit cluuub', 'cluuub-embed' ) . '</a></p></section>';
	}

	public static function privacy_policy_content( $content ) {
		return $content . "\n\n" . __( 'Dieses Plugin ruft veröffentlichte Vereinsinformationen serverseitig von der konfigurierten cluuub-Umgebung ab und speichert sie bis zu fünf Minuten im WordPress-Cache. Dabei werden die IP-Adresse des WordPress-Servers und die angefragte Vereinskennung an cluuub übermittelt. Besucherbrowser stellen für diese Anzeige keine direkte Verbindung zu cluuub her. Der Website-Betreiber sollte die konkrete Verarbeitung und die cluuub-Datenschutzhinweise in seiner Datenschutzerklärung berücksichtigen.', 'cluuub-embed' );
	}
}
