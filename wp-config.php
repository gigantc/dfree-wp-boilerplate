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
<<<<<<< HEAD
define('DB_NAME', 'wp_thelawfirm');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');
=======
define('DB_NAME', 'wp_thelawfirm');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');
>>>>>>> 9293eb2da5e86d503a3970bebe9f18937d5ada4f

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
<<<<<<< HEAD
define('AUTH_KEY',         'd`$ZA3q202sY^,}F}Spl-+scp#yu=--~+~%&-umtF7:pk5ZRa;/E)!rgt8 nSRiA');
define('SECURE_AUTH_KEY',  '#]^7r|wzK?sS)iY8hAiAs8|%1QG|yf4T3|R7#XW2-w-lLZ7:&By6GvtFpr*Gvrx=');
define('LOGGED_IN_KEY',    'kn|kC6ft(_x$L*z{MkWq~}vvLG&?-)4+i4M||M;C_,K6JXBCTb:|8LxQ&[9Gs0xL');
define('NONCE_KEY',        '4o=_{;vmok9|wB+]|=[i.a$hX[VEA@Mpxe#I+zGs8.Ab]gAB2R-?}u/m2aNn[Yk.');
define('AUTH_SALT',        'UjJdaQ~o9n0m-CvGLlIWgvHEbm<Xb-IvuzNsez+MQGH0ETl~gsi*0*F 3&OApT39');
define('SECURE_AUTH_SALT', '-5V/*A8(Gw1&&d|E{`W{sa!}0-5QOATJ7f>A#myA/y^?H,$0V#YZb[C`m-d]kJG2');
define('LOGGED_IN_SALT',   'q _&vpg6Mv}q;:6P!#(drMp56Yv-Vr)@A:+md./&VJmrN|.?`shF`<`(Vymza#-M');
define('NONCE_SALT',       't{nX?Jj8=)K^NiO8=?w69#2=.6MKz2g9s@5*W|jPeZ$y|.;9q z4Ke fGhhI}/h8');
=======
define('AUTH_KEY',         'VU``m~_H;g{29yxX`L]DYL#AW.U7NhX]wD~o8_ x*jH`:V_9g.>pu.+n`7UtbFAO');
define('SECURE_AUTH_KEY',  '+Q,+>%X%;+!-QLVCm^W b[`euZQtW H+#zxuBSg^u{$g$U:<}b&#Px&X5QvTZJ+E');
define('LOGGED_IN_KEY',    ';U~ahxq 9vpe9wJ4#bQAfUQ]xdjiEFTsS+=vc5v]yAtPRP0C!/2q*)WD<||JVW6r');
define('NONCE_KEY',        'N_ew&kO}#LaOV>3X8N;vW*GaGzbo!|~|)hkXoN0d_/&/(BihmPZE)/]%-NK4 K-]');
define('AUTH_SALT',        'p9hn#%+#t:8![OszGH}Ix2n<]-}@i2q[l}OPS~uq@!R^.$`C+T dRjklxN6<PkHz');
define('SECURE_AUTH_SALT', 'h=)LELC`4=$o|dD,p$V%lB`CNmj&/oes-5Zt&l@Z8FzRuSug`oSYGFi( IoF@r;N');
define('LOGGED_IN_SALT',   '=i`G?ShG9+,c{> 791U[.CzN,&Q+o_!IpX|@t{mnj1||az0g!~*@F[k>y6@LUOA&');
define('NONCE_SALT',       'HwfeRhGoOPu-!?o,Z!XnNMQq#6]yDx@uG+IHBic?}X%FwvvfLSuP6rO#~6mHv-|)');
>>>>>>> 9293eb2da5e86d503a3970bebe9f18937d5ada4f

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
<<<<<<< HEAD
$table_prefix  = 'wp_';
=======
$table_prefix  = 'wp_';
>>>>>>> 9293eb2da5e86d503a3970bebe9f18937d5ada4f

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
