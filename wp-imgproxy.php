<?php

/**
 * Plugin Name: WPImgProxy
 * Version: 0.0.1
 * Description: A plugin to use ImgProxy with S3-Uploads on WordPress
 * Author: Max Kieltyka | Joe Hoyle | Human Made | Automattic Inc
 */

/**
 * Copyright: Automattic Inc
 * Copyright: Human Made Limited
 * Copyright: Max Kieltyka
 */

if ( ! defined( 'WP_IMGPROXY_URL' ) || ! WP_IMGPROXY_URL ) {
	return;
}

require_once( dirname( __FILE__ ) . '/inc/class-wpimgproxy.php' );

WPImgProxy::instance();

/**
 * Generates a WP-ImgProxy URL.
 *
 * @see http://developer.wordpress.com/docs/wpimgproxy/
 *
 * @param string $image_url URL to the publicly accessible image you want to manipulate
 * @param array|string $args An array of arguments, i.e. array( 'w' => '300', 'resize' => array( 123, 456 ) ), or in string form (w=123&h=456)
 * @return string The raw final URL. You should run this through esc_url() before displaying it.
 */
function wpimgproxy_url( $image_url, $args = array(), $scheme = null ) {

	$upload_dir = wp_upload_dir();
	$upload_baseurl = $upload_dir['baseurl'];

	if ( is_multisite() ) {
		$upload_baseurl = preg_replace( '#/sites/[\d]+#', '', $upload_baseurl );
	}

	$image_url = trim( $image_url );

	$image_file = basename( parse_url( $image_url, PHP_URL_PATH ) );
	$image_url  = str_replace( $image_file, urlencode( $image_file ), $image_url );

	if ( strpos( $image_url, $upload_baseurl ) !== 0 ) {
		return $image_url;
	}

	if ( false !== apply_filters( 'wpimgproxy_skip_for_url', false, $image_url, $args, $scheme ) ) {
		return $image_url;
	}

	$image_url = apply_filters( 'wpimgproxy_pre_image_url', $image_url, $args,      $scheme );
	$args      = apply_filters( 'wpimgproxy_pre_args',      $args,      $image_url, $scheme );

	$wpimgproxy_url = str_replace( $upload_baseurl, WP_IMGPROXY_URL, $image_url );

	if ( $args ) {
		if ( is_array( $args ) ) {
			$wpimgproxy_url = add_query_arg( $args, $wpimgproxy_url );
		} else {
			// You can pass a query string for complicated requests but where you still want CDN subdomain help, etc.
			$wpimgproxy_url .= '?' . $args;
		}
	}

	/**
	 * Allows a final modification of the generated wpimgproxy URL.
	 *
	 * @param string $wpimgproxy_url The final wpimgproxy image URL including query args.
	 * @param string $image_url   The image URL without query args.
	 * @param array  $args        A key value array of the query args appended to $image_url.
	 */
	return apply_filters( 'wpimgproxy_url', $wpimgproxy_url, $image_url, $args );
}
