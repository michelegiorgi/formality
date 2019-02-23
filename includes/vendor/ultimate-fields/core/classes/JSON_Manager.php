<?php
namespace Ultimate_Fields;

/**
 * Manages the loading, saving and listing of JSON containers.
 */
class JSON_Manager {
	/**
	 * Holds the path of the JSON directory.
	 *
	 * @since 3.0
	 * @var string
	 */
	protected $path;

	/**
	 * Indicates if the JSON directory exists.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $directory_exists = true;

	/**
	 * Indicates if the JSON directory is writeable.
	 *
	 * @since 3.0
	 * @var bool
	 */
	protected $directory_writable = false;

	/**
	 * Holds metadata about container JSONs.
	 *
	 * @since 3.0
	 * @var mixed[]
	 */
	protected $container_meta = array();

	/**
	 * Returns an instance of the manager.
	 *
	 * @since 3.0
	 * @return Ultimate_Fields\JSON
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initializes everything that is needed.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		/**
		 * Allows the path for UF to store JSON to be modified.
		 *
		 * @since 3.0
		 *
		 * @param string $path The default path for JSON.
		 * @return string
		 */
		$this->path = apply_filters( 'uf.json_path', get_stylesheet_directory() . '/uf-json' );

		# Check if the directory exists.
		if( ! file_exists( $this->path ) || ! is_dir( $this->path ) ) {
			$this->directory_exists = false;
			return;
		}

		# Check if the directory is writeable
		$this->directory_writable = is_readable( $this->path ) && is_writeable( $this->path );

		# Ensure the path has a trailing slash
		$this->path = trailingslashit( $this->path );
	}

	public function is_enabled() {
		if( defined( 'ULTIMATE_FIELDS_DISABLE_JSON' ) && ULTIMATE_FIELDS_DISABLE_JSON ) {
			return false;
		}

		return $this->directory_exists;
	}

	/**
	 * Indicates if the JSON directory is write-able.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function is_writeable() {
		return $this->directory_writable;
	}

	/**
	 * Reads containers when they are needed.
	 *
	 * @since 3.0
	 */
	protected function read() {
		static $read;

		if( ! $this->directory_writable ) {
			return;
		}

		if( ! is_null( $read ) ) {
			return;
		}

		$dir = opendir( $this->path );
		while( $file = readdir( $dir ) ) {
			if( ! preg_match( '~\.json$~', $file ) )
				continue;

			$json = file_get_contents( $this->path . $file );
			$data = json_decode( $json, true );

			if( ! $data || ! $data = array_shift( $data ) ) {
				trigger_error( $this->path . $file . " contains invalid JSON!" );
				continue;
			}

			$data[ 'path' ] = $this->path . $file;
			$this->container_meta[ $data[ 'id' ] ] = $data;
		}

		$read = true;
	}

	/**
	 * Returns the path that is used for JSON synchronization.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Returns all local containers.
	 *
	 * @since 3.0
	 *
	 * @return mixed[]
	 */
	public function get_containers() {
		$this->read();

		return $this->container_meta;
	}

	/**
	 * Checks the current JSON state for a container.
	 *
	 * @since 3.0
	 *
	 * @param string $hash The hash of the container.
	 * @return mixed[]
	 */
	public function get_container_json_state( $hash ) {
		$state = array();

		if( isset( $this->container_meta[ $hash ] ) ) {
			return $this->container_meta[ $hash ];
		} else {
			return false;
		}
	}
}
