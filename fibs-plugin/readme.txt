=== Featured Image Bulk Set Plugin ===
Contributors: kc2qcy
Tags: utilities, featured image
Requires at least: 4.7
Tested up to: 5.6
Stable tag: 1.5.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This is a plugin designed to do one simple job: programatically add a featured image in WordPress to existing posts. Either images already in the post, or a default image you can select.

== Description ==

This plugin adds a section to your Settings menu in the wp-admin section. Through that section you can perform two tasks:

* A scripted, one-time action updating all of the posts in your blog that do not have a featured image where the script will either try to find a suitable image to use or use one you have provided and configure it for you.
* An ongoing, continuous monitoring for posts that do not have a featured image, using the same logic to try and find a suitable featured image and setting that for the post.

You can find additional documentation for this plugin on the author's blog [blog.nickleghorn.com](https://blog.nickleghorn.com/2021/01/25/adding-a-featured-image-to-all-posts-in-wordpress-in-one-easy-click/ "blog.nickleghorn.com")

Special thanks to Robert Farago for giving me yet another massive site without any Featured Images to clean up for the impetus to finally make this available.

== Changelog ==

= 1.5.1 = 
* Update readme

= 1.5 =
* Tested stable release of changes in 1.4.1 and 1.4.2

= 1.4.2 =
* Fixed a bug that would cause no posts to be returned from the query of the post DB

= 1.4.1 =
* Added the ability to exclude drafts in posts that get featured images

= 1.4 =
* Moved post checking items into their own function
* Added configurable options to the script
* Added the ability to automatically have every post update with a featured image, checked every time the page is loaded

= 1.3 =
* Added unique identifier to function name

= 1.2 =
* Added input sanitization and output escaping as per the WordPress plugin security standards

= 1.1 =
* Connected the image override function
* Moved image validation to a seperate function, and validate that image is actually an image before assigning

= 1.0 =
* First version! 
