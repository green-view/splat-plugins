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
	const URL = 'https://api-stg.green-view.nl';

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
	private static function get_authorization_header( $isCurl = false ) {
		$token = get_option( 'gv_splat_token' );

		if ( empty( $token ) ) {
			return array(); // No token, no authorization header
		}
		if ( $isCurl ) {
			return array(
				'Authorization: Bearer ' . $token,  // Correct string format for cURL
			);
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

// File: admin/class-gv-splat-manager.php

	public static function create_splat( $payload ) {
		// API URL for creating the Splat
		$url = self::URL . '/splat-wp/create';

		// Prepare headers (you can customize this based on your authorization method)
		$headers                 = self::get_authorization_header();
		$headers['Content-Type'] = 'application/json'; // Ensure the request is sent as JSON

		// Send the POST request with the payload
		$response = wp_remote_post( $url, array(
			'body'    => json_encode( $payload ),
			'headers' => $headers,
		) );

		// Check if the response is valid
		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Retrieve the response body
		$body = wp_remote_retrieve_body( $response );

		// Return the response data
		return json_decode( $body, true );
	}

	public static function upload_file( $file ) {
		// Check if the file was uploaded correctly
		if ( ! isset( $file ) || $file['error'] != 0 ) {
			return false;
		}

		// Prepare the file for cURL upload
		$file_data = curl_file_create( $file['tmp_name'], $file['type'], $file['name'] );

		// API URL for uploading files
		$url = self::URL . '/splat-wp/upload';

		// Initialize cURL session
		$ch = curl_init();

		// Prepare headers (Authorization headers or others if required)
		$headers   = self::get_authorization_header( true ); // Assuming this returns an array of headers
		$headers[] = 'Content-Type: multipart/form-data'; // For file upload

		// Prepare cURL options
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, array( 'file' => $file_data ) );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // Return response as string instead of outputting it

		// Execute cURL request
		$response = curl_exec( $ch );

		// Check for cURL errors
		if ( curl_errno( $ch ) ) {
			$error_msg = curl_error( $ch );
			curl_close( $ch );

			return 'Error: ' . $error_msg;
		}

		// Close cURL session
		curl_close( $ch );

		// Decode the response
		$response_data = json_decode( $response, true );

		// Check if the response indicates success
		if ( isset( $response_data['success'] ) && $response_data['success'] ) {
			return $response_data['responseObject']['id']; // Return the file ID
		} else {
			return false; // Return false if upload failed
		}
	}

	public static function update_splat( $id, $payload ) {
		// API URL for updating the Splat
		$url = self::URL . '/splat-wp/update/' . intval( $id );

		// Prepare headers (Authorization headers or others if required)
		$headers                 = self::get_authorization_header();
		$headers['Content-Type'] = 'application/json'; // Ensure the request is sent as JSON

		// Send the PUT request with the payload
		$response = wp_remote_request( $url, array(
			'method'  => 'PUT',
			'body'    => json_encode( $payload ),
			'headers' => $headers,
		) );

		// Check if the response is valid
		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Retrieve the response body
		$body = wp_remote_retrieve_body( $response );

		// Return the response data
		return json_decode( $body, true );
	}

    public static function get_splat($id)
    {
        // API URL for retrieving the Splat
        $url = self::URL . '/splat-wp/get/' . intval($id);

        // Prepare headers
        $args = array(
            'headers' => self::get_authorization_header(),
            'timeout' => 30, // Increase timeout
        );

        // Send the GET request
        $response = wp_remote_get($url, $args);

        // Debug logging
        error_log('Splat API Response for ID ' . $id . ': ' . print_r($response, true));

        // Check if response is WP_Error
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'WordPress Error: ' . $response->get_error_message()
            );
        }

        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return array(
                'success' => false,
                'message' => 'HTTP Error: ' . $response_code
            );
        }

        // Get response body
        $body = wp_remote_retrieve_body($response);

        // Debug logging
        error_log('Splat API Response Body: ' . $body);

        // Decode JSON response
        $data = json_decode($body, true);

        // Check for JSON decode errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => 'JSON Error: ' . json_last_error_msg()
            );
        }

        // If response doesn't contain required data
        if (!isset($data['responseObject']) || empty($data['responseObject'])) {
            return array(
                'success' => false,
                'message' => 'Invalid response format or empty data'
            );
        }

        // Return successful response
        return array(
            'success' => true,
            'responseObject' => $data['responseObject']
        );
    }

	public static function delete_splat( $id ) {
		// API URL for deleting the Splat
		$url = self::URL . '/splat-wp/delete/' . intval( $id );

		// Prepare headers (Authorization headers or others if required)
		$args = array(
			'method'  => 'DELETE',
			'headers' => self::get_authorization_header(),
		);

		// Send the DELETE request
		$response = wp_remote_request( $url, $args );

		// Check if the response is valid
		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Retrieve the response body
		$body = wp_remote_retrieve_body( $response );

		// Return the response data
		return json_decode( $body, true );
	}

}
