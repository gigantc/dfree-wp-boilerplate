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

// ** MySQL settings ** //
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
define( 'AUTH_KEY',         '$[Vt)?2W,}:#_5ad+A<0NL(/g][Hj=b[g4Y6g^q[?sZmh7=HjUICA*vCSx_na$:Q' );
define( 'SECURE_AUTH_KEY',  'l7APNoWWj/bP>:b]2!YmYC4u,$`)-iSG0U,n3dW@92Yut]H|Edi<<PU:V@6E=?EZ' );
define( 'LOGGED_IN_KEY',    'z<!ri[_k5wG*_)H^jIW^/8;s`BH~N>$+P`0$6flgL#Z ;~PB3p8@v&|zwb~`<7_P' );
define( 'NONCE_KEY',        'xZ6<E gGC37>c]#9ws<C0O~= =n9*_{yxCtc]$yS>;~?wyyxs{IY:tQkmZYgSpE5' );
define( 'AUTH_SALT',        'l|GzC*6@7~aJ;}Jt5Q3pYaQdb!8Ri33G<3yZ#qt[wUqt|uNfVtg+%cK|BH&)C?-#' );
define( 'SECURE_AUTH_SALT', 'mUUF(7pNm[Et<-Pod,INb1x61_Spj_jXsf|0>Wlus?a}*P2x.0@QsX6lm-x=kxXY' );
define( 'LOGGED_IN_SALT',   '.$82Q~Q_BwY#u~OJJ)j5.9$8K4v,@!bU4s{RUtN`EiYI&*D$Sl&W,(1>f6l3~=bB' );
define( 'NONCE_SALT',       'Sq`bDvj5@:/`IP}TUr8BViBO!,6{cMYc*rI5Pm(nuir^zQxFIgg;+{bx%uJ)c<2V' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', true );
define( 'SCRIPT_DEBUG', true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
