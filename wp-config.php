<?php
//Begin Really Simple Security session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple Security cookie settings
//Begin Really Simple Security key
define('RSSSL_KEY', 'BgIKRcDzkr0fcNSIeZ1kOMnzr9eIDaV9Ctt7ekz3jojUcCJcDHUmrxWBKfM5YTo8');
//END Really Simple Security key
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', '7826573db5');
/** MySQL database username */
define('DB_USER', 'sql8613709');
/** MySQL database password */
define('DB_PASSWORD', 'r9@3xgd');
/** MySQL hostname */
define('DB_HOST', 'mysqlsvr76.world4you.com');
/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'vqo-!*fy-ArU*w-rifEdE!iCDo5#@zZH6d=wPjJZ!oxWUVuFP5RCP?D1SAQ%St');
define('SECURE_AUTH_KEY',  'aPhk4V0C0sjJWw7pbU%2?#RHVq40$yjyRqu?!A=Xni?m#uKjUn?RWj7L71%JMY');
define('LOGGED_IN_KEY',    '$fwpPam=ffQsuDgR7L6-Wb3QuFMQ5Sk3BPTGHRrsf4D0bygMMNF0DGYqT1Z4Bl');
define('NONCE_KEY',        'Z9Bj-9P7VQfBleEhe9TmkKuRzk:No!h5HA?VpSrH##zL0s5:ub-GV9mlCp7IA6');
define('AUTH_SALT',        'DdrWUYDZpuF8d072JhSkF8C5I*qRS7cTgYZmU%gJC$-4RYxZerox$*fwRe+%WL');
define('SECURE_AUTH_SALT', 'Rm+g-Rvj+_G:@F@yjB8!x40_ow-%a_Ht-GQZ+X#qllxYIAJuBdazZEdAfJN*v%');
define('LOGGED_IN_SALT',   'RXvFgL3PBJLsjk*_?3_FUCzZYy?XD!V6QBVaDsA?cR06E19bP%Gm0c0l=?R0lk');
define('NONCE_SALT',       'Qziv2wEaN#4dTCDa8Dsu?zGjj-I*js!A2Z27_Lc0XVFf1DDa?lM+?s:Lv=MWy4');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'kioprefixq1w2e3x_';
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');//Disable File Edits
if (!defined('DISALLOW_FILE_EDIT')) { define('DISALLOW_FILE_EDIT', true); }