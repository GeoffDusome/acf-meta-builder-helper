<?php
/**
 * ACF Meta Builder Helper
 *
 * Plugin Name: ACF Meta Builder Helper
 * Plugin URI:  https://github.com/GeoffDusome/acf-meta-builder-helper
 * Description: A helper WordPress plugin for the ACF Meta Builder gulp task. 
 * Version:     1.0.0
 * Author:      Geoff Dusome
 * Author URI:  https://github.com/GeoffDusome
 * License:     GPL-3.0
 * License URI: https://github.com/GeoffDusome/acf-meta-builder-helper/blob/master/LICENSE
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

// Define path and URL to the ACF plugin.
define( 'ACFMBH_ACFPRO_PATH', plugin_dir_path(__DIR__) . 'advanced-custom-fields-pro/' );
define( 'ACFMBH_ACF_PATH', plugin_dir_path(__DIR__) . 'advanced-custom-fields/' );

// Include the ACF plugin.
if ( file_exists( ACFMBH_ACFPRO_PATH . 'acf.php' ) )
{
	include_once( ACFMBH_ACFPRO_PATH . 'acf.php' );
}
else if ( file_exists( ACFMBH_ACF_PATH . 'acf.php' ) ) 
{
	include_once( ACFMBH_ACF_PATH . 'acf.php' );
}

// define global $acfmb_page_meta array
$acfmb_page_meta = array();

/**
 * acfmbh_global_meta_variable()
 * 
 * get global post variable and setup global $acfmb_page_meta variable
 */
function acfmbh_global_meta_variable()
{
	global $post;
	global $acfmb_page_meta;

	$acfmb_page_meta = get_post_meta($post->ID);
}
add_action('wp_head', 'acfmbh_global_meta_variable');

// if ACF exists, add the local field groups (we can't do this if ACF isn't added)
if ( function_exists('acf_add_local_field_group') )
{
	$current_meta = acfmbh_get_meta_structure();
	if ( $current_meta !== false )
	{
		foreach ($current_meta['data'] as $group => $meta) 
		{
			acf_add_local_field_group($meta);
		}
	}
}

/**
 * acfmbh_get_meta_structure()
 * 
 * Pull acf JSON file for adding a local field group
 */
function acfmbh_get_meta_structure()
{
	if ( file_exists( get_template_directory().'/acf-json/acf-meta.json') )
	{
		$acf_file = get_template_directory().'/acf-json/acf-meta.json';
		$current_meta = file_get_contents($acf_file);
		$current_meta = json_decode($current_meta, true);
		return array('file' => $acf_file, 'data' => $current_meta);
	}
	else 
	{
		return false;
	}
}

/**
 * acfmb($type, $name, $group, $options)
 * 
 * Front end display for meta fields! 
 *
 * @param string $type [required] the type of field you want to use (https://www.advancedcustomfields.com/resources/#field_types)
 * @param string $name [required] the name of the field
 * @param string $group [required] the name of the group the field belongs to
 * @param array $options an array of extra options for the field
 */
function acfmb($type, $name, $group, $options = false)
{
	// Get global meta array
	global $acfmb_page_meta;
	global $post;

	// Get slug from the field name
	$slug = str_replace( '-', '_', sanitize_title($name) );

	// return the value
	if ( $type !== 'tab' && $type !== 'group' && $type !== 'repeater' )
	{
		if ( $type === 'link' || $type === 'gallery' || $type === 'checkbox' || $type === 'relationship' || $type === 'taxonomy' )
		{
			return ( array_key_exists($slug, $acfmb_page_meta) ) ? unserialize($acfmb_page_meta[$slug][0]) : unserialize(get_post_meta($post->ID, $slug, true));
		}
		else if ( $type === 'post_object' || $type === 'page_link' || $type === 'user' || $type === 'select' )
		{
			if ( isset( json_decode($options)->multiple ) )
			{
				return ( array_key_exists($slug, $acfmb_page_meta) ) ? unserialize($acfmb_page_meta[$slug][0]) : unserialize(get_post_meta($post->ID, $slug, true));
			}
			else
			{
				return ( array_key_exists($slug, $acfmb_page_meta) ) ? $acfmb_page_meta[$slug][0] : get_post_meta($post->ID, $slug, true);
			}
		}
		else if ( $type === 'textarea' )
		{
			return ( array_key_exists($slug, $acfmb_page_meta) ) ? wpautop( $acfmb_page_meta[$slug][0], true ) : wpautop( get_post_meta($post->ID, $slug, true), true );
		}
		else if ( $type === 'oembed' )
		{
			return ( array_key_exists($slug, $acfmb_page_meta) ) ? wp_oembed_get($acfmb_page_meta[$slug][0]) : wp_oembed_get(get_post_meta($post->ID, $slug, true));
		}
		else
		{
			return ( array_key_exists($slug, $acfmb_page_meta) ) ? $acfmb_page_meta[$slug][0] : get_post_meta($post->ID, $slug, true);
		}
	}
}

/**
 * acfmb_image_url($value, $size)
 * 
 * Get image urls from an ID
 *
 * @param string $value [required] the id of the image
 * @param string $size the defined image size of the image you want to display
 */
function acfmb_image_url($value, $size = 'full')
{
	// get the image
	$img = wp_get_attachment_image_src($value, $size);

	// return the image url if it exists
    return ( isset($img[0]) ) ? $img[0] : '';
}

/**
 * acfmb_link_markup($value, $classes, $wrapper)
 * 
 * get the link object and provide markup for the button
 *
 * @param string $value [required] json encoded array from db
 * @param string $classes classes for the button in string format
 * @param bool $wrapper whether or not to show the wrapper for the button
 * @param bool $unserialize whether or not to unserialize the data given to the function
 */
function acfmb_link_markup($value, $classes = '', $wrapper = false, $unserialize = true)
{
	$output = '';

	$link_obj = ( $unserialize ) ? unserialize($value) : $value;

	if ( $link_obj )
	{
		if ( $wrapper )
		{
			$output .= '<div class="button-wrap">';
		}

		$output .= '<a href="' . $link_obj['url'] . '" class="' . $classes . '" target="' . $link_obj['target'] . '">' . $link_obj['title'] . '</a>';
		
		if ( $wrapper )
		{
			$output .= '</div>';
		}
	}

	return $output;
}