const fs = require('fs');
const path = require('path');
const root = path.join(__dirname, '..');
const php = fs.readFileSync(path.join(root, 'includes/class-cluuub-embed.php'), 'utf8');
const block = fs.readFileSync(path.join(root, 'blocks/embed/block.json'), 'utf8');
const checks = [
  ['HTTPS-only origin sanitization', /esc_url_raw[^\n]*https/.test(php) && /'https' !==/.test(php)],
  ['administrator capability gate', /current_user_can\( self::CAPABILITY \)/.test(php)],
  ['restrictive club slug allowlist', /\[a-z0-9\]\+\(\?:-\[a-z0-9\]\+\)\*/.test(php)],
  ['shortcode cannot select a remote host', !/atts\[['"](?:url|src|host)['"]\]/.test(php)],
  ['verified public showcase endpoint', /api\/v1\/public\/.+\/showcase/.test(php)],
  ['response size limited', /limit_response_size' => 1048576/.test(php)],
  ['five-minute cache', /CACHE_SECONDS = 300/.test(php) && /set_transient\([^;]+self::CACHE_SECONDS/s.test(php)],
  ['external content escaped as text', /esc_html\( is_scalar\( \$title \)/.test(php) && /esc_html\( \(string\) \$detail \)/.test(php)],
  ['only reviewed widgets allowed', /const WIDGETS = array\( 'angebote', 'trainer', 'hallen', 'vorstand', 'sponsoren' \)/.test(php)],
  ['dynamic Gutenberg block', /register_block_type\(/.test(php) && /render_callback/.test(php) && /"name": "cluuub\/embed"/.test(block)],
  ['no personal-data write endpoints called', !/trial-booking|courts\/book|contact-relay/.test(php)],
  ['server-side privacy disclosure', /server-side/.test(php) && /WordPress server IP address/.test(php)],
  ['credit link is opt-in and disabled by default', /'show_credit' => 0/.test(php) && /if \( ! empty\( self::settings\(\)\['show_credit'\] \) \)/.test(php)],
];
let failed = false;
for (const [name, ok] of checks) { console.log(`${ok ? 'PASS' : 'FAIL'} ${name}`); failed ||= !ok; }
process.exit(failed ? 1 : 0);
