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

	// Get slug from the field name
	$slug = sanitize_title($name);

	// return the value
	if ( $type !== 'tab' && $type !== 'group' )
	{
		// if we don't get the data from $acfmb_page_meta, get it manually
		if ( ! array_key_exists($slug, $acfmb_page_meta) )
		{
			global $post;
			return get_post_meta($post->ID, $slug, true);
		} 
		else
		{
			return $acfmb_page_meta[$slug][0];
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
 * acfmb_link($value)
 * 
 * get the link object array for display on the front end
 *
 * @param string $value [required] json encoded array from db
 */
function acfmb_link($value)
{
	// get link object
	$link_obj = unserialize($value);

	// return link object
	return $link_obj;
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

/**
 * acfmb_true_false($value)
 * 
 * return a boolean value instead for a true/false field
 *
 * @param string $value [required] expects a '0' or '1' to return a bool
 */
function acfmb_true_false($value)
{
	// return bool value instead of 0 or 1
	if ( $value === '1' )
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * acfmb_get_multi_level_meta($meta)
 * 
 * creates a multi-level array containing the meta of repeaters that have sub repeaters or groups
 *
 * @param array $meta [required] expects an array of meta key => value pairs
 * @return array $return_array returns a multi-level array containing the meta split into sub arrays
 */
function acfmb_get_multi_level_meta($meta)
{
	$return_array = array();

	$return_array =& $target_ref;

	foreach ( $meta as $key => $value )
  	{
  		$key_parts = explode('_', $key);

  		$target_ref =& $return_array;

		for ( $i = 0; $i < count( $key_parts ); $i++ )
		{
			$key_part = ( ! is_numeric( $key_parts[$i] ) ) ? $key_parts[$i] : intval( $key_parts[$i] );

			if ( ( $i == ( count( $key_parts ) - 1 ) ) && ( ! isset( $target_ref[$key_part] ) ) )
			{
				$target_ref[$key_part] = $value;
			}
			else if ( ! isset( $target_ref[$key_part] ) )
			{
				$target_ref[$key_part] = array();
			}

			$target_ref =& $target_ref[$key_part];
		}
  	}

  	return $return_array;
}

/**
 * acfmb_repeater($name, $return = false)
 * 
 * outputs markup from a part file or returns data
 *
 * @param string $name [required] the name of your repeater in the same casing as when you defined it
 * @param string $return [required] whether or not to return the data generated or show the view from a part file
 */
function acfmb_repeater($name, $return = false)
{
	global $acfmb_page_meta;

	$repeater_data = array();

	$name = sanitize_title($name);

	if ( ! array_key_exists($name, $acfmb_page_meta) )
	{
		global $post;
		$post_meta = get_post_meta($post->ID);

		$data = array();
		$keys = preg_grep('/^'.$name.'_/', array_keys($post_meta));
		foreach ( $keys as $key )
		{
			$data[$key] = $post_meta[$key][0];
		}
		$repeater_data = acfmb_get_multi_level_meta($data);
	}
	else
	{
		$data = array();
		$keys = preg_grep('/^'.$name.'_/', array_keys($acfmb_page_meta));
		foreach ( $keys as $key )
		{
			$data[$key] = $acfmb_page_meta[$key][0];
		}
		$repeater_data = acfmb_get_multi_level_meta($data);
	}

	if ( $return )
	{
		return $repeater_data;
	}
	else
	{
		// set a query var to send to our template part
		set_query_var( 'repeater_data', $repeater_data );
		get_template_part( 'parts/repeater', $name );
	}
}

/**
 * acfmb_group($name)
 * 
 * returns data for use on template files
 *
 * @param string $name [required] the name of your group in the same casing as when you defined it
 * @param bool $has_sub_meta whether or not the group has more than 1 level of meta
 */
function acfmb_group($name, $has_sub_meta = false)
{
	global $acfmb_page_meta;

	$group_data = array();

	$name = sanitize_title($name);

	if ( ! array_key_exists($name, $acfmb_page_meta) )
	{
		global $post;

		$post_meta = get_post_meta($post->ID);

		if ( $has_sub_meta )
		{
			$data = array();
			$keys = preg_grep('/^'.$name.'_/', array_keys($post_meta));
			foreach ( $keys as $key_value => $key )
			{
				$data[$key] = $post_meta[$key][0];
			}

			$group_data = acfmb_get_multi_level_meta($data);
		}
		else
		{
			$keys = preg_grep('/^'.$name.'_/', array_keys($post_meta));
			foreach ( $keys as $key_value => $key )
			{
				$group_data[str_replace($name.'_', '', $key)] = $post_meta[$key][0];
			}
		}
	}
	else
	{
		if ( $has_sub_meta )
		{
			$data = array();
			$keys = preg_grep('/^'.$name.'_/', array_keys($acfmb_page_meta));
			foreach ( $keys as $key_value => $key )
			{
				$data[$key] = $acfmb_page_meta[$key][0];
			}

			$group_data = acfmb_get_multi_level_meta($data);
		}
		else 
		{
			$keys = preg_grep('/^'.$name.'_/', array_keys($acfmb_page_meta));
			foreach ( $keys as $key_value => $key )
			{
				$group_data[str_replace($name.'_', '', $key)] = $acfmb_page_meta[$key][0];
			}
		}
	}

	return $group_data;
}