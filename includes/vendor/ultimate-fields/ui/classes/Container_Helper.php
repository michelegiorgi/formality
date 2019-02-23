<?php
namespace Ultimate_Fields\UI;

use Ultimate_Fields\UI\Field_Helper;
use Ultimate_Fields\Container as Core_Container;
use Ultimate_Fields\Datastore\Values as Values_Datastore;
use Ultimate_Fields\UI\Post_Type;
use Ultimate_Fields\UI\Location;

/**
 * Prepares and registers containers.
 *
 * @since 3.0
 */
class Container_Helper {
	/**
	 * This variable will contain the core container object when generated.
	 *
	 * @since 3.0
	 * @var Ultimate_Fields\Container
	 */
	protected $container;

	/**
	 * Holds basic properties, which will be used for the creation/export of the core container.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $props = array();

	/**
	 * Fetches container settings based on a post.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post from the back-end.
	 */
	public function import_from_post( $post ) {
		$this->props = array(
			'title'                 => $post->post_title,
			'id'                    => Post_type::instance()->get_container_hash( $post ),
			'hash'                  => Post_type::instance()->get_container_hash( $post ),
			'modified'              => $post->post_modified_gmt,
			'order'                 => intval( $post->menu_order ),

			'layout'                => get_post_meta( $post->ID, 'layout', true ),
			'style'                 => get_post_meta( $post->ID, 'style', true ),
			'description'           => get_post_meta( $post->ID, 'description', true ),
			'description_position'  => get_post_meta( $post->ID, 'description_position', true ),
			'roles'                 => get_post_meta( $post->ID, 'roles', true ),

			'fields'                => get_post_meta( $post->ID, '_group_fields', true ),
			'locations'             => get_post_meta( $post->ID, 'container_locations', true ),
		);
	}

	/**
	 * Imports a container from JSON.
	 *
	 * @since 3.0
	 *
	 * @param mixed[] $data The data to import.
	 * @return bool
	 */
	public function import_from_json( $data ) {
		$this->container = Core_Container::create_from_array( $data );

		if( isset( $data[ 'order' ] ) ) {
			$this->props[ 'order' ] = $data[ 'order' ];
		}
	}

	/**
	 * Accepts an external, already created container.
	 *
	 * @since 3.0
	 *
	 * @param Ultimate_Fields\Container $container The container to work with.
	 */
	public function import_container( $container ) {
		$this->container = $container;
	}

	/**
	 * Registers the container.
	 *
	 * This will be based on the props, generated in import_from_post() and only used when setting
	 * up a container based on database data.
	 *
	 * @since 3.0
	 */
	public function register() {
		# Start with the basics
		$basics = array(
			'title'                => $this->props[ 'title' ] ? $this->props[ 'title' ] : __( 'Untitled', 'ultimate-fields' ),
			'id'                   => $this->props[ 'id' ],
			'layout'               => $this->props[ 'layout' ],
			'style'                => $this->props[ 'style' ],
			'layout'               => $this->props[ 'layout' ],
			'description'          => $this->props[ 'description' ],
			'description_position' => $this->props[ 'description_position' ],
			'roles'                =>  $this->props[ 'roles' ] ? $this->props[ 'roles' ] : array()
		);

		$container = Core_Container::create_from_array( $basics );
		$this->container = $container;

		# Add fields
		foreach( $this->props[ 'fields' ] as $raw ) {
			$helper     = Field_Helper::import_from_meta( $raw );
			$real_field = $helper->setup_field();

			if( $real_field ) {
				$container->add_field( $real_field );
			}
		}

		# Add locations
		foreach( $this->props[ 'locations' ] as $raw ) {
			$type = $raw['__type'];

			/**
			 * Allows the UI class name for a location to be overwritten.
			 *
			 * @since 3.0
			 *
			 * @param mixed  $class_name The name of the class, null by default.
			 * @param string $type       The basic type of the location (ex. `comment`).
			 * @return mixed             Either a string class or the original null.
			 */
			$class_name = apply_filters( 'uf.ui.location.class', null, $type );

			if( is_null( $class_name ) ) {
				$class_name = ultimate_fields()->generate_class_name( "UI/Location/$type" );
			}

			# Silently fail if there is no supported location
			if( class_exists( $class_name ) ) {
				$container->add_location( $class_name::export( $raw, $this ) );
			}
		}

		# Save the container
		$this->container = $container;
	}

