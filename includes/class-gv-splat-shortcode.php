<?php

class Gv_Splat_Shortcode {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'splat_shortcode', array( __CLASS__, 'render_splat_shortcode' ) );
	}

	/**
	 * Render the Splat shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string HTML content or error message.
	 */
	public static function render_splat_shortcode( $atts ) {
		// Get the Splat ID from the shortcode attributes
		$atts = shortcode_atts( array(
			'id' => 0, // Default to 0 if not provided
		), $atts, 'splat_shortcode' );

		$splat_id = intval( $atts['id'] );

		// If no ID is provided, return an error message
		if ( ! $splat_id ) {
			return '<div class="notice notice-error">No valid Splat ID provided.</div>';
		}

		// Fetch the Splat data
		$splat_data = Gv_Splat_HTTP::get_splat( $splat_id );

		// Check if data retrieval was successful
		if ( ! isset( $splat_data['success'] ) || ! $splat_data['success'] ) {
			return '<div class="notice notice-error">Error fetching Splat data.</div>';
		}

		// Generate the HTML for the Splat
		$base = "https://staging.green-view.nl/s";
		// If there's a thumbnail, display it
		$output = "";
		if ( ! empty( $splat_data['responseObject']['slug'] ) ) {
			$output .= '<iframe src="' . esc_url( $base . "/" . $splat_data['responseObject']['slug'] ) . '" title="' . esc_url( $splat_data['responseObject']['title'] ) . '" style="width:100%; height:100dvh; border:none;"></iframe>';
		}

		return $output;
	}
}
