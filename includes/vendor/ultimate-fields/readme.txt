=== Ultimate Fields ===
Contributors: RadoGeorgiev
Donate link: https://www.ultimate-fields.com/pro/
Tags: custom fields, meta, theme options, repeater, post meta
Requires at least: 4.9
Tested up to: 4.9.5
Stable tag: 3.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.4

Easy and powerful custom fields management: Post Meta, Options Pages, Repeaters and many field types!

== Description ==

With Ultimate Fields you can easily create fields in the admin. Those fields can be displayed when you are editing a post or page (any post type actually) or in an options page (ex. Theme Options) anywhere in the admin.

Please visit [https://github.com/RadoslavGeorgiev/ultimate-fields](https://github.com/RadoslavGeorgiev/ultimate-fields) for more details.

= Features =
* Easy to use.
* Various field types (listed below)
* WYSIWYG Field Creation
* Focused on developers: A clean object-oriented API allows you to cleanly define all of your fields through code.
* Perfected tabs, alignment and styles
* Import, Export and in-theme JSON Synchronization
* JavaScript-based interface
* Unlimited field nesting with the Repeater and Complex fields
* Conditional Logic between fields in the same container (and ones on upper levels)
* REST API Support
* Admin Columns
* Integration with WordPress SEO for automatic field content ratings
* Clean styles: Ultimate Fields follows WordPress' built in styles as much as possible and provides a seamless experience. It's even fully responsive.
* Full-featured interface for data loading
* Front-end forms

= Locations =

All locations have advanced placement rules, which allow them to only appear when really necessary.

* Post Type
* Options Page
* Customizer
* Comment
* Attachment
* Menu Item
* Shortcode
* Taxonomy
* User
* Widget

= Fields =
* __Text:__ Text, Textarea, WYSIWYG, and Password
* __Choices:__ Checkbox, Select, Multiselect, and Image Select
* __Files:__ File, Image, Audio, Gallery, Video, and Embed
* __Relational:__ WP_Object, WP_Objects, and Link
* __Others:__ Number, Color, Date, Time, Datetime, Font, Icon, Map, and Sidebar
* __Advanced:__ Tab, Section, Message, Complex, __Repeater__, and Layout

= Repeaters =
Repeaters can be used to add repeatable groups of fields. You can combine a text and a file field into a Slide group and allow the user to add as many slides as he needs. The value is saved in a single field!

Additionally you can add different types of groups into a single repeater. This way the user could add both Video Slides and Image Slides through the same place.

= Embedding in themes and plugins =
Ultimate Fields has all the necessary logic built in. Just place it wherever you need and include ultimate-fields.php


== Installation ==

1. Upload `ultimate-fields` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You are ready to create your first container. To do this, choose "Add New" from the "Ultimate Fields" section in the menu.
4. Output values through get_value(), the_value() and etc.

Please make sure to check out [the documentation on our website](https://www.ultimate-fields.com/docs/).

== Screenshots ==

1. This screenshot shows the main editing screen where containers are set up.
2. An example Options page
4. A post meta box with a few simple fields and a repeater

== Upgrade Notice ==

= 3.0 =
This is a complete rewrite of the plugin with renewed interface and various new functions. After upgrade, you will be required to migrate your containers to the format of the new version. Important: There are no immediate modifications of field values and the old container format will be stored in case you need to revert.

== Changelog ==

= 3.1 Merge with Ultimate Fields Pro =

This version brings the functionality of Ultimate Fields Pro to Ultimate Fields. This includes:

__Locations:__

* The Customizer
* Attachment
* Comment
* Menu
* Shortcode
* Taxonomy
* User
* Widget

__Fields:__

* Gallery
* Audio
* Video
* Embed
* Date
* Time
* Date-Time
* Color
* Font
* Icon
* Map
* Sidebar
* Layout

Additionally, Front-End Forms are also now available.

= 3.0.2 Bugfix Release with a couple of small features =

[FIX] Limiting preview image sizes to 100% to ensure that if `thumbnail` is not available as an image size, images are not overflowing.
[FIX] Enqueueing overlays properly for files: The File fields were expecting the Overlay JS class to be available, but it was not enqueued.
[FIX] Values are now properly saved and loaded for WYSIWYG editors, including empty paragraphs and etc.
[FIX] Improved handling of repeater values in the REST API: The iterator format of the repeater field was not playing nicely with the REST API.
[UPDATE] Minor adjustments of the UI and complex fields in order to allow https://github.com/RadoslavGeorgiev/ultimate-fields-layout-control to work.
[FIX] Messages are not saving values anymore.
[FIX][W2422] Conditional logic is not used if unchecked
[FIX][W2439] Complex fields can now handle missing containers.
[FEATURE][W2440] Adding a target_control setting to the link field
[FIX][W2444] Empty dependency sets in array mode are ignored now.

= 3.0.1 Bugfix Relase =

Fixes:

* A sanitization bug, prevented the options datastore from saving non-scalar values
* Post-based rules for the `Post_Type` location, both in the UI and in core
* Even when a repeater was empty, the `while` loop was being entered, which is not the case anymore
* To improve compatability with "Front-end Editor", revision fields are only provided for existing posts


= 3.0 =
3.0 is a complete rewrite of the plugin, which brings a ton of improvements, including but not limited to:

* JavaScript-driven UI
* WYSIWYG Field Creation
* New styles and field arrangements
* Proper PHP exports, as well as JSON exports
* JSON Synchronization
* Unlimited field nesting
* Conditional logic
* REST API Support
* Admin columns
* WordPress SEO Integration
* Better relational fields
* A new field type, called "Complex"

As of this version, the website, documentation and PRO version of the plugin are online too.

On June 28, 2017 we announced that the qTranslate support is being eliminated and sadly, it is no longer available. You can read more about that in https://rageorgiev.com/blog/2017/06/28/ultimate-fields-multilingual-support-will-removed/

= 1.2 =
* Updated visual styles
* Added a warning about dropping qTranslate support in the next big version
* Fixed JavaScript errors

= 1.1 =
* The MIME type of screenshots is changed in order to allow them to be viewable in the browser without downloading.
* The Richtext field has been updated to be compatible with the latest version of WordPress, 4.4.2 . A wrong link to mac.int is also fixed.
* The "Select Term" field will not trigger a fatal error when a taxonomy is no longer existing.
* Modified the filters for the textarea and richtext filter output.
* Tabs are no longer broken.
* The repeater field is working well.

= 1.0.3 =
* Adding an HTML field that is only available through code.
* UF_Datastore_Postmeta will return NULL if a value is not set. This way checkboxes and default values will work.
* Modified UF_Field_Checkbox to check for the proper value when retrieving from a datastore.
* UF_Postmeta is not calling save when a new post is being created (the draft).
* Made a few minor changes to allow Ultimate Post Types to work properly.

= 1.0.2 =
* PHP: Fixed bugs with the import functionality, which prevent containers from being registered.
* PHP: The get_uf_repeater( $key ) function returns processed values: Repeater values, like images and etc. will be processed before the function returns them.

= 1.0.1 =
* PHP: The Richtext Field will no longer add blank lines when displaying multiple paragraphs.
* PHP: The Richtext Field's setting for rows is actually working.
* PHP: Options pages can now reside under other Ultimate Fields pages.
* PHP: uf_extend is now called on plugins_loaded. This way all that is extended will have access to more hooks, including widgets_init.
* PHP: Changed the message when a container is saved.
* PHP: UF_Field_Set will be saved even without checked values.

* JS & CSS: Validation is a bit better - it highlights the field and tab of the error.
* JS: New repeater elements can be toggled by using the arrow.

* Translations: Fixed the path in uf-bg_BG.po
* Translations: German translation added (Big Thanks to [sixtyseven](https://profiles.wordpress.org/sixtyseven/) !)
* Translations: Several string fixes
* Translations: The Heading field does no longer use automatically generated labels. This way translations can work.
* Translations: New tabs' text can be translated too now, missing textdomain added.

**WordPress 3.9**
The "Richtext" field is fixed.

= 1.0 =
Initial version.
