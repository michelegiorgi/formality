=== Formality ===
Contributors: michelegiorgi
Donate link: https://www.paypal.me/michelegiorgi/
Tags: form, conversational, multistep, design form, gutenberg, block editor
Requires at least: 5.8
Tested up to: 6.0
Stable tag: 1.5.5
Requires PHP: 7.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Forms made simple (and cute). Designless, multistep, conversational, secure, all-in-one WordPress forms plugin.

== Description ==

Formality is an all-in-one WordPress form plugin that puts design and user experience first. Each form is characterized by an **essential interface** and a **modern layout** that follows the most recent UX patterns. No frills, no superfluous or overly characterized elements. No matter what theme you are using... with a few simple options you can quickly customize your forms and align them to your website design.

= Why choose Formality =

Formality is not the best nor the most complete form plugin. Formality does few things but it does them well and it continues to improve every day.

* **Designless** - Smart layout with simple UI and common UX patterns
* **Conversational** - Distraction-free form experience
* **Multistep** - Group your questions in multistep form
* **Simple editor** - Gutenberg-based form builder
* **Smooth** - Async data submit with WP REST API
* **Security** - Prevent spam with built-in token authentication
* **Logic condition** - Show/hide fields based on user answers
* **Collect data** - Simple interface to manage all your form results

Explore our [brand new website](https://formality.dev) for some awesome form examples that you can create with Formality.

= Documentation/support =

We are working on the full documentation which will be available soon...
In the meantime, you can request support or report a bug on the [Support page](https://wordpress.org/support/plugin/formality), or write us an email for any information.

= Dev Hooks =

You can extend Formality plugin functionality with its custom hooks. Formality has Filters, Actions and DOM Events for developers. You can find a wip reference page to start with, on this [Gist](https://gist.github.com/michelegiorgi/56fe4489b922cf2af4704b79d4f56bb6).

= Translations =

Formality is now available in 5 languages. You can now choose between English, Italian, Indonesian, Czech and Swedish (Thanks to WordPress Community). You can help translating Formality to your language on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/formality)

== Installation ==

1. Upload the entire `formality` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Formality** menu in your WordPress admin screen.

== Screenshots ==

1. Editor - General options
2. Editor - Single field options
3. Editor - Templates
4. Editor - Customization
5. Admin - Forms archive
6. Admin - Results archive

== Changelog ==

= 1.5.5 =
Release Date: May 9th, 2022

* Fix navbar order on Safari 15
* Fix input focus bug on long clicks
* WordPress 6.0 compatibility
* Gutenberg 13.1.0 compatibility

= 1.5.4 =
Release Date: Mar 27th, 2022

* Fix select keyboard navigation by letter/number
* Fix javascript errors on embedded forms
* Gutenberg 12.8.1 compatibility

= 1.5.3 =
Release Date: Mar 13th, 2022

* Add select keyboard navigation by letter/number
* Fix textarea characters counter on Safari browser
* Gutenberg 12.7.2 compatibility

= 1.5.2 =
Release Date: Jan 30th, 2022

* Fix conditional fields init ¹
* Remove characters counter from textarea field when max length is not defined
* Fix keyboard navigation with hidden fields

<small>¹ Thanks to @lukaskopenec for bug report</small>

= 1.5.1 =
Release Date: Jan 16th, 2022

* Update HK Grotesk font by Alfredo Marco Pradil (2021 version)
* Fix next/prev buttons visibility on 3+ steps forms ¹
* Fix submit errors with not mandatory upload fields
* Fix step navigation with keyboard

<small>¹ Thanks to @recveri for bug report</small>

= 1.5 =
Release Date: Jan 8th, 2022

* Completely rewritten frontend scripts with modern syntax and no 3rd party dependencies
* Frontend assets cleanup/optimization with 56% files size reduction
* Add border radius support
* Add filled input style
* Add 5 new templates ¹
* Various fixes and refinements
* WordPress 5.9 compatibility

<small>¹ New installations will be automatically download updated templates selection. Old installations (v1.4.2 or previous) can update their templates selection, by clicking on the "Reset/update templates" link (at the end of the template list).</small>

= 1.4.2 =
Release Date: Nov 27th, 2021

* Add new custom form validation
* Remove parsley.js dependency from frontend script
* Gutenberg 12 compatibility

= 1.4.1 =
Release Date: Sep 27th, 2021

* Various fixes and refinements on mobile editor
* Gutenberg 11.5 compatibility
* Remove emergence.js dependency from frontend script

= 1.4 =
Release Date: Sep 6th, 2021

* New feature: Export tool
* Various fixes and improvements on results admin pages
* Remove 3rd party styles from Formality editor page
* Change single form and single notification template name ¹

<small>¹ You can override default form and notification templates (source code on public/templates/ directory), by putting formality-form.php and formality-notification.php files inside your active theme's directory.</small>

= 1.3.6 =
Release Date: Aug 8th, 2021

* Formality block is now available on Widget block editor
* Bump minimum WordPress required version to v5.7
* Minor UI fixes

= 1.3.5 =
Release Date: Jul 21th, 2021

* WordPress 5.8 compatibility
* Gutenberg 11.1 compatibility

= 1.3.4 =
Release Date: Jul 2nd, 2021

* Add email notification template
* Gutenberg 10.9+ compatibility
* WordPress 5.8 FSE compatibility
* Minor file upload UI fixes

= 1.3.3 =
Release Date: Jun 13th, 2021

* Minor UI changes
* Formality brand refresh
* New website online

= 1.3.2 =
Release Date: May 25th, 2021

* Gutenberg 10.5+ compatibility
* WordPress 5.8 FSE compatibility
* Fix multiple rating inputs bug
* Minor UI fixes

= 1.3.1 =
Release Date: April 10th, 2021

* Fix mobile select UX

= 1.3 =
Release Date: April 5th, 2021

* New upload field
* Minor UI changes
* Various fixes

= 1.2.3 =
Release Date: February 21th, 2021

* Gutenberg 10+ compatibility
* Fix typo

= 1.2.2 =
Release Date: January 23th, 2021

* Gutenberg 9.8+ compatibility
* WordPress 5.7 Alpha compatibility

= 1.2.1 =
Release Date: January 10th, 2021

* Minor UI changes
* Update language files
* Dynamic background fixes

= 1.2 =
Release Date: January 10th, 2021

* Dynamic background
* Conditional assets loading
* PHP 8.0 compatibility

= 1.1.1 =
Release Date: December 29th, 2020

* Fix filled input state bug
* Minor UI changes

= 1.1 =
Release Date: December 28th, 2020

* Add Dev Hooks (Actions/Filters)
* Add JS DOM events
* Minor fixes

= 1.0.7 =
Release Date: December 12th, 2020

* New website online
* Minor UI fixes
* Add GitHub public repository
* Remove non-minified source assets

= 1.0.6 =
Release Date: November 29th, 2020

* Minor UI fixes

= 1.0.5 =
Release Date: November 28th, 2020

* WordPress 5.6 compatibility
* Minor UI fixes

= 1.0.4 =
Release Date: October 1st, 2020

* Minor UI fixes
* Prevent notices/warnings on generate templates action

= 1.0.3 =
Release Date: September 30th, 2020

* Embed rating icons

= 1.0.2 =
Release Date: September 12th, 2020

* Hide mobile nav scrollbar
* Various minor fixes

= 1.0.1 =
Release Date: August 29th, 2020

* Prevent 3rd party style override
* Fix it_IT language
* Fix sidebar embed width

= 1.0.0 =
Release Date: August 28th, 2020

* First version
