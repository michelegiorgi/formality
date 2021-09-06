<?php
/**
 * Formality email notification template
 * You can override this by putting a formality-notification.php file inside your active theme's directory.
 * Don't forget to use basic html tags and inline css rules.
 *
 * Some inherited variables that you can easily use:
 *   $title - Your form title
 *   $content - Html table with all form data
 *   $result_link - Direct link to this result in your admin dashboard
 *   $results_link - Direct link to all results for this form
 *
 * @link       https://formality.dev
 * @since      1.3.4
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 * @license GPL-3.0+
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Formality email template</title>
  </head>
  <body>
    <div style="font-family: sans-serif;">
      <?php _e("New result for", "formality"); ?>
      <h2 style="margin: 0; font-family: sans-serif;"><?php echo $title; ?></h2>
      <br><br>
      <?php echo $content; ?>
      <br><br>
      <a style="font-family: sans-serif;" href="<?php echo $result_link; ?>"><?php _e('View this result', 'formality'); ?></a>
      <?php _e('in your admin dashboard', 'formality'); ?>
      <br>
      <a style="font-family: sans-serif;" href="<?php echo $results_link; ?>"><?php _e('View all results', 'formality'); ?></a>
      <?php _e('for', 'formality'); ?> <?php echo $title; ?>
      <br><br>
      <?php _e("Made with", "formality"); ?> <a href="https://formality.dev" style="color:inherit; font-family: sans-serif"><strong>Formality</strong></a>
    </div>
  </body>
</html>
