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

if ( class_exists( 'ACF_Meta_Builder_Helper' ) )
{

	class ACF_Meta_Builder_Helper
	{
		const plugin_version = 1.0;
		private static $page_meta;

		private function __construct() {}

		public static function init()
		{
			add_action('wp_head', 'global_meta_variable');
		}

		private function global_meta_variable()
		{
			global $post;
			$page_meta = get_post_meta($post->ID);

			if ( function_exists('acf_add_local_field_group') )
			{
				$current_meta = get_meta_structure();
				if ( $current_meta !== false )
				{
					foreach ($tbx_current_meta['data'] as $group => $meta) 
					{
						acf_add_local_field_group($meta);
					}
				}
			}
		}

		private function get_meta_structure()
		{
			if ( file_exists(get_template_directory().'/acf-json/acf-meta.json') )
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

		public function acfmb($type, $name, $group, $options = false)
		{
			// Get global meta array
			global $page_meta;

			// Get slug from the field name
			$slug = sanitize_title($name);

			// return the value
			if ( $type !== 'tab' && $type !== 'group' )
			{
				// if we don't get the data from $page_meta, get it manually
				if ( ! array_key_exists($slug, $page_meta) )
				{
					global $post;
					return get_post_meta($post->ID, $slug, true);
				} 
				else
				{
					return $page_meta[$slug][0];
				}
			}
		}
	}

	add_action( 'plugins_loaded', array( 'ACF_Meta_Builder_Helper', 'init' ) );

}