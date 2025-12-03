<?php
/**
    * Template Name: Event Registration Form
    * Version: 1.2
    * Description: A simple event ticket template with QR code for Gravity PDF.
    * Author: Raphael S Taurai
    * Author URI: https://gravitypdf.com
    * Group: Core
    * License: GPLv2
    * Required PDF Version: 4.0
    * Tags: Header, Footer, Background, Optional HTML Fields, Optional Page Fields, Container Background Color
    */

/**
 * Event Ticket PDF Template for Gravity PDF
 *
 * Copy this folder into your Gravity PDF custom templates directory or zip and upload.
 * Edit the field ID mappings below to match your form fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Gravity PDF v6 passes template data via $this->get_data(). Use it when available
if ( isset( $this ) && method_exists( $this, 'get_data' ) ) {
    $data = $this->get_data();
    $form = isset( $data['form'] ) ? $data['form'] : [];
    $entry = isset( $data['entry'] ) ? $data['entry'] : [];
    $settings = isset( $data['settings'] ) ? $data['settings'] : [];
} else {
    // Backwards compatible fallback: expect $form and $entry to be available in scope
    $data = null;
    if ( ! isset( $form ) ) {
        $form = [];
    }
    if ( ! isset( $entry ) ) {
        $entry = [];
    }
}

/* ----------------------------
 *  FIELD MAPPINGS (edit me)
 * ----------------------------
 * Set these to the field IDs used in your Gravity Form.
 * Example: $field_name = 3; // where field ID 3 is the registrant name field
 */
$field_event   = 2; // Event name field ID
$field_name    = 1; // Registrant's name field ID
$field_qty     = 3; // Number of attendees field ID
$field_addons  = 4; // Add-ons / extras field ID (can be multi-line or checkbox text)

/* ----------------------------
 *  Helpers
 * ----------------------------*/
function _get_entry_value( $entry, $id ) {
    if ( function_exists( 'rgar' ) ) {
        return rgar( $entry, (string) $id );
    }

    return isset( $entry[ $id ] ) ? $entry[ $id ] : '';
}

$event  = _get_entry_value( $entry, $field_event );
$name   = _get_entry_value( $entry, $field_name );
$qty    = _get_entry_value( $entry, $field_qty );
$addons = _get_entry_value( $entry, $field_addons );

// Entry ID for QR linking
$entry_id = _get_entry_value( $entry, 'id' );

// Build entry URL for QR code. Edit if your site uses a different entry view URL.
if ( function_exists( 'site_url' ) ) {
    $site = site_url();
} else {
    $site = ( isset( $_SERVER['HTTP_HOST'] ) ? ( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] ) : '' );
}

$entry_url = $site . '/?gf_entry=' . $entry_id;

// QR generator (Google Chart API) - optional; can be replaced with server-side QR generator
$qr_src = 'https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=' . rawurlencode( $entry_url );

// Logo path. We prefer to use a repo-level `assets/logo.svg` file (one level up),
// which is outside the `pdf-template` folder. mPDF accepts filesystem paths for images,
// so use the filesystem path when available. If not found, fall back to template assets.
$logo_url = '';
if ( isset( $this ) && method_exists( $this, 'get_template_url' ) ) {
    // Keep the original template asset URL if Gravity PDF can serve it
    $logo_url = $this->get_template_url( 'assets/logo.svg' );
}

$repo_level_logo = __DIR__ . '/../assets/logo.svg';
if ( file_exists( $repo_level_logo ) ) {
    // Use filesystem path for mPDF if available
    $logo_url = $repo_level_logo;
} else {
    // Fallback to original location inside the template folder
    $template_logo = __DIR__ . '/assets/logo.svg';
    if ( file_exists( $template_logo ) ) {
        $logo_url = $template_logo;
    }
}


// Load HTML template and inject variables
$html_path = __DIR__ . '/template.html';
$html = file_exists($html_path) ? file_get_contents($html_path) : '';

$replacements = [
    '{{logo_url}}'      => esc_attr($logo_url),
    '{{event_title}}'   => esc_html($event ? $event : (isset($form['title']) ? $form['title'] : 'Event')), 
    '{{name}}'          => esc_html($name),
    '{{qty}}'           => esc_html($qty),
    '{{addons}}'        => nl2br(esc_html($addons)),
    '{{qr_src}}'        => esc_attr($qr_src),
    '{{entry_id}}'      => esc_html($entry_id),
    '{{generated_date}}'=> date('Y-m-d'),
];

$html = strtr($html, $replacements);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Event Ticket</title>
        <link rel="stylesheet" href="<?php echo esc_attr( $this->get_template_url( 'template.css' ) ); ?>">
    </head>
    <body>
        <?php echo $html; ?>
    </body>
</html>
