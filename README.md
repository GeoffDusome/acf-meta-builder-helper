# @geoffdusome/acf-meta-builder-helper

[![GitHub stars](https://img.shields.io/github/stars/GeoffDusome/acf-meta-builder.svg)](https://github.com/GeoffDusome/acf-meta-builder-helper/stargazers)
[![GitHub issues](https://img.shields.io/github/issues/GeoffDusome/acf-meta-builder.svg)](https://github.com/GeoffDusome/acf-meta-builder-helper/issues)
[![GitHub license](https://img.shields.io/github/license/GeoffDusome/acf-meta-builder.svg)](https://github.com/GeoffDusome/acf-meta-builder-helper/blob/master/LICENSE)

A helper WordPress plugin for the [ACF Meta Builder gulp task](https://github.com/GeoffDusome/acf-meta-builder).

## Installation

Clone or download the plugin and upload entire folder to your WordPress installation (usually located under `./wp-content/plugins/`).

## Usage

The helper plugin uses a `get_post_meta($post->ID)` call to pull **all** of the meta for the page. It will then use that meta array to pull from. This allows us to have all of our meta for the page with one call to the DB, instead of one for every meta call.

### acfmb($type, $name, $group, $options)

This function is the heart of the helper plugin, as well as the heart of the gulp task! This function not only creates the meta, but also pulls the meta value from the meta call above.

**$type** - string  
**[required]**  
The type of field you want to use (view field types [here](https://www.advancedcustomfields.com/resources/#field_types)).

**$name** - string  
**[required]**  
The name of the field.

**$group** - string  
**[required]**  
The name of the group the field belongs to.

**$options** - string (JSON encoded string)  
Unused in the helper plugin but optional for the gulp task.

### acfmb_image_url($value, $size)

Get image URL from an attachment ID

**$value** - string  
**[required]**  
The ID of the image

**$size** - string  
The defined image size of the image you want to display.

### acfmb_link($value)

Get the link object array (using `unserialize()`) for display on the front end.

**$value** - string  
**[required]**  
JSON encoded array from DB.

### acfmb_link_markup($value, $classes, $wrapper)

Get the link object and provide markup for the button

**$value** - string  
**[required]**  
JSON encoded array from DB.

**$classes** - string  
Classes for the button in string format.

**$wrapper** - bool  
Whether or not to show the wrapper for the button.

**$unserialize** - bool  
Whether or not to unserialize the data given to the function.

### acfmb_true_false($value)

Return a boolean value instead for a true/false field.

**$value** - string  
**[required]**  
Expects a '0' or '1' to return a bool.

### acfmb_repeater($name, $return = false)

Outputs markup from a part file or returns data. If `$return` is set to false, this function will look for a part file in `{theme}/parts/` with the name `repeater-{name-slug}.php`. Otherwise, the data will be returned in an array to the location the function was called.

**$name** - string  
**[required]**  
The name of your repeater in the same casing as when you defined it in your `acfmb()` function call.

**$return** - bool  
**[required]**  
Whether or not to return the data generated or show the view from a part file.

### acfmb_group($name, $has_sub_meta = false)

Returns data for use on template files for ACF group fields.

**$name** - string  
**[required]**  
The name of your group in the same casing as when you defined it.

**$has_sub_meta** - bool  
Whether or not the group has more than 1 level of meta (if the group contains a repeater or another group). If you're unsure, always set this to true to have this function work the same as the `acfmb_repeater()` function.

## Example

If you have both the [ACF Meta Builder gulp task](https://github.com/GeoffDusome/acf-meta-builder) as well as the ACF Meta Builder WordPress plugin below is an example of the typical workflow of building meta.

```
// Create a tab
$hero_tab = acfmb('tab', 'Hero', 'Page Meta');

// Create an image
$hero_background_id = acfmb('image', 'Hero Background', 'Page Meta');
$hero_background = acfmb_image_url($hero_background_id, 'large');

// Create a text field
$hero_headline = acfmb('text', 'Hero Headline', 'Page Meta');

// Create a link field
$hero_button_obj = acfmb('link', 'Hero Button', 'Page Meta');
$hero_button = acfmb_link($hero_button_obj);
```