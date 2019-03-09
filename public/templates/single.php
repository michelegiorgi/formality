<?php
/**
 * The template for displaying single formality form
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class("body-formality"); ?>> 
<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
<div class="formality__bg"></div>
<?php wp_footer(); ?>
</body>
</html>