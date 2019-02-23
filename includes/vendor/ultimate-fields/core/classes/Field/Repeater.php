<?php
namespace Ultimate_Fields\Field;

use Ultimate_Fields\Field;
use Ultimate_Fields\Container\Repeater_Group;
use Ultimate_Fields\Datastore\Group as Group_Datastore;
use Ultimate_Fields\Template;
use Ultimate_Fields\Helper\Group_Values;
use Ultimate_Fields\Helper\Groups_Iterator;
use ReflectionClass;

/**
 * Handles the "input" of the repeater field.
 *
 * @since 3.0
 */
class Repeater extends Field {
	/**
	 * Holds the groups of the repeater.
	 *
	 * @since 3.0
	 * @var Repeater_Group[]
	 */
	protected $groups = array();

	/**
	 * Holds the minimum number of rows.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $minimum = 0;

	/**
	 * Holds the maximum number of rows.
	 *
	 * @since 3.0
	 * @var int
	 */
	protected $maximum = 0;

	/**
	 * Holds the text for the add button.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $add_text = '';

	/**
	 * Holds the placeholder text for the field.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $placeholder_text = '';

	/**
	 * Holds the custom background color when one is set.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $background_color = '';

	/**
	 * Holds available chooser types.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $chooser_type = 'widgets';

	/**
	 * Holds the layout of the field.
	 * Possible options: 'normal', 'table'.
	 *
	 * @since 3.0
	 */
	protected $layout = 'normal';

	/**
	 * Holds the availalble chooser types.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $possible_chooser_types = array( 'widgets', 'dropdown', 'tags' );

	/**
	 * Adds a group to the repeater.
	 *
	 * @since 3.0
	 *
	 * @param string|Repeater_Group $group Either the ID of a group or a generated one.
	 * @param array                 $args  Arguments for the group.
	 *                                     @see Ultimate_Fields\Container\Repeater_Group::__construct().
	 * @return Ultimate_Fields\Field\Repeater The instance of the field.
	 */
	public function add_group( $group, $args = array() ) {
		# If the group has already been created, just use it.
		if( is_a( $group, Repeater_Group::class ) ) {
			$this->groups[ $group->get_id() ] = $group;
		} else {
			$group = new Repeater_Group( $group, $args );
			$this->groups[ $group->get_id() ] = $group;
		}

		return $this;
	}

	/**
	 * Creates a new simple group, based on fields.
	 * Use this only when it's knowh that the repeater will have a single group.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field[] The fields to add.
	 * @return Ultimate_Fields\Field\Repeater
	 */
	public function add_fields( $fields ) {
		$this->add_group( 'entry', array(
			'title'  => '',
			'fields' => $fields
		));

		return $this;
	}

	/**
	 * Exports the settings for the field that will be used in JS.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_field() {
		$settings = parent::export_field();

		# Export groups
		$settings[ 'groups' ] = array();
		foreach( $this->groups as $group ) {
			if( 'table' == $this->layout ) {
				$group->fix_table_widths();
			}

			$settings[ 'groups' ][] = $group->export_settings();
		}


		/**
		 * Add the limit of rows for the field.
		 */
		$settings[ 'minimum' ]      = $this->minimum;
		$settings[ 'maximum' ]      = $this->maximum;
		$settings[ 'chooser_type' ] = $this->get_chooser_type();

		/**
		 * Add the needed labels & attributes.
		 */
		$settings[ 'add_text' ]         = $this->get_add_text();
		$settings[ 'placeholder_text' ] = $this->get_placeholder_text();
		$settings[ 'background' ]       = $this->get_background_color();
		$settings[ 'layout' ]           = $this->get_layout();

		return $settings;
	}

	/**
	 * Exports the data of the field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export_data() {
		$raw   = $this->get_value( $this->name );
		$value = array();

		if( ! $raw && ! is_array( $raw ) && $this->default_value ) {
			$raw = $this->default_value;
		}

		# Use the default value if needed
		if( ( false === $raw || null === $raw ) && is_array( $this->default_value ) ) {
			$raw = $this->default_value;
		}

		# If there are groups, go through each of them.
		$i = 0;
		if( $raw ) foreach( $raw as $raw_row ) {
			$datastore = new Group_Datastore( $raw_row );

			# Switch variables for version 1
			if( ! isset( $raw_row[ '__type' ] ) && isset( $raw_row[ 'type' ] ) ) {
				$raw_row[ '__type' ] = $raw_row[ 'type' ];
				unset( $raw_row[ 'type' ] );
			}

			# Groups without type may be ignored
			if( ! isset( $raw_row[ '__type' ] ) ) {
				if( 1 === count( $this->groups ) ) {
					$group_keys = array_keys( $this->groups );
					$raw_row[ '__type' ] = array_shift( $group_keys );
				} else {
					continue;
				}
			}

			# If the type of group is no longer available, skip
			if( ! isset( $this->groups[ $raw_row[ '__type' ] ] ) )
				continue;

			# Get the datastore and export data
			$group = $this->groups[ $raw_row[ '__type' ] ];
			$group->set_datastore( $datastore );
			$exported_row = $group->export_data();
			$exported_row[ '__index' ] = $i++;

			$hidden = isset( $exported_row[ '__hidden' ] )
				? $exported_row[ '__hidden' ]
				: false;

			/**
			 * Allows the visiblity of an existing group to be changed.
			 * This modifies the __hidden property of the group, so returning true will collapse it.
			 *
			 * @since 3.0
			 *
			 * @param bool                $hidden   Whether the group is already hidden or not.
			 * @param Ultimate_Fields\Container\Group $group    The group that is to be controlled.
			 * @param Ultimate_Fields\Field\Repeater  $repeater The repeater that contains the group.
			 * @return bool
			 */
			$exported_row[ '__hidden' ] = apply_filters( 'uf.repeater.group_hidden', $hidden, $group, $this );

			$value[] = $exported_row;
		}

		return array(
			$this->name => $value
		);
	}

	/**
	 * Adds the templates of the field to the queue.
	 *
	 * @since 3.0
	 */
	public function templates() {
		if( 'table' == $this->layout ) {
			Template::add( 'field-repeater-table', 'field/repeater/table' );
			Template::add( 'repeater-heading', 'field/repeater/heading' );
			Template::add( 'table-row', 'field/repeater/table-row' );
			Template::add( 'cell-wrap', 'field/wrap/cell' );
		} else {
			Template::add( 'field-repeater', 'field/repeater/base' );

			if( 'dropdown' == $this->chooser_type ) {
				Template::add( 'repeater-dropdown',  'field/repeater/dropdown' );
			} else if( 'tags' == $this->chooser_type ) {
				Template::add( 'repeater-tags', 'field/repeater/tags' );
			} else {
				Template::add( 'repeater-prototype', 'field/repeater/prototype' );
			}
		}

		Template::add( 'repeater-group',  'field/repeater/group' );
		Template::add( 'popup-group',     'field/repeater/popup-group' );
		Template::add( 'overlay-wrapper', 'overlay-wrapper' );
		Template::add( 'overlay-alert',   'overlay-alert' );
	}

	/**
	 * Enqueues the scripts for the field and its sub-fields.
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'uf-field-repeater' );

		# Enqueue the scripts for all groups
		foreach( $this->groups as $group ) {
			$group->enqueue_scripts();
		}

		# Localize
		ultimate_fields()
			->localize( 'repeater-required',        __( 'This field is required.', 'ultimate-fields' ) )
			->localize( 'repeater-add',             __( 'Add', 'ultimate-fields' ) )
			->localize( 'repeater-incorrect-value', __( 'There are invalid values within "%s"', 'ultimate-fields' ) )
			->localize( 'repeater-min-value',       __( '%s requires at least %d entries to be added.', 'ultimate-fields' ) )
			->localize( 'repeater-max-value',       __( '%s allows maximum %d entries to be added.', 'ultimate-fields' ) )
			->localize( 'repeater-save',            _x( 'Save', 'repeater', 'ultimate-fields' ) )
			->localize( 'repeater-cancel',          _x( 'Cancel', 'repeater', 'ultimate-fields' ) )
			->localize( 'repeater-close',           _x( 'Close', 'repeater', 'ultimate-fields' ) )
			->localize( 'repeater-save',            __( 'Save %s', 'ultimate-fields' ) )
			->localize( 'repeater-delete',          __( 'Delete %s', 'ultimate-fields' ) )
			->localize( 'repeater-no-groups',       __( 'There are no groups added to this repeater field.', 'ultimate-fields' ) );;
	}

	/**
	 * Sets the minimum count of groups to be added.
	 *
	 * @since 3.0
	 * @see the $minimum varible above.
	 *
	 * @param int $minimum The new minimum.
	 * @return Ultimate_Fields\Field\Repeater The instance of the current field, useful for chaining.
	 */
	public function set_minimum( $minimum ) {
		$this->minimum = intval( $minimum );

		return $this;
	}

	/**
	 * Returns the rows minimum of the field.
	 *
	 * @since 3.0
	 *
	 * @return int.
	 */
	public function get_minimum() {
		return $this->minimum;
	}

	/**
	 * Sets the maximum count of groups to be added.
	 *
	 * @since 3.0
	 * @see the $maximum varible above.
	 *
	 * @param int $maximum The new maximum.
	 * @return Ultimate_Fields\Field\Repeater The instance of the current field, useful for chaining.
	 */
	public function set_maximum( $maximum ) {
		$this->maximum = intval( $maximum );

		return $this;
	}

	/**
	 * Returns the rows maximum of the field.
	 *
	 * @since 3.0
	 *
	 * @return int.
	 */
	public function get_maximum() {
		return $this->maximum;
	}

	/**
	 * Returns the available groups.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Container\Repeater_Group[]
	 */
	public function get_groups() {
		return $this->groups;
	}

	/**
	 * Changes the text that will be used for the "add" button.
	 *
	 * This button will only be added to the repeater when there is a single
	 * group. If you want to change the text, that appears as a placeholder,
	 * @see the set_palceholder_text method.
	 *
	 * @since 3.0
	 *
	 * @param string $text The text for the add button.
	 * @return Ultimate_Fields\Field\Repeater The istance of the field, useful for chaining.
	 */
	public function set_add_text( $text ) {
		$this->add_text = $text;

		return $this;
	}

	/**
	 * Returns the text for the "Add" button.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_add_text() {
		if( ! $text = $this->add_text ) {
			$text = __( 'Add entry', 'ultimate-fields' );
		}

		return $text;
	}

	/**
	 * Changes the placeholder text that will be displayed when there are no entries.
	 *
	 * @since 3.0
	 *
	 * @param string $text The palceholder text.
	 * @return Ultimate_Fields\Field\Repeater The istance of the field, useful for chaining.
	 */
	public function set_placeholder_text( $text ) {
		$this->placeholder_text = $text;

		return $this;
	}

	/**
	 * Returns the text for the placeholder.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_placeholder_text() {
		if( ! $text = $this->placeholder_text ) {
			if( count( $this->get_groups() ) > 1 && 'widgets' == $this->get_chooser_type() ) {
				$text = __( 'Drag an item here to create a new entry.', 'ultimate-fields' );
			} else {
				$text = sprintf( __( 'Please click the "%s" button to add a new entry.', 'ultimate-fields' ), $this->get_add_text() );
			}
		}

		return $text;
	}

	/**
	 * Sets the background for the repeater.
	 *
	 * @since 3.0
	 *
	 * @param string $background
	 * @return Ultimate_Fields\Field\Repeater the repeater, in order to allow chaining.
	 */
	public function set_background_color( $background ) {
		$this->background_color = $background;

		return $this;
	}

	/**
	 * Returns the background of the repeater.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_background_color() {
		return '#fff' == $this->background_color || '#ffffff' == $this->background_color
			? ''
			: $this->background_color;
	}

	/**
	 * Sets the chooser type.
	 *
	 * This modifies the way prototypes of multiple groups are displayed. Possible options:
	 * - 'widtgets': Displays prototypes as closed metaboxes, similar to the widgets screen.
	 * - 'dropdown': Displays a dropdown, which allows the user to select a type.
	 *
	 * @since 3.0
	 *
	 * @param string $type The selected type for the chooser.
	 * @return Ultimate_Fields\Field\Repeater The isntance of the field, needed for chaining methods.
	 */
	public function set_chooser_type( $type ) {
		if( ! in_array( $type, $this->possible_chooser_types ) ) {
			# This type is not supported.
			return $this;
		}

		$this->chooser_type = $type;

		return $this;
	}

	/**
	 * Returns the current chooser type.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_chooser_type() {
		return $this->chooser_type;
	}

	/**
	 * Allows the layout of the field to be changed.
	 *
	 * @since 3.0
	 *
	 * @param string $layout The needed layout.
	 * @return Ultimate_Fields\FIeld\Repeater
	 */
	public function set_layout( $layout ) {
		if( ! in_array( $layout, array( 'table', 'normal' ) ) ) {
			$layout = 'normal';
		}

		$this->layout = $layout;

		return $this;
	}

	/**
	 * Returns the layout of the field.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_layout() {
		return $this->layout;
	}


	/**
	 * Retrieves the value of the field from a source and saves it in the current datastore.
	 *
	 * This method should not perform any validation - if something is wrong with
	 * the value of the field, simply don't save it. Validation will be performed
	 * later and will return an error anyway, if the internal value is empty.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $source The source which the value of the field should be available in.
	 */
	public function save( $source ) {
		$value = array();

		if(
			isset( $source[ $this->name ] )
			&& (
				is_array( $source[ $this->name ] )
				|| is_a( $source[ $this->name ], Groups_Iterator::class )
			)
		) {
			foreach( $source[ $this->name ] as $row ) {
				if( ! isset( $row[ '__type' ] ) || ! isset( $this->groups[ $row[ '__type' ] ] ) ) {
					continue;
				}

				$group = $this->groups[ $row[ '__type' ] ];
				$group->save( $row );
				$value[] = $group->get_datastore()->get_values();
			}
		}

		$this->datastore->set( $this->name, $value );
	}

	/**
	 * Imports the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data for the field.
	 */
	public function import( $data ) {
		parent::import( $data );

		# Parse groups
		foreach( $data[ 'repeater_groups' ] as $raw ) {
			if( isset( $raw[ 'name' ] ) ) {
				$id = $raw[ 'name' ];
			} elseif( isset( $raw[ 'type' ] ) ) {
				$id = $raw[ 'type' ];
			} else {
				$id = $raw[ '__type' ];
			}

			$group = new Repeater_Group( $id, $raw[ 'title' ] );

			if( isset( $raw[ 'fields_source' ] ) && 'container' == $raw[ 'fields_source' ] ) {
				// container
				$group->load_from( $raw[ 'container' ] );
			} else {
				// manual
				foreach( $raw[ 'fields' ] as $field ) {
					$group->add_field( $field );
				}
			}

			$group->proxy_data_to_setters( $raw, array(
				'edit_mode'        => 'set_edit_mode',
				'maximum'          => 'set_maximum',
				'description'      => 'set_description',
				'title_background' => 'set_title_background',
				'title_color'      => 'set_title_color',
				'border_color'     => 'set_border_color',
				'icon'             => 'set_icon',
				'title_template'   => 'set_title_template',
				'layout'           => 'set_layout'
			));

			$this->add_group( $group );
		}

		# Normal values
		$this->proxy_data_to_setters( $data, array(
			'repeater_minimum'          => 'set_minimum',
			'repeater_maximum'          => 'set_maximum',
			'repeater_add_text'         => 'set_add_text',
			'repeater_placeholder_text' => 'set_placeholder_text',
			'repeater_layout'           => 'set_layout',
			'repeater_chooser_type'     => 'set_chooser_type',
			'repeater_background_color' => 'set_background_color',
		));
	}

	/**
	 * Generates the data for file exports.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function export() {
		$settings = parent::export();

		$this->export_properties( $settings, array(
			'minimum'          => array( 'repeater_minimum', 0 ),
			'maximum'          => array( 'repeater_maximum', 0 ),
			'add_text'         => array( 'repeater_add_text', '' ),
			'placeholder_text' => array( 'repeater_placeholder_text', '' ),
			'layout'           => array( 'repeater_layout', 'normal' ),
			'chooser_type'     => array( 'repeater_chooser_type', 'widgets' ),
			'background_color' => array( 'repeater_background_color', '' ),
		));

		# Export groups
		$settings[ 'repeater_groups' ] = array();
		foreach( $this->groups as $group ) {
			$settings[ 'repeater_groups' ][] = $group->export();
		}

		return $settings;
	}

	/**
	 * Handles a value for the front-end.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to handle.
	 * @param mixed $source The context of the value.
	 * @return mixed
	 */
	public function handle( $value, $source = null ) {
		if( ! $value ) {
			return new Groups_Iterator( array() );
		}

		$rows = array();
		foreach( $value as $row ) {
			$group = null;

			if( isset( $row[ '__type' ] ) ) {
				$type = $row[ '__type' ];

				foreach( $this->groups as $g ) {
					if( $g->get_id() == $type ) {
						$group = $g;
						break;
					}
				}
			}

			$rows[] = new Group_Values( $row, $group );
		}

		$iterator = new Groups_Iterator( $rows );
		$iterator->set_source( $source );
		return $iterator;
	}

	/**
	 * Performs AJAX.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action that is being performed.
	 * @param mixed  $item   The item that is being edited.
	 */
	public function perform_ajax( $action, $item ) {
		foreach( $this->groups as $group ) {
			$group->perform_ajax( $item, $action );
		}
	}

	/**
	 * Beautifies the value that is displayed within admin columns.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to process.
	 * @return mixed
	 */
	public function prepare_admin_column( $value ) {
		$entries = is_array( $value ) ? count( $value ) : 0;

		return sprintf( _n( '%s entry', '%s entries', $entries, 'ultimate-fields' ), $entries );
	}
}
