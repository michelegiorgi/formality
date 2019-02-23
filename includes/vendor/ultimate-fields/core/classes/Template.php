<?php
namespace Ultimate_Fields;

/**
 * This class is responsible for displaying all required template in the (admin's) footer.
 *
 * There are many JS templates that are required throughout the plugin. Those templates
 * should only be loaded when they are needed and this class will collect them and display
 * them in the admin footer, when every class/etc. should be already loaded.
 *
 * @since 3.0
 */
class Template {
	/**
	 * Holds all possible paths to templates.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $paths = array();

	/**
	 * Holds all the templates that will be displayed later in pairs id => template.
	 *
	 * @since 3.0
	 * @var string[]
	 */
	protected $templates = array();

	/**
	 * Returns an instance of the class.
	 *
	 * @since 3.0
	 *
	 * @return Template
	 */
	public static function instance() {
		static $instance;

		if( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Adds a script by path.
	 *
	 * Adds a template by reading it from a file.
	 *
	 * @param string $id The ID of the template.
	 * @param string $path The path to the file.
	 */
	public static function add( $id, $path ) {
		return self::instance()->add_template_by_path( $id, $path );
	}

	/**
	 * Adds a hook for output in the admin footer.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		if( is_admin() ) {
			add_action( 'admin_footer', array( $this, 'output_templates' ) );
		} else {
			add_action( 'wp_footer', array( $this, 'output_templates' ) );
			add_action( 'login_footer', array( $this, 'output_templates' ) );
		}

		add_action( 'customize_controls_print_footer_scripts', array( $this, 'output_templates' ), 1000 );

		$this->paths[] = ULTIMATE_FIELDS_DIR . 'templates/';

		/**
		 * Allows additional paths for templates to be added.
		 *
		 * @since 3.0
		 *
		 * @param strings $path The full paths to the templates directory.
		 * @return string[]
		 */
		$this->paths = apply_filters( 'uf.template.paths', $this->paths, $this );
	}

	/**
	 * Outputs the templates in the admin footer.
	 *
	 * @since 3.0
	 */
	public function output_templates() {
		static $shown;

		if( is_null( $shown ) ) {
			$shown = array();
		}

		/**
		 * Allows adding/modifying templates before displaying them in the footer.
		 *
		 * @since 3.0
		 *
		 * @param string[] $templates The templates which will be shown.
		 */
		$templates = apply_filters( 'uf.templates', $this->templates );

		foreach( $templates as $id => $template ) {
			if( isset( $shown[ $id ] ) ) {
				continue;
			}

			printf(
				'<script type="text/html" id="ultimate-fields-%s">%s</script>',
				esc_attr( $id ),
				$template
			);

			$shown[ $id ] = true;
		}
	}

	/**
	 * Adds (replaces) a template.
	 *
	 * @since 3.0
	 *
	 * @param string $id The ID of the template.
	 * @param string $template The source of the template.
	 * @return UF_Themes The class, to allow chaining.
	 */
	public function add_template( $id, $template ) {
		$this->templates[ $id ] = $template;

		return $this;
	}

	/**
	 * Locates a template.
	 *
	 * @since 3.0
	 *
	 * @param string $template The name/path of the template.
	 * @return mixed A string path or a boolean false.
	 */
	public function locate( $template ) {
		if( ! preg_match( '~\.[a-z0-5]+$~i', $template ) ) {
			$template .= '.php';
		}

		foreach( $this->paths as $dir ) {
			if( file_exists( $dir . $template ) ) {
				return $dir . $template;
			}
		}

		wp_die( "$template does not exist!" );
	}


	/**
	 * Adds a template by reading it from a file.
	 *
	 * @param string $id The ID of the template.
	 * @param string $path The path to the file.
	 * @return UF_Themes The object, to allow chaining.
	 */
	public function add_template_by_path( $id, $path ) {
		# If the template is already in place, we're done.
		if( isset( $this->templates[ $id ] ) ) {
			return $this;
		}

		$full_path = $this->locate( $path );

		ob_start();

		# ToDo: Add propert themes/template inheritance
		include( $full_path );
		$this->add_template( $id, ob_get_clean() );

		return $this;
	}

	/**
	 * Removes a template when necessary.
	 *
	 * @since 3.0
	 *
	 * @param string $id The id of the template.
	 */
	public function remove_template( $id ) {
		if( isset( $this->templates[ 'id' ] ) ) {
			unset( $this->templates[ 'id' ] );
		}
	}

	/**
	 * Includes a template with a given context.
	 *
	 * @since 3.0
	 *
	 * @param string  $template The path to the template (no extension).
	 * @param mixed[] $args The arguments to be used as locals within the template.
	 * @param bool    $echo Wether to output the template or not.
	 */
	public function include_template( $template, $context = array(), $output = true ) {
		extract( $context );

		$path = $this->locate( $template );

		ob_start();
		include( $path );
		$result = ob_get_clean();

		if( $output ) {
			echo $result;
		}

		return $result;
	}

	/**
	 * Adds an additional folder to look for templates in.
	 *
	 * @since 3.0
	 *
	 * @param string $path The path to the directory.
	 */
	public function add_path( $path ) {
		array_unshift( $this->paths, $path );
	}
}
