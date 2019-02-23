<div class="wrap uf-welcome">
	<div class="uf-welcome-section uf-welcome-section-first uf-welcome-top">
		<div class="uf-welcome-intro">
			<h1><?php _e( 'Hello!', 'ultimate-fields' ) ?></h1>
			<div class="about-text">
				<p><?php _e( 'Thank you for installing or updating to Ultimate Fields 3!', 'ultimate-fields' ) ?></p>
				<p><?php _e( 'This is a complete rewrite of the plugin, which brings a ton of improvements and new features. To learn more about them, take a look below or visit <a href="https://www.ultimate-fields.com/" target="_blank">our new website</a>.', 'ultimate-fields' ) ?></p>
			</div>
		</div>

		<div class="wp-ui-highlight uf-welcome-version">
			<span class="ultimate-fields-icon">
				<?php echo file_get_contents( ULTIMATE_FIELDS_UI_DIR . 'assets/icon.svg' ) ?>
			</span>
			<strong><?php printf( __( 'Version %s', 'ultimate-fields' ), ULTIMATE_FIELDS_VERSION ) ?></strong>
		</div>
	</div>

	<hr />

	<div class="uf-welcome-section uf-welcome-changelog">
		<h2><?php _e( 'Introducing a cool new plugin', 'ultimate-fields' ) ?><sup>2</sup></h2>
		<p><?php _e( 'Ultimate Fields 3 is a completely new plugin, following the same philosophy as the first version.', 'ultimate-fields' ) ?></p>
		<p><?php _e( 'Following the trends on the World Wide Web and the ones for WordPress, this version introduces a completely JavaScript driven interface. This allows for improved user experience and various functions, which were nearly impossible to exist in its PHP based equivalent.', 'ultimate-fields' ) ?></p>
	</div>

	<hr />

	<div class="uf-welcome-section uf-welcome-section-centered">
		<h2><?php _e( 'WYSIWYG fields creation', 'ultimate-fields' ) ?></h2>

		<img src="<?php echo ULTIMATE_FIELDS_UI_URL ?>assets/welcome/wysiwyg-fields.png" alt="WYSIWYG Editor" class="uf-welcome-image" />

		<p><?php _e( 'Starting from the first screen, Ultimate Fields already looks awesome.', 'ultimate-fields' ) ?></p>
		<p><?php _e( 'One of the key visual additions in Ultimate Fields 3 is the new container edit screen. In addition to the improved location settings, the section with the fields provides a real preview of the field, allowing you to see how the fields will look, while making the navigation between fields easier and visually intuitive.', 'ultimate-fields' ) ?></p>

		<div class="clearfix"></div>
	</div>

	<hr />

	<div class="uf-welcome-section uf-welcome-section-centered">
		<h2><?php _e( 'Overhauled UI', 'ultimate-fields' ) ?></h2>
		<p><?php _e( 'Although in its core, Ultimate Fields is trying to get out of the way from your users by utilizing the WordPress look and feel, it is all new now. Starting with simple fields and extending to the administration interface, version three is completely rewritten and heavily JavaScript-based.', 'ultimate-fields' ) ?></p>
	</div>

	<hr />

	<div class="uf-welcome-section uf-welcome-section-centered">
		<h2>
			<span class="dashicons dashicons-chart-line"></span>
			<?php _e( 'Improved performance', 'ultimate-fields' ) ?>
		</h2>

		<div class="uf-welcome-columns">
			<div class="uf-welcome-column">
				<h4><?php _e( 'Back-end performance', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'With smarter location rules, fields will not be initialized until they need to be displayed.', 'ultimate-fields' ) ?></p>
				<p><?php _e( 'This minifies the impact of Ultimate Fields in the back-end, preventing fields, which are not needed from slowing down the whole administration area.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column">
				<h4><?php _e( 'Front-end performance', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Version three uses a new approach for loading fields and values in the front end.', 'ultimate-fields' ) ?></p>
				<p><?php _e( 'The appropriate details are cached in an option, which is automatically loaded during WordPress startup. After that, when retrieving values, the performance impact of using <code>the_value()</code> and <code>get_the_value()</code> is zero to none. Now Ultimate Fields is the fastest in it&apos;s class.', 'ultimate-fields' ) ?></p>
			</div>
		</div>
	</div>

	<hr />

	<div class="uf-welcome-section uf-welcome-section-centered">
		<h2><?php _e( 'Better fields', 'ultimate-fields' ) ?></h2>

		<div class="uf-welcome-columns">
			<div class="uf-welcome-column">
				<h4><?php _e( 'Repeaters', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Repeaters just became way more powerful.', 'ultimate-fields' ) ?></p>
				<p><?php _e( 'They now support tabs, full-screen mode for groups, table layouts, icons, color customization, minimum/maximum limits and that is not even all!', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column">
				<h4><?php _e( 'Complex Field', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'The new Complex field allows you to add multiple sub-fields to the same field. Those fields may even be loaded from an existing container, making reusable fields possible.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'Relational fields', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'The new WP Object, WP Objects and Link field allow users to select all types of content through a JS-driven and intuitive field!', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'Conditional Logic', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Conditional Logic is now supported in the User Interface. Not only that, but you can actually create multiple groups of rules.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'Unlimited nesting', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Now you can not just create repeaters, but also have fields within a complex field within a repeater within a layout field. From now on, the sky is the limit!', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'Minor UI Tweaks', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Most existing fields will now work and look better, thanks to various tweaks and improvements in the interface.', 'ultimate-fields' ) ?></p>
			</div>
		</div>
	</div>

	<hr />

	<div class="uf-welcome-section uf-welcome-section-centered">
		<h2><?php _e( '...and much more', 'ultimate-fields' ) ?></h2>

		<div class="uf-welcome-columns">
			<div class="uf-welcome-column">
				<h4><?php _e( 'JSON Import/Export', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Reliably export and import fields from the admin as JSON files.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column">
				<h4><?php _e( 'JSON Synchronization', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Fields can be automatically saved and synchronized in your theme.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'New website & Documentation', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'The website of the plugin is now live and full of docs.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'New layout', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'The new Grid layout allows fields to be added to columns.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'REST API', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Automatically expose fields in the REST API.', 'ultimate-fields' ) ?></p>
			</div>

			<div class="uf-welcome-column uf-welcome-column-top">
				<h4><?php _e( 'Admin columns', 'ultimate-fields' ) ?></h4>
				<p><?php _e( 'Easily display the values of fields as columns in listings.', 'ultimate-fields' ) ?></p>
			</div>
		</div>
	</div>

	<hr>

	<div class="uf-welcome-section">
		<p>
			<a href="<?php echo admin_url( 'post-new.php?post_type=ultimate-fields&demo' ) ?>" class="button button-hero button-primary"><?php _e( 'Create your first container', 'ultimate-fields' ) ?></a>
		</p>
	</div>
</div>
