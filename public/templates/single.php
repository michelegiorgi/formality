<?php
/**
 * The template for displaying single formality form
 */
?>
<!DOCTYPE html>
<html class="html-formality" <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class("body-formality body-formality--side"); ?>> 
    <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
    <?php wp_footer(); ?>
  </body>
</html>