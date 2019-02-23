<?php
/**
 * Returns an instance of the plugin's core.
 *
 * This function allows access to some general functionality of the plugin. See the class for more.
 *
 * @since 3.0
 * @return Ultimate_Fields\Core
 */
function ultimate_fields() {
	return Ultimate_Fields\Core::instance();
}

/**
 * Retrieves the value of a field.
 *
 * The value that is returned by this function will not be processed based on the fields' settings.
 * This means that if you are retrieving the value of an image, you will get it's ID instead of the
 * option, selected when creating the field.
 *
 * @since 3.0
 *
 * @param  string $name The name of the field, whose value is needed.
 * @param  mixed  $type The type of item, which the field is associated with, ex. 1 or user_2.
 * @return mixed        The value of the field if found, otherwise false.
 */
function get_value( $name, $type = '' ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->get_value( $name, $type );
}

/**
 * Retrieves the processed value of a field.
 *
 * The value that is returned by this function will be processed based on the fields' settings.
 * This means that if you are retriving the value of an image, it will be formatted based on the
 * settings of the field it belongs to.
 *
 * @since 3.0
 *
 * @param  string $name The name of the field, whose value is needed.
 * @param  mixed  $type The type of item, which the field is associated with, ex. 1 or user_2.
 * @return mixed        The value of the field if found, otherwise false.
 */
function get_the_value( $name, $type = '' ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->get_the_value( $name, $type );
}

/**
 * Outputs the processed value of a field.
 *
 * @see get_the_value()
 * @since 3.0
 *
 * @param  string $name The name of the field, whose value is needed.
 * @param  mixed  $type The type of item, which the field is associated with, ex. 1 or user_2.
 * @return mixed        The value of the field if found, otherwise false.
 */
function the_value( $name, $type = '' ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->the_value( $name, $type );
}

/**
 * Updates the value of a field.
 *
 * @since 3.0
 *
 * @param string $name  The name of the field, whose value will be updated.
 * @param mixed  $value The value to update.
 * @param mixed  $type  The type of item, which the field is associated with, ex. 1 or user_2.
 * @return bool         An indicator whether the value was updated or not.
 */
function update_value( $name, $value, $type = '' ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->update_value( $name, $value, $type );
}

/**
 * Checks if there are any/more groups to iterate from the value of a repeater, complex or layout field.
 *
 * Use this function in conjunction with the_group(), get_group_type(), and *_sub_value() functions.
 * Example:
 *
 * <?php if( have_groups( 'content_blocks' ) ): ?>
 * 	<div class="blocks">
 * 	<?php while( have_groups( 'content_blocks' ) ): the_group() ?>
 * 		<div class="block block-<?php echo get_group_type() ?>">
 * 			<?php the_sub_value( 'title' ) ?>
 * 		</div>
 * 	<?php endwhile ?>
 * 	</div>
 * <?php endif ?>
 *
 * @since 3.0
 *
 * @param string $name  The name of the field, whose value will be looped.
 * @param mixed  $type  The type of item, which the field is associated with, ex. 1 or user_2.
 * @return bool         An indcator if the loop can continue.
 */
function have_groups( $name, $type = null ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->have_groups( $name, $type );
}

/**
 * Proceeds to the next group in the value of a repeater field or a layout row.
 *
 * @see See the example above.
 *
 * @since 3.0
 */
function the_group() {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->the_group();
}

/**
 * Returns the type of the current group within repeatable fields.
 *
 * @since 3.0
 *
 * @return string
 */
function get_group_type() {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->get_group_type();
}

/**
 * Outputs the type of the current group within repeatable fields.
 *
 * @since 3.0
 */
function the_group_type() {
	$api = Ultimate_Fields\Data_API::instance();
	echo $api->get_group_type();
}

/**
 * Returns a the value of a sub-field within repeatable fields.
 *
 * @since 3.0
 *
 * @param string $name The name of the field whose value is needed.
 * @return mixed
 */
function get_sub_value( $name ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->get_sub_value( $name );
}

/**
 * Returns a processed value from a repeater/layout group.
 *
 * @since 3.0
 *
 * @param string $name The name of the value.
 * @return mixed
 */
function get_the_sub_value( $name ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->get_the_sub_value( $name );
}

/**
 * Outputs the value of a sub-field within repeatable field looops.
 *
 * @since 3.0
 *
 * @param string $name The name of the field whose value is needed.
 * @return mixed
 */
function the_sub_value( $name ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->the_sub_value( $name );
}

