<?php
/**
 * The template for displaying single formality form
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
