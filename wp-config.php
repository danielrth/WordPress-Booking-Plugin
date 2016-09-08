<?php
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
define('DB_NAME', 'wp_practitioner_booking');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'H#M$KSB)M)<JJ]tPg1l|kH=9AtNCDfJad2*<6Mc<:LXOQep:^(WXX?uJ#Qk*!^Km');
define('SECURE_AUTH_KEY',  '/u%r l&p=adV?edvC9]h7kU#A}q*y^s9yqc8k|f;]iO{5ra<IN(`Q=f(s?x31Ln`');
define('LOGGED_IN_KEY',    'F:Ej)j4W(_~0|/Mb8)]:6y3~+<e_G=D0+RFgzOB*r~fEQ}%J/ NF}K}@#baS|~7x');
define('NONCE_KEY',        '+c|(l} lPu`K]iF64Ac|UR-dk)CP<2#x.~9>h`T_xKgy2y{r#~icnwn}@_nm.{gx');
define('AUTH_SALT',        'bcYP@{9HJDgn0[|3H*Z`XCwT5K)CG%CBY1cf@T.P#2B?8{GViNGWL]p&j=f0fNfW');
define('SECURE_AUTH_SALT', 'ou9sExmSM.)N_`O`u[ehq[T`6&e!d%W&-}5?Ed4i!4ZbF:BWb}Ot7(zcUV/}_b 1');
define('LOGGED_IN_SALT',   'R=XZuAuI:Ak4,{wZO(vRCy#1U(n%I5G3JS^ieeW{^`J/J6hfL?M_}G6#C@>~7u?:');
define('NONCE_SALT',       '%v8YaBa<vZxe|Y.Cb=^_Y??g[hVTR[BVV9#)x2HYIX_W=+d!Ic6.~saieOJyf-P3');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