/**
 * Updates a sub-value while a loop or layout mode is happening.
 *
 * NB: This function will update the value, but not save it in the database.
 *     Since if you are changing one value in a repeater loop, you will probably going to change
 *     a lot of other values simultaneously. In order to improve database performance, only mass-
 *     saving is enabled. Therefore, you need to call save_sub_values() when you are finished!.
 *
 * @since 3.0
 *
 * @param string $name  The name of the needed value.
 * @param mixed  $value The value to set.
 * @return bool
 */
function update_sub_value( $name, $value ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->update_sub_value( $name, $value );
}

/**
 * After changes are done to the sub-values within a repeater loop, this will commit them to the DB.
 *
 * @since 3.0
 *
 * @param string $name The name of the repeater/layout/complex field that should be saved.
 * @param string $type The type of object the field and value belong to.
 * @return bool        An indicator if the save was successfull.
 */
function save_sub_values( $name, $type = '' ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->save_sub_values( $name, $type );
}

/**
 * Enables using the_value() as a shortcode
 *
 * @param mixed $args The args that are provided to the shortcode
 * @param string $content The content that is in the shortcode. It should be empty
 * @return string The output of the fields
 */
function uf_shortcode( $atts, $content = null ) {
	if( is_array( $atts ) && ! empty( $atts ) && ( isset( $atts[ 'name' ] ) || isset( $atts[ 'key' ] ) ) ) {
		$name = isset( $atts[ 'key' ] )
			? $atts[ 'key' ]
			: $atts[ 'name' ];

		$type = isset( $atts[ 'type' ] )
			? $atts[ 'type' ]
			: false;

		return get_the_value( $name, $type );
	}

	return __( 'The [uf] shortcode requires at least the "name" parameter!', 'ultimate-fields' );
}

add_shortcode( 'uf', 'uf_shortcode' );
add_shortcode( 'value', 'uf_shortcode' );

/**
 * Checks if there are any additional rows to iterate when using a layout field.
 *
 * Please use this function in conjunction with the_row(), have_groups(), the_group() and sub-value
 * functions in order to have a proper loop. Example:
 *
 * <?php if( have_layout_rows( 'layout_blocks' ) ): ?>
 * 	<div class="layout">
 * 	<?php while( have_layout_rows( 'layout_blocks' ) ): the_row() ?>
 * 	<div class="layout-row">
 * 		<?php while( have_groups( 'layout_blocks' ) ): the_group() ?>
 * 			<div class="layout-column layout-column-<?php the_group_width() ?>">
 * 				<?php the_sub_value( 'title' ) ?>
 * 			</div>
 * 		<?php endwhile ?>
 * 	</div>
 * 	<?php endwhile ?>
 * 	</div>
 * <?php endif ?>
 *
 * @since 3.0
 *
 * @param string $name The name of the field whose value will be iterated.
 * @param mixed  $type The type of data the field is associated with.
 * @return bool        An indicator if there are any more rows to process.
 */
function have_layout_rows( $name, $type = null ) {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->have_rows( $name, $type );
}

/**
 * Proceeds to the next row when looping a layout field.
 *
 * @since 3.0
 */
function the_layout_row() {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->the_row();
}

/**
 * Returns the width of the current group when looping through layout fields.
 *
 * @since 3.0
 *
 * @return int The width of the group in columns.
 */
function get_group_width() {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->get_group_width();
}

/**
 * Displays the width of the current group when looping through layout fields.
 *
 * @since 3.0
 */
function the_group_width() {
	$api = Ultimate_Fields\Data_API::instance();
	return $api->the_group_width();
}

/**
 * Initializes and prepares containers to be displayed in the front-end
 * through the uf_form() function.
 *
 * It's paramount to use this function before calling get_header(), as
 * this way the front-end forms may do any of the following:
 *
 * 1. Load all containers which are to be displayed.
 * 2. Enqueues scripts and styles.
 * 3. When a form is submitted, saves its data.
 * 4. Redirects the user when submitted.
 *
 * ... and much more.
 *
 * @since 3.0
 * @param mixed[] $args Arguments for displaying the containers/forms.
 */
function uf_head( $args = array() ) {
	return Ultimate_Fields\Form::instance()->head( $args );
}

/**
 * Displays forms in the front end when the moment is right.
 *
 * @see uf_head() - this function needs to be called before uf_form().
 * @since 3.0
 */
function uf_form() {
	return Ultimate_Fields\Form::instance()->form();
}

