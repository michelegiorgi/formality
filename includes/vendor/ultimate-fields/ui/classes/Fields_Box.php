<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\Template;
use Ultimate_Fields\Datastore\Group as Datastore;
use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\UI\Field_Editor;
use Ultimate_Fields\UI\Field_Container;

class Fields_Box {
	/**
	 * Holds the post whose fields are being saved.
	 *
	 * @since 3.0
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * Sets the new post for the box.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post for the edited container.
	 */
	public function set_post( $post ) {
		$this->post = $post;
	}

	/**
	 * Displays the box.
	 *
	 * @since 3.0
	 */
	public function display() {
		# Output the neccessary settings JSON for fields and data
		$editor = Field_Editor::instance();
		$editor->output();

		$fields = get_post_meta( $this->post->ID, '_group_fields', true );
		if( ! $fields ) {
			$fields = array();
		} else {
			$container = $editor->container;
			$processed = array();

			foreach( $fields as $field ) {
				$datastore = new Datastore( $field );
				$container->set_datastore( $datastore );
				$exported = $container->export_data();
				$exported = $this->prepare_conditional_logic( $exported, $container );
				$exported = Field_Helper::prepare_field_data_for_editor( $exported );

				$processed[] = $exported;
			}


			$fields = $processed;
		}

		# Include the template for fields
		Template::instance()->include_template( 'ui/fields-box.php', array(
			'existing' => json_encode( $fields )
		));

		Template::instance()->output_templates();

		?>
		<script type="text/javascript">
		jQuery( document ).trigger( 'uf-ui-init-editor' );
		</script>
		<?php
	}

	/**
	 * Outputs all necessary JSON.
	 *
	 * @since 3.0
	 */
	public function output() {
		$settings = $this->get_settings();

		printf(
			'<script type="text/json" class="uf-field-settings">%s</script>',
			json_encode( $settings )
		);

		Template::add( 'ui-field-popup', 'ui/field-popup' );
	}

	/**
	 * Saves fields along with the post.
	 *
	 * @since 3.0
	 */
	public function save() {
		if( ! isset( $_POST[ 'uf-group-fields' ] ) )
			return;

		$raw       = json_decode( stripslashes( $_POST[ 'uf-group-fields' ] ), true );
		$data      = array();
		$container = Field_Container::instance();

		$container->add_fields( Field_Editor::instance()->fields() );

		foreach( $raw as $field ) {
			# Prepare the container with a blank datastore
			$datastore = new Datastore;
			$container->set_datastore( $datastore );
			$container->save( $field );

			# Add to the list
			$data[] = $datastore->get_values();
		}

		update_post_meta( $this->post->ID, '_group_fields', $data );
	}

	/**
	 * Prepares data for the conditional logic fields.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $settings The existing settings.
	 */
	public function prepare_conditional_logic( &$settings, $container ) {
		$logic = $settings[ 'conditional_logic' ];

		if( ! $logic ) {
			return $settings;
		}

		foreach( $logic as $group ) {
			foreach( $group[ 'rules' ] as $rule ) {
				if( preg_match( '~^(post|term|user)_(\d+)$~', $rule[ 'value' ], $matches ) ) {
					$prepared = $container->get_fields()[ 'default_value_object' ]->export_objects( array( $rule[ 'value' ] ) );
					$existing = $settings[ 'default_value_object_prepared' ];
					if( ! $existing )
						$existing = array();

					foreach( $prepared as $key => $item ) {
						$existing[] = $item;
					}

					$settings[ 'default_value_object_prepared' ] = $existing;
				}
			}
		}

		return $settings;
	}
}
