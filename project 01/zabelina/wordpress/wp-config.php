<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'zabelina' );

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
define( 'AUTH_KEY',         'J2MfU?g[`E![r6b.s$,cf_ul!O~X_|8)Vb-%l oQtg!P8KZ.DyDVqS9EbICEO/6n' );
define( 'SECURE_AUTH_KEY',  '{8q]EMeo_w%5DqJ-E3`<O6EY@4 P:$ly,2f|,{!a)klP,>+MRk~Uu=`knj}^v5UQ' );
define( 'LOGGED_IN_KEY',    't~(9h},]awF!Jb.YSVi^q{-Y4:Zy_Scul3}hKz[J0}pDXowJ1L|:=a+_jI/vkU:m' );
define( 'NONCE_KEY',        '/*_;ls%,At*E*nXiKZ_hTWI9wfvh6c #`$UippaX?.wJE+0+_mM~55w+K?m6+k&l' );
define( 'AUTH_SALT',        ':QqPad*/| OFyj6Ak:*Ev9`e>X5i*Zl.j~bh[K#?,/2[.$fT{00xDGrsaMAfu(_M' );
define( 'SECURE_AUTH_SALT', ']fhS*-99Ougl204 (TN@/(`N YHpX?AH!nk7d:{B4(U[onS/uZ.Ek`+Ix#A#i*ks' );
define( 'LOGGED_IN_SALT',   'Wf%v.rU4&y^U3qVDd=jvk~;L&w[gEj`PA0-0Fg>+xxDJd5+|^&?9TwqsMzUO345W' );
define( 'NONCE_SALT',       'sf?Os#,*9mh!|bXl[pm:aS2n[2?b2ci5!5ZI.jUE./,Sd`rVJ.E0-cFu5`pg|&cR' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_331';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
