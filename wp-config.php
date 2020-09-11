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
define( 'DB_NAME', 'wp-boilerplate' );

/** MySQL database username */
define( 'DB_USER', 'wp' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wp' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'DHAb?J$[k|k;%]oy]X]%.oOAye&v9[)iK_nd?4S#t<<o$SydG#aW $qUM;0E@gU+' );
define( 'SECURE_AUTH_KEY',   '/a+23uvKX~=dOqU$<[!oKzaP-P&Po$Yx~)h4<}MmRKiKHvh<u:]:-,!M@a/_zT=o' );
define( 'LOGGED_IN_KEY',     '^0;eL{Tv).&<6I14O^tF&#>1<Bk]OBDi-9AGat8Ig+C(?U1~bl6+&K[f %zS+<jz' );
define( 'NONCE_KEY',         '>soV+-#,.BzT/!HvWJ/eg#&=fhA-${6gsn_)mI~=OZ:/KF{Y_+Kl`HV;|c_2uQVG' );
define( 'AUTH_SALT',         '=glI!F|CYFc+3Vj7!`rgwVC1l,V7nY%nxn_fG9p-y+)Z3&a7$=:([H$[X:Cr!x]e' );
define( 'SECURE_AUTH_SALT',  '=YFPpp=$Z,#wQx~&L-8NX+KpH:+h@-TGy|.l{cj,+-=Kn;[~c];V(MC+~U{G|caj' );
define( 'LOGGED_IN_SALT',    '.y#UQ41KC;mr^Yv]&S;4V)<`5@Q!*t)&!#k)NICbsiYu7}su8Au2;)o;HlBj; /=' );
define( 'NONCE_SALT',        'O>=BdUqGVa>A6:b~e%KWidE[i.V=s6y6++,/St^_XCOQ?)6U]WDKzMq8xlHu-T{=' );
define( 'WP_CACHE_KEY_SALT', '?0-EHE?2<l/DD1bx)o)J.~|T!+-z-J,=87X.x#PpuKT}6[B8uV?N:;%Z:7p+wCe#' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


define( 'WP_DEBUG', true );
define( 'SCRIPT_DEBUG', true );


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
