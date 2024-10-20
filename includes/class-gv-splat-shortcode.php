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
        // Set default attributes
        $atts = shortcode_atts( array(
            'id'            => 0,          // Default ID
            'width'         => '100%',     // Default width
            'height'        => '100dvh',   // Default height
            'border'        => 'none',     // Default border
            'border_radius' => '',         // Default border-radius
            'style'         => '',         // Custom style
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
        $output = "";

        if ( ! empty( $splat_data['responseObject']['slug'] ) ) {
            // Build the style string
            $style = '';

            // If 'style' attribute is provided, use it directly
            if ( ! empty( $atts['style'] ) ) {
                $style = $atts['style'];
            } else {
                // Build style from individual attributes
                $style_attributes = array(
                    'width'  => esc_attr( $atts['width'] ),
                    'height' => esc_attr( $atts['height'] ),
                    'border' => esc_attr( $atts['border'] ),
                );
                if ( ! empty( $atts['border_radius'] ) ) {
                    $style_attributes['border-radius'] = esc_attr( $atts['border_radius'] );
                }
                // Convert style attributes to a string
                foreach ( $style_attributes as $key => $value ) {
                    $style .= $key . ':' . $value . ';';
                }
            }

            $output .= '<iframe src="' . esc_url( $base . "/" . $splat_data['responseObject']['slug'] ) . '" title="' . esc_attr( $splat_data['responseObject']['title'] ) . '" style="' . esc_attr( $style ) . '"></iframe>';
        }

        return $output;
    }
}
