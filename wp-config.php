<?php


// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wpboiler');

/** MySQL database username */
define('DB_USER', 'wp');

/** MySQL database password */
define('DB_PASSWORD', 'wp');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('AUTH_KEY',         'P=Li:E(:Y2T%wet@vNPmG7Ibv2y<Uh|S~{huYqb_:QoPQRtRJ%H/y/[S5[`Kt2xl');
define('SECURE_AUTH_KEY',  '^}xa$Q[KG-KE1344.4AVS2ms-bU:Ra4&&_,-+,[:Ci<,B!?uZUnw^5vYH(9nV6YD');
define('LOGGED_IN_KEY',    'bUjd80/vq/|2oC]:V& Lp|Pg1V)@#qD&^BaIPC-]iO{hqXI_p)1~E%qC-u,7luB7');
define('NONCE_KEY',        '<ak2n|}j+|W8e7k~c4+C-5=?]+FX}30CJ?SrAI`j>d0A6aOQypH.8aQ<V+wbhChn');
define('AUTH_SALT',        'f!h9iP&5l8j)vIBQ|:O2&S-{FWVh:w[$*I={B0]._pgN-FeI^^8EUSelhTgwnWMc');
define('SECURE_AUTH_SALT', 'nlJAp]x6ewQYdOZ(#q4=v+y SOAX25y{f<Fk-ko2^,-:XioM:YAetTw0l#+YrX#+');
define('LOGGED_IN_SALT',   'mDe1X]<J:jC.+(Jlj[W|fi^Z,@rRd|6emnLLZ|<oOH({;wVXIsDD}]3!:!6D,biw');
define('NONCE_SALT',       '83Z5B.=rp2c,No;X?M~KFTvrvt`n#z**1Fh}VzhrI1KsZ:wU?z.bDY;`M}O?.W^D');


$table_prefix = 'wp_';


define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', true);
define('SCRIPT_DEBUG', true);
define('JETPACK_DEV_DEBUG', true);



/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
