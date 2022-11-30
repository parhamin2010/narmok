<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'narmok' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'iSQiOjR*RE}q(.WshDRoWPpV`@NBlZ/T/aU#t4PDL#5;=oBB):#=H<BfI;%G+Dq2' );
define( 'SECURE_AUTH_KEY',  'mOH il(7*u&p]hz[~a%1bP rPi/IO9QCwQz#ohI}+SDdBiEK2.(QS?WCSft6)Q2`' );
define( 'LOGGED_IN_KEY',    '+BBIYd5!g@$N/I(|zDk,$3Bf8 x^Xh5#=bCCRV}SI2W,giI_6c/wXzz+E)T27e//' );
define( 'NONCE_KEY',        'MuiecEvS`qur!k~3zd>)@kpjT!-n.FD2ntIk($1S[C.P2YjhXSpV+>y}}>0ED.Un' );
define( 'AUTH_SALT',        'u?Wh-F8$nL+ pQ*}LWCzcTP_PLTJy7S;E*#GN,U$jq](A0|&$Qb~hKpO9E1+zo5^' );
define( 'SECURE_AUTH_SALT', 'SI)F!{6!.h&=;F,-Ds=Ga;Kn46b+!>OP^=rPUS){?@dCjs|-uOjKJ+mv4ZG%o]n[' );
define( 'LOGGED_IN_SALT',   '[ChOPlUp8M{Md|hGlC?22pOqB,I@#B-TWG{xLykA@)|BsuY<KOZqvZk7Z:a:C}q?' );
define( 'NONCE_SALT',       '5(`]D:JKx:64is<C_Fo&]},u9GPj#xBcD(W}6q1?HHR5_>eTnhmK([i%kgy^,_0.' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ubg_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

define( 'WP_DEBUG_DISPLAY', false );

define( 'WP_DEBUG_LOG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';