<?php
/**
 * Formality standalone form template
 * You can override this by putting a formality-form.php file inside your active theme's directory.
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 * @license GPL-3.0+
 */
?>
<!DOCTYPE html>
<html class="html-formality" <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>
    <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
    <?php wp_footer(); ?>
  </body>
</html>
