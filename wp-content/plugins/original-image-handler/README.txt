=== Original Image Handler ===
Contributors: haboes, timbeks, michielhabraken, thomasvanderbeek
Tags: Images, Upload, Resize
Requires at least: 3.6
Tested up to: 3.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will resize big images to a smaller size and remove the original image after resizing it to save diskspace.

== Description ==

This plugin let's you upload images to the gallery. It will resize to big images and remove the original by default.
It will also convert BMP files to JPEG file formats to save disk space.

You can configure the settings under "Media -> Image handler". Here you can set the default values for the resizing and converting to BMP.
On the media upload page you can select if the original needs to be resized or not, this will temporarly override the default setting.
This is the long description.  No limit, and you can use Markdown (as well as in the following sections).

== Installation ==

1. Extract the original_image_handler.zip to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Adjust the settings for your needs under "Media -> Image handler"

== Frequently Asked Questions ==

= Can I bulk resize images? =

Yes, in the Library there is an bulk action to resize multiple files at once. Keep in mind that this is an pretty heavy opperation for the server.

= What is the minimum resize size? =

The minimum resize size is 16 pixels (width and height)

= Can I upload an image and keep the original size? =

Yes, when you upload an image via the "Library -> Add New" you have an option that disables the resize for the time you are on the page.

== Screenshots ==

1. Here you can change the settings of the Original Image Handler
2. The information tab where you can see how much diskspace you saved after activating the plugin.
3. There is also an extra bulk option on the Media > Library page.

== Changelog ==

= Version 1.0.0 =

* Official Release