<?php
/**
 * CMB2 Plus
 *
 * Common utility classes for the CMB2 plugin on WordPress.
 *
 * @link              http://log.pt/
 * @since             1.0.0
 * @package           CMB2
 *
 * @wordpress-plugin
 * Plugin Name:       CMB2 Plus
 * Plugin URI:        https://github.com/log-oscon/cmb2-plus/
 * Description:       Common utility classes for the CMB2 plugin on WordPress.
 * Version:           1.0.0
 * Author:            log.OSCON, Lda.
 * Author URI:        http://log.pt/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cmb2-plus
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/log-oscon/cmb2-plus
 * GitHub Branch:     master
 */

namespace logoscon\WP\Plugin\CMB2;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Gets a number of terms and displays them as options.
 *
 * Usage:
 * $cmb->add_field( array(
 *     'name'           => 'Featured Category',
 *     'desc'           => 'Set a featured category for this post.',
 *     'id'             => '_cmb2_featured_category',
 *     'type'           => 'select',
 *     'options_cb'     => 'cmb2_get_term_options',
 *     'get_terms_args' => array(
 *         'taxonomy'   => 'category',
 *         'hide_empty' => false,
 *     ),
 * ) );
 *
 * @link https://github.com/WebDevStudios/CMB2/wiki/Tips-&-Tricks
 *
 * @since  1.0.0
 * @param  \CMB2_Field $field The CMB2 field.
 * @return array              An array of options that matches the CMB2 options array.
 */
function get_term_options( $field ) {

	$args = $field->args( 'get_terms_args' );
	$args = \is_array( $args ) ? $args : array();
	$args = \wp_parse_args( $args, array( 'taxonomy' => 'category' ) );

	$terms = (array) \get_terms( $args );

	$term_options = array();
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$term_options[ $term->term_id ] = $term->name;
		}
	}

	return $term_options;
}

/**
 * Determine if metabox should show on a front page.
 *
 * Usage:
 *   Front page: `'show_on' => array( 'key' => 'front-page', 'value' => 'page_on_front' )`
 *   Posts page: `'show_on' => array( 'key' => 'front-page', 'value' => 'page_for_posts' )`
 *
 * @link https://github.com/WebDevStudios/CMB2/wiki/Adding-your-own-show_on-filters
 *
 * @since  1.0.0
 * @param  bool  $show          Default is true, show the metabox.
 * @param  mixed $meta_box_args Array of the metabox arguments.
 * @param  mixed $cmb           The CMB2 instance.
 * @return bool
 */
function show_on_front_page( $show, $meta_box_args ) {

	if ( empty( $meta_box_args['show_on']['key'] ) ) {
		return $show;
	}

	$key = \sanitize_key( $meta_box_args['show_on']['key'] );
	if ( 'front-page' !== $key ) {
		return $show;
	}

	if ( empty( $meta_box_args['show_on']['value'] ) ) {
		return $show;
	}

	$post_id = 0;

	// If we're showing it based on ID, get the current ID
	if ( isset( $_GET['post'] ) ) {
		$post_id = intval( $_GET['post'] );
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = intval( $_POST['post_ID'] );
	}

	if ( ! $post_id ) {
		return false;
	}

	$value = \sanitize_key( $meta_box_args['show_on']['value'] );
	if ( empty( $value ) ) { // Backward compatibility
		$value = 'page_on_front';
	}
	
	if ( ! in_array( $value,  array( 'page_on_front', 'page_for_posts' ) ) ) {
		return false;
	}

	return $post_id === intval( \get_option( $value ) );
}
\add_filter( 'cmb2_show_on', '\logoscon\WP\Plugin\CMB2\show_on_front_page', 10, 2 );