	/**
	 * Returns the settings of the container in an exportable array.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_exported_settings() {
		# Get core data
		$data = $this->container->export();

		# Add UI properties to the mix
		if( isset( $this->props[ 'hash' ] ) )  $data[ 'hash' ]  = $this->props[ 'hash' ];
		if( isset( $this->props[ 'order' ] ) && $this->props[ 'order' ] ) {
			$data[ 'order' ] = $this->props[ 'order' ];
		}

		return $data;
	}

	/**
	 * Once being registered, the values of the container can be dumped as JSON
	 * for easy loading within the core.
	 *
	 * @since 3.0
	 *
	 * @param WP_Post $post The post (group) that is being saved.
	 * @param string  $path The path for the JSON.
	 */
	public function dump_json( $post, $path ) {
		# Wrap the container in an array to allow direct import
		$data = $this->get_exported_settings();

		# Save the last modified date in order to be able to compare
		if( isset( $this->props[ 'modified' ] ) ) {
			$data[ 'modified' ] = strtotime( $this->props[ 'modified' ] );
		}

		$data = array( $data );
		$data = json_encode( $data, JSON_PRETTY_PRINT );
		file_put_contents( $path, $data );
	}

	/**
	 * Saves the container as a post type.
	 *
	 * @since 3.0
	 *
	 * @return bool Indicates if everything went well.
	 */
	public function save( $data, $post_id = null ) {
		$post_type = Post_Type::instance();

		$postdata = array(
			'post_title'   => $this->container->get_title(),
			'post_type'    => $post_type->get_slug(),
			'post_name'    => $this->container->get_id(),
			'post_status'  => 'publish',
			'post_content' => ' '
		);

		if( $post_id ) {
			$postdata[ 'ID' ] = $post_id;
		}

		$meta = array();

		if( isset( $data[ 'hash' ] ) ) {
			$meta[ '_uf_hash' ] = $data[ 'hash' ];
		}

		if( isset( $this->props[ 'order' ] ) ) {
			$meta[ 'container_order' ] = $this->props[ 'order' ];
		}

		# Get basic container settings as meta
		foreach( Container_Settings::instance()->get_defaults() as $key => $value ) {
			if( isset( $data[ $key ] ) && $value != $data[ $key ] ) {
				$meta[ $key ] = $data[ $key ];
			}
		}

		# Get the field meta
		$fields = array();
		foreach( $this->container->get_fields() as $field ) {
			$fields[] = Field_Helper::get_field_data( $field );
		}
		$meta[ '_group_fields' ] = $fields;

		# Add locations
		$locations = array();
		foreach( $this->container->get_locations() as $location ) {
			$locations[] = Location::get_location_data( $location, $this->container );
		}
		$meta[ 'container_locations' ] = $locations;

		# Merge meta into the postdata
		$postdata[ 'meta_input' ] = $meta;

		# Insert the post
		$result = wp_insert_post( $postdata );
		$post   = get_post( $result );

		if( ! isset( $data[ 'hash' ] ) )  {
			Post_Type::instance()->get_container_hash( $post );
		}

		return true;
	}

	/**
	 * Returns the internal (core) container.
	 *
	 * @since 3.0
	 *
	 * @return Ultimate_Fields\Container
	 */
	public function get_container() {
		return $this->container;
	}

	/**
	 * Returns a property of the container.
	 *
	 * @since 3.0
	 *
	 * @param string $key The key for the needed property.
	 * @return mixed
	 */
	public function prop( $prop ) {
		return isset( $this->props[ $prop ] )
			? $this->props[ $prop ]
			: false;
	}

	/**
	 * Outputs a JSON script to allow previews of a contaienr to
	 * work when another container is being edited in the interface.
	 *
	 * @since 3.0
	 *
	 * @param string[] $ids The container IDs (hashes) needed.
	 */
	public static function generate_preview_data( $ids ) {
		static $done;

		if( is_null( $done ) ) {
			$done = array();
		}

		$needed = array();
		foreach( (array) $ids as $id ) {
			if( ! in_array( $id, $done ) ) {
				$needed[] = $id;
			}
		}

		// $containers = get_posts(array(
		// 	'post_type'      => Post_Type::instance()->get_slug(),
		// 	'posts_per_page' => -1,
		// 	'meta_key'       => '_uf_hash',
		// 	'meta_value'     => $needed,
		// 	'meta_compare'   => 'IN'
		// ));
		//
		// foreach( $containers as $post ) {
		// 	# Get data about the container
		// 	$container = new Container_Helper;
		// 	$container->import_from_post( $post );
		//
		// 	echo '<script type="text/json" id="uf-ui-container-' . $post->_uf_hash . '">';
		// 		echo json_encode( array(
		// 			'id'     => $post->_uf_hash,
		// 			'fields' => $container->prop( 'fields' )
		// 		));
		// 	echo '</script>';
		//
		// 	$done[] = $post->_uf_hash;
		// }

		$registered = Core_Container::get_registered();
		foreach( $needed as $hash ) {
			if( ! isset( $registered[ $hash ] ) ) {
				continue;
			}

			# Use an empty datastore to force default values
			$registered[ $hash ]->set_datastore( new Values_Datastore );

			$arr = array(
				'id'     => $hash,
				'fields' => $registered[ $hash ]->export_fields_settings(),
				'data'   => $registered[ $hash ]->export_data()
			);

			echo '<script type="text/json" id="uf-ui-container-' . $hash . '">';
				echo json_encode( $arr );
			echo '</script>';

			$done[] = $hash;
		}
	}
}
