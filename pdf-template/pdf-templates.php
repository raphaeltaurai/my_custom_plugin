<?php
/**
 * Template Name: pdf-templates
 * Description: External HTML + CSS PDF template
 * Version: 1.0
 * Author: Raphael Shawn Taurai
 * Group: Legacy
 * Required PDF Version: 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GFForms' ) ) {
	return;
}

// Get form data
$event_name      = rgar($entry, '3');
$registrant_name = rgar($entry, '1.3') . ' ' . rgar($entry, '1.6');
$attendees       = rgar($entry, '7');
$addons          = rgar($entry, '8');
$addons_display  = is_array($addons) ? implode(', ', $addons) : $addons;

// Load CSS
$css_path = __DIR__ . '/style.css';
$css = file_exists($css_path) ? file_get_contents($css_path) : '';

// Load HTML
$html_path = __DIR__ . '/layout.html';
$html = file_exists($html_path) ? file_get_contents($html_path) : '<p>HTML file not found.</p>';

// Replace placeholders
$html = str_replace('{{event_name}}', esc_html($event_name), $html);
$html = str_replace('{{registrant}}', esc_html($registrant_name), $html);
$html = str_replace('{{attendees}}', esc_html($attendees), $html);
$html = str_replace('{{addons}}', esc_html($addons_display), $html);
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		<?php echo $css; ?>
	</style>
</head>
<body>
<?php echo $html; ?>
</body>
</html>