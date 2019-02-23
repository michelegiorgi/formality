<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Field;
use Ultimate_Fields\UI\Field_Container;
use Ultimate_Fields\UI\Field_Editor;
use Ultimate_Fields\Datastore\Group as Datastore;
use Ultimate_Fields\Dependency\Group as Dependency_Group;

/**
 * This is the basic class for fields in the UI.
 *
 * @since 3.0
 */
class Field_Helper {
	/**
	 * Creates a new field and imports settings for it.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data The saved data about the field.
	 * @return Field
	 */
	public static function import_from_meta( $field_data ) {
		$type = $field_data[ 'type' ];

		# Locate the particular class for the field
		$class_name = ultimate_fields()->generate_class_name( "UI/Field_Helper/$type" );

		# If there is no particular class, use a generic field.
		if( ! class_exists( $class_name ) ) {
			$class_name = Field_Helper::class;
		}

		# Create the field and import the meta
		$field = new $class_name();
		$field->import( $field_data );

		return $field;
	}

	/**
	 * Exports the data from a field.
	 *
	 * This function will retrieve the settings of the field, transform them into a field-container
	 * compatible data in order to use the fields of the Field_Editor class to generate the final field.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Field $field The field whose data is needed.
	 * @return mixed[]
	 */
	public static function get_field_data( $field ) {
		static $container;

		# Prepare the needed container
		if( is_null( $container ) ) {
			$container = Field_Container::instance();
			$container->add_fields( Field_Editor::instance()->fields() );
		}

		# Export the settings from the field itself
		$source = $field->export();

		# Add the proper default values
		if( isset( $source[ 'default_value' ] ) ) {
			$default_key = 'default_value_' . strtolower( $source[ 'type' ] );
			$source[ $default_key ] = $source[ 'default_value' ];
		}

		# Check the validation rule
		if( isset( $source[ 'validation_rule' ] ) && $source[ 'validation_rule' ] ) {
			$rule = $source[ 'validation_rule' ];

			if( '^$' === $rule ) {
				$source[ 'validation_rule' ] = 'null';
			} else {
				$source[ 'validation_rule' ]     = 'regex';
				$source[ 'required_expression' ] = $rule;
			}
		}

		# Add dependencies
		if( isset( $source[ 'dependencies' ] ) && $source[ 'dependencies' ] ) {
			$source[ 'enable_conditional_logic' ] = true;
			$source[ 'conditional_logic' ] = array();

			foreach( $source[ 'dependencies' ] as $raw_group ) {
				$group = array();

				foreach( $raw_group as $raw ) {
					$group[] = array(
						'selector' => $raw[ 'field' ],
						'compare'  => $raw[ 'compare' ],
						'value'    => $raw[ 'value' ]
					);
				}

				$source[ 'conditional_logic' ][] = array( 'rules' => $group );
			}
		}

		# Let field classes prepare data
		$type           = $source[ 'type' ];
		$class_name     = ultimate_fields()->generate_class_name( "UI/Field_Helper/$type" );
		$prepare_method = array( $class_name, 'prepare_field_data' );

		if( method_exists( $class_name, 'prepare_field_data' ) ) {
			$source = call_user_func( $prepare_method, $source );
		}

		# Create a datastore and let the container put the settings there
		$ds = new Datastore();
		$container->set_datastore( $ds );
		$errors = $container->save( $source );

		return $ds->get_values();
	}

	/**
	 * Imports data about the field from post meta.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $field_data the data about the field.
	 */
	public function import( $meta ) {
		$this->meta = $meta;
	}

	/**
	 * Prepares the import data for a field.
	 *
	 * @since 3.0
	 *
	 * @return mixed[] The data for $field->import()
	 */
	protected function prepare_setup_data() {
		$meta = $this->meta;

		if( isset( $meta[ 'default_value_' . strtolower( $meta[ 'type' ] ) ] ) ) {
			$meta[ 'default_value' ] = $meta[ 'default_value_' . strtolower( $meta[ 'type' ] ) ];
		}

		if( isset( $meta[ 'validation_rule' ] ) ) switch( $meta[ 'validation_rule' ] ) {
			case 'none':
				$meta[ 'validation_rule' ] = false;
				break;

			case 'not_null':
				unset( $meta[ 'validation_rule' ] );
				break;

			case 'null':
				$meta[ 'validation_rule' ] = '~^$~';
				break;

			case 'value':
				$meta[ 'validation_rule' ] = '^' . preg_quote( $meta[ 'required_value' ] ) . '$';
				break;

			case 'regex':
				$meta[ 'validation_rule' ] = $meta[ 'required_expression' ];
				break;
		}

		return $meta;
	}

	/**
	 * Sets the field up.
	 *
	 * @since 3.0
	 *
	 * @return Field
	 */
	public function setup_field() {
		$meta  = $this->meta;
		$name  = $meta[ 'name' ];
		$label = $meta[ 'label' ];
		$type  = strtolower( $meta[ 'type' ] );

		$field = Field::create( $type, $name, $label );
		if( ! $field ) {
			return false;
		}
		$field->import( $this->prepare_setup_data() );
		$this->setup_dependencies( $meta, $field );
		return $field;
	}

	/**
	 * Sets up the dependencies of the field.
	 *
	 * @since 3.0
	 *
	 * @param mixed[]   $data  The data for the field.
	 * @param Ultimate_Fields\Field $field The field that is being set up.
	 */
	protected function setup_dependencies( $data, $field ) {
		if( ! isset( $data[ 'enable_conditional_logic' ] ) || ! $data[ 'enable_conditional_logic' ] ) {
			return;
		}

		if( ! isset( $data[ 'conditional_logic' ] ) || empty( $data[ 'conditional_logic' ] ) ) {
			return;
		}

		foreach( $data[ 'conditional_logic' ] as $raw_group ) {
			$rules = new Dependency_Group;

			foreach( $raw_group[ 'rules' ] as $raw ) {
				$rule = array(
					'field'   => $raw[ 'selector' ],
					'value'   => $raw[ 'value' ],
					'compare' => $raw[ 'compare' ]
				);

				$rules->add_rule( $rule );
			}

			$field->add_dependency_group( $rules );
		}
	}

	/**
	 * Prepares data for import.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data that has been already generated + source data.
	 * @return mixed[]
	 */
	public static function prepare_field_data( $data ) {
		if( isset( $data[ 'select_options' ] ) && is_array( $data[ 'select_options' ] ) ) {
			$rows = array();

			foreach( $data[ 'select_options' ] as $key => $value ) {
				$rows[] = $key . ' :: ' . $value;
			}

			$data[ 'select_options' ] = implode( "\n", $rows );
		}

		return $data;
	}

	/**
	 * Prepares data for usage within the UI.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The previously existing data.
	 * @return mixed[]
	 */
	public final static function prepare_field_data_for_editor( $data ) {
		$type = $data[ 'type' ];

		# Locate the particular class for the field
		$class_name = ultimate_fields()->generate_class_name( "UI/Field_Helper/$type" );

		# If there is no particular class, use a generic field.
		if( class_exists( $class_name ) && method_exists( $class_name, 'prepare_editor_data' ) ) {
			return call_user_func( array( $class_name, 'prepare_editor_data' ), $data );
		} else {
			return $data;
		}
	}
}
