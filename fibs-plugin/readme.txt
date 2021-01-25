=== Featured Image Bulk Set Plugin ===
Contributors: kc2qcy
Tags: utilities, featured image
Requires at least: 4.7
Tested up to: 5.6
Stable tag: 1.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This is a plugin designed to do one simple job: programatically add a featured image in WordPress to existing posts. Either images already in the post, or a default image you can select.

== Description ==

This plugin adds a section to your Settings menu in the wp-admin section. Through that section you can perform two tasks:

* Update all posts that do not have a current Featured Image set such that the FIRST image used in the post is the new Featured Image
* Update all posts that do not have a current Featured Image set such that the LAST image used in the post is the new Featured Image
* Update all posts that do not have a current Featured Image set with a selected image as the new Featured Image

There is also the option, for the first two functions, to have a specific image set as a fallback option in the case that there are no images in the post.

== Changelog ==

= 1.1 =
* Connected the image override function
* Moved image validation to a seperate function, and validate that image is actually an image before assigning

= 1.0 =
* First version! 
