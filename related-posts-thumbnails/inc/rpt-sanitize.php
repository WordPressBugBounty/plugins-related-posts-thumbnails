<?php
/**
 * Sanitizers for CSS-related Related Posts Thumbnails settings.
 *
 * @package RelatedPostsThumbnails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a hex color for CSS output.
 *
 * @param mixed  $color   Raw color value.
 * @param string $default Fallback color.
 * @return string
 */
function rpt_sanitize_hex_color( $color, $default = '#000000' ) {
	$sanitized = sanitize_hex_color( (string) $color );

	return $sanitized ? $sanitized : $default;
}

/**
 * Sanitize a CSS length value.
 *
 * @param mixed  $length  Raw length value.
 * @param string $default Fallback length.
 * @return string
 */
function rpt_sanitize_css_length( $length, $default = '0px' ) {
	$length = trim( (string) $length );

	if ( preg_match( '/^\d+(\.\d+)?(px|em|rem|%|vh|vw|pt)$/i', $length ) ) {
		return strtolower( $length );
	}

	return $default;
}

/**
 * Sanitize a font size stored without units (rendered as px).
 *
 * @param mixed  $size    Raw font size.
 * @param string $default Fallback size.
 * @return string
 */
function rpt_sanitize_css_font_size( $size, $default = '12' ) {
	if ( '' === $size || null === $size || ! is_numeric( $size ) ) {
		return (string) absint( $default );
	}

	$size = (int) $size;

	if ( $size < 1 || $size > 200 ) {
		return (string) absint( $default );
	}

	return (string) $size;
}

/**
 * Sanitize a positive integer stored as a string option.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Fallback value.
 * @param int    $min     Minimum allowed value.
 * @param int    $max     Maximum allowed value.
 * @return string
 */
function rpt_sanitize_positive_int_string( $value, $default, $min = 0, $max = 9999 ) {
	if ( '' === $value || null === $value ) {
		return (string) absint( $default );
	}

	if ( ! is_numeric( $value ) ) {
		return (string) absint( $default );
	}

	$value = (int) $value;

	if ( $value < $min || $value > $max ) {
		return (string) absint( $default );
	}

	return (string) $value;
}

/**
 * Sanitize a CSS font-family stack.
 *
 * @param mixed  $family  Raw font family value.
 * @param string $default Fallback font family.
 * @return string
 */
function rpt_sanitize_css_font_family( $family, $default = 'Arial' ) {
	$family = sanitize_text_field( (string) $family );

	if ( '' === $family || ! preg_match( "/^[a-zA-Z0-9\\s,'\"\\-]+$/", $family ) ) {
		return $default;
	}

	return $family;
}

/**
 * Sanitize an aspect ratio value used in inline CSS.
 *
 * @param mixed  $value   Raw aspect ratio value.
 * @param string $default Fallback aspect ratio.
 * @return string
 */
function rpt_sanitize_aspect_ratio( $value, $default = '1/1' ) {
	$value = sanitize_text_field( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	if ( preg_match( '/^\d+\/\d+$/', $value ) ) {
		return $value;
	}

	return $default;
}

/**
 * Sanitize a column count option.
 *
 * @param mixed  $value   Raw column value.
 * @param string $default Fallback column value.
 * @return string
 */
function rpt_sanitize_column_count( $value, $default ) {
	$value = sanitize_text_field( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	$value = absint( $value );

	if ( $value < 1 || $value > 6 ) {
		return (string) $default;
	}

	return (string) $value;
}
