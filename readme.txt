=== Run time Image resizing ===
Contributors: Commercepundit
Tags: bulk resize, downsize, image, imsanity, optimisation, optimise, Optimization, image optimization, plugin, resize, resizing, crop image, runtime resizing
Requires at least: 3.0.1
Tested up to: 4.6.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin resize the image which is already uploaded and it is updating image at run time

== Description ==
What if one would want to resize image at Run time? 
"Run time Image resizing" is answer of this Question. This plugin is useful to Developer as well as Non-Developer users.
Developers can use this plugin by using Shortcode. In shortcode one has to pass attachment url or attachment id with width and height for which 
he/she want to resize at run time. All parameter options in shortcode are optional except image url or image id with new height and width.
If one want image in return value then he/she must pass paramater "return='image'" in Shortcode otherwise it will return image URL.
When you use this shortcode in wordpress editor then you should use "return" attribute with "Image" value. 
Non-Dveloper user can use backend tool for resizing.

== Features ==

1. "Run time Image resizing" provide facility of resizing images at run time with user defined height and width.
2. This plugin also provide backend configuration for resizing image. One can resize the image and also get resized image at same place.
3. One can download the resized image by Right click on image


== Installation ==
1. Upload "Run time Image resizing" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Place "do_shortcode('[resize_img width="new-width" height="new-height" imgurl="Url of Attachment" attach_id="Id of Attachment" picquality="jpeg pic quality"]' );" 
in your templates. It will return you resized image url.

== Frequently Asked Questions ==
= How many parameters are supported by this plugin and what would be the value for each parameter? =

There are total 6 parameters supported by this plugin. Explanation of each parameter are as below.

* Image URL - Pass Image url which you want to resize.
* Attach id - Pass the id of the image, image which you want to resize.
* Width - Width of new resized image.
* Height - Height of new resized image.
* PictureQuality - Pass the integer value out of 100, which set the Quality of image after resize it.
* Return - Pass the value ("Image") if you want to receive resized image instead of URL at frontend. By default it will give resized image URL.

= In which format should I pass parameter with shortcode? = 

* imgurl = URL of the image (String).
* attach_id = ID of the image attachment (String).
* picquality = Qaulity value (Integer).
* width = (Integer).
* height = (Integer).
* return = (String).

= Is it always necessary to pass both parameters URL as well as ID of the image? =

No, one should pass only one parameter from both of them in shortcode.

== Screenshots ==

1. screenshot-1.png

2. screenshot-2.png

3. screenshot-3.png

== Changelog ==
= 1.0 =
* Initial release.

== Upgrade Notice ==
= 1.0 =
This version fixes a security related bug. Upgrade immediately.