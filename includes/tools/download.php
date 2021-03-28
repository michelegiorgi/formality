<?php
/*
 * Formality upload reader script
 * Protect Formality uploads with login
 *
 * Based on hakre script <http://hakre.wordpress.com/>
 * http://wordpress.stackexchange.com/questions/37144/protect-wordpress-uploads-if-user-is-not-logged-in
 * 
 * @link       https://formality.dev
 * @since      1.3.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
 * @license GPL-3.0+
 */

$wpload = ( isset($_GET['wproot']) ? $_GET['wproot'] : '' ) . '/wp-load.php';
if(file_exists($wpload)) {
  require_once($wpload);
} else {
  die('404 &#8212; WP not found.');
}

is_user_logged_in() || auth_redirect();

if(!current_user_can('manage_options')) {
  status_header(401);
  die('401 &#8212; Unauthorized');
}

$filename = isset($_GET['file']) ? $_GET['file'] :'';
$file = path_join(__DIR__, $filename); 

if(!is_file($file)) {
  status_header(404);
  die('404 &#8212; File not found.');
}

$mime = wp_check_filetype($file);
if(false === $mime['type'] && function_exists( 'mime_content_type')) { $mime['type'] = mime_content_type($file); }

$mimetype = $mime['type'] ? $mime['type'] : 'image/' . substr($file, strrpos($file, '.') + 1);

header('Content-Type: ' . $mimetype);
if(false === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS')) { header('Content-Length: ' . filesize($file)); }

$last_modified = gmdate('D, d M Y H:i:s', filemtime($file));
$etag = '"' . md5( $last_modified ) . '"';
header("Last-Modified: $last_modified GMT");
header('ETag: ' . $etag);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 100000000 ) . ' GMT');

$client_etag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false;

if(!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) { $_SERVER['HTTP_IF_MODIFIED_SINCE'] = false; }

$client_last_modified = trim($_SERVER['HTTP_IF_MODIFIED_SINCE']);
$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;
$modified_timestamp = strtotime($last_modified);

if(($client_last_modified && $client_etag)
  ? (($client_modified_timestamp >= $modified_timestamp) && ($client_etag == $etag))
  : (($client_modified_timestamp >= $modified_timestamp) || ($client_etag == $etag))) {
  status_header( 304);
  exit;
}

readfile($file);