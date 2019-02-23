<?php
echo "<?php\n" ?>
/**
 * Plugin name: Ultimate Fields Export <?php echo date_i18n( get_option( 'date_format' ) ) ?>
 
 * Description: An export from Ultimate Fields. You can use this file as a plugin or copy its content to your theme, either directly to you functions.php file or a file that's included by it.
 *
 * Fore more information, please check
 * @link https://www.ultimate-fields.com/docs/
 */
<?php $before->callback() ?>
add_action( 'uf.init', '<?php echo $function_name ?>' );
function <?php echo $function_name ?>() {
	<?php
	$i = 0;
	foreach( $data as $container ) {
		$dump = new Ultimate_Fields\UI\Dump_Beautifier( $container );
		$dump->indent( 1 );
		if( $textdomain ) {
			$dump->add_textdomain( $textdomain );
		}

		echo 'Ultimate_Fields\Container::create_from_array( ' . $dump . ' );';

		$i++;

		if( $i < count( $data ) ) {
			echo "\n\n\t";
		}
	}

	echo "\n";
	?>
}
<?php $after->callback() ?>