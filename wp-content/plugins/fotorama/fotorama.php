<?php
/**
 * @package Fotorama
 * @version 4.6.3
 */
/*
Plugin Name: Fotorama
Plugin URI: http://fotorama.io/
Description: Transforms standard galleries into fotoramas. Fotorama is a simple, stunning, powerful jQuery gallery. Licensed under the MIT.
Author: Artem Polikarpov
Version: 4.6.3
Author URI: https://github.com/artpolikarpov
*/

define('FOTORAMA_REQUIRED_JQUERY_VERSION', '1.8.0');
define('FOTORAMA_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) . '/');

add_action('init', 'fotorama_scripts');
add_shortcode('gallery', 'fotorama_gallery_shortcode');
add_shortcode('fotorama', 'fotorama_gallery_shortcode');

function fotorama_gallery_shortcode($atts)
{
	if (!$atts) {
		$atts = array();
	}

	if (array_key_exists('fotorama', $atts) && $atts['fotorama'] == 'false') {
		return gallery_shortcode($atts);
	}

	$atts['link'] = 'file';
	$atts['itemtag'] = 'dl';
	$atts['icontag'] = 'dt';
	$atts['captiontag'] = 'dd';
	$atts['columns'] = 0;

	if (array_key_exists('orderby', $atts) && $atts['orderby'] == 'rand') {
		$atts['orderby'] = '';
		$atts['shuffle'] = 'true';
	}

	$atts['size'] = 'thumbnail';
	$gallery = gallery_shortcode($atts);

	$width = array_key_exists('width', $atts) ? $atts['width'] : '';
	$height = array_key_exists('height', $atts) ? $atts['height'] : '';

	$atts['size'] = 'large';
	preg_match_all('/(<img[^<>]*>).*\n*.*<\/dt/', gallery_shortcode($atts), $images);
	preg_match_all('/href=(\'|")([^"\']+)(\'|").*\n*.*<\/dt/', $gallery, $hrefs);

	for ($i = 0, $l = count($images[0]); $i < $l; $i++) {
		$image = $images[1][$i];
		preg_match('/src=(\'|")([^"\']+)(\'|")/', $image, $src);

		if (!$i) {
			preg_match('/width=(\'|")([^"\']+)(\'|")/', $image, $__width);
			$_width = $__width[2];

			preg_match('/height=(\'|")([^"\']+)(\'|")/', $image, $__height);
			$_height = $__height[2];

			if (!$width) {
				$atts['width'] = $_width;
			}

			if (!$height) {
				$height = $_height;
			}
		}

		$quote = $hrefs[1][$i];
		$full = $hrefs[2][$i];

		$gallery = str_replace($quote . $full . $quote,
			$quote . $src[2] . $quote . ' data-full=' . $quote . $full . $quote,
			$gallery);
	}

	$atts['auto'] = 'false';
	$atts['max-width'] = '100%';
	$atts['ratio'] = array_key_exists('ratio', $atts) ? $atts['ratio'] : ($_width && $_height ? $_width / $_height : '');

	$data = '';
	foreach ($atts as $key => $value) {
		if ($key != 'fotorama') {
			$data .= "data-$key='$value'";
		}
	}

	return "<div class='fotorama--wp' $data>$gallery</div>";
}

function fotorama_scripts()
{
	// Register scripts and styles in non-admin area only.
	if (!is_admin()) {
		// Stylesheets
		wp_register_style('fotorama.css', FOTORAMA_PLUGIN_URL . 'fotorama.css');
		wp_enqueue_style('fotorama.css');

		// Scripts
		wp_register_script('fotorama.js', FOTORAMA_PLUGIN_URL . 'fotorama.js', array('jquery'));
		wp_enqueue_script('fotorama.js');

		if (file_exists(WP_PLUGIN_DIR . '/fotoramaDefaults.js')) {
			// Override defaults
			wp_register_script('fotoramaDefaults.js', WP_PLUGIN_URL . '/fotoramaDefaults.js');
			wp_enqueue_script('fotoramaDefaults.js');
		}

		wp_register_script('fotorama-wp.js', FOTORAMA_PLUGIN_URL . 'fotorama-wp.js');
		wp_enqueue_script('fotorama-wp.js');
	}

	wp_register_style('fotorama-wp.css', FOTORAMA_PLUGIN_URL . 'fotorama-wp.css');
	wp_enqueue_style('fotorama-wp.css');
}

?>
