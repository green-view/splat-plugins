<?php

/**
 * GV Splat HTTP Provider
 *
 * This class handles HTTP requests for the GV Splat plugin, including adding the Authorization header with the token.
 *
 * @link       https://green-view.nl
 * @since      1.0.0
 * @package    Gv_Splat
 * @subpackage Gv_Splat/includes
 */

class Gv_Splat_HTTP {
	const URL = 'https://gsplat.ciptadusa.com';

	/**
	 * Make a GET request
	 *
	 * @param string $url The URL to send the GET request to.
	 *
	 * @return array|WP_Error The response or WP_Error on failure.
	 */
	public static function get( $url ) {
		$args = array(
			'headers' => self::get_authorization_header(),
		);

		$response = wp_remote_get( $url, $args );

		return $response;
	}

	/**
	 * Get the Authorization header with the Bearer token
	 *
	 * @return array The headers including Authorization.
	 */
	private static function get_authorization_header() {
		$token = get_option( 'gv_splat_token' );

		if ( empty( $token ) ) {
			return array(); // No token, no authorization header
		}

		return array(
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
		);
	}

	/**
	 * Make a POST request
	 *
	 * @param string $url The URL to send the POST request to.
	 * @param array $body The body of the request.
	 *
	 * @return array|WP_Error The response or WP_Error on failure.
	 */
	public static function post( $url, $body = array() ) {
		$args = array(
			'headers' => self::get_authorization_header(),
			'body'    => $body,
		);

		$response = wp_remote_post( $url, $args );

		return $response;
	}

	public static function get_user_info() {
		$_uri = self::URL;
		$url  = "$_uri/splat-wp/me";

		$args = array(
			'headers' => self::get_authorization_header(),
		);

		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			return 'Error: ' . $error_message;
		} else {
			return wp_remote_retrieve_body( $response );
		}
	}

	public static function get_splats( $limit, $page ) {
		$url  = self::URL . '/splat-wp/list?limit=' . intval( $limit ) . '&page=' . intval( $page );
		$args = array(
			'headers' => self::get_authorization_header(),
		);

		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			return 'Error: ' . $error_message;
		} else {
			return wp_remote_retrieve_body( $response );
		}
	}

}
