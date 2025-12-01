<?php
/**
 * Plugin Name: Simple Contact Form
 * Description: A basic multi-field contact form plugin using WordPress Shortcode and Hooks.
 * Version: 1.0.2
 * Author: Your Name
 * License: GPL2
 * Text Domain: simple-contact-form
 */

// Exit if accessed directly (security)
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 0. ENQUEUE STYLES
 * Loads the external CSS file for the form.
 */
function scf_enqueue_styles() {
    // Only load the CSS file if the shortcode is present on the page (optimization)
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'contact_form' ) ) {
        // --- PATH CORRECTED HERE ---
        wp_enqueue_style(
            'scf-style',
            plugins_url( 'contactform.css', __FILE__ ), // Reference to assets/contactform.css
            array(),
            '1.0.2'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'scf_enqueue_styles' );


/**
 * 1. REGISTER THE SHORTCODE
 * Displays the HTML form when the shortcode is used.
 */
function scf_display_contact_form() {
    // Check if the form was just submitted successfully
    $success_message = '';
    if ( isset( $_GET['scf_status'] ) && 'success' === $_GET['scf_status'] ) {
        $success_message = '<p class="scf-success-message">Thank you! Your message has been sent.</p>';
    }

    // Define the form HTML (now clean, with classes instead of inline styles)
    $form_html = $success_message;
    $form_html .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" class="scf-form">';

    // --- Security Nonce Field ---
    $form_html .= wp_nonce_field( 'scf_contact_form_nonce', 'scf_nonce_field', true, false );

    // Hidden field tells WordPress which hook to trigger in the handler function
    $form_html .= '<input type="hidden" name="action" value="scf_handle_form_submission">';

    // --- Form Fields ---

    // Name Field
    $form_html .= '<div class="scf-field-group">';
    $form_html .= '<label for="scf_name" class="scf-label">Your Name:</label>';
    $form_html .= '<input type="text" id="scf_name" name="scf_name" required class="scf-input">';
    $form_html .= '</div>';

    // Email Field
    $form_html .= '<div class="scf-field-group">';
    $form_html .= '<label for="scf_email" class="scf-label">Your Email:</label>';
    $form_html .= '<input type="email" id="scf_email" name="scf_email" required class="scf-input">';
    $form_html .= '</div>';

    // Subject Field
    $form_html .= '<div class="scf-field-group">';
    $form_html .= '<label for="scf_subject" class="scf-label">Subject:</label>';
    $form_html .= '<input type="text" id="scf_subject" name="scf_subject" required class="scf-input">';
    $form_html .= '</div>';

    // Message Field
    $form_html .= '<div class="scf-field-group">';
    $form_html .= '<label for="scf_message" class="scf-label">Message:</label>';
    $form_html .= '<textarea id="scf_message" name="scf_message" rows="6" required class="scf-textarea"></textarea>';
    $form_html .= '</div>';

    // Submit Button
    $form_html .= '<button type="submit" class="scf-submit-button">Send Message</button>';

    $form_html .= '</form>';

    // Return the form HTML to be displayed by the shortcode
    return $form_html;
}
add_shortcode( 'simple_contact_form', 'scf_display_contact_form' );


/**
 * 2. HANDLE FORM SUBMISSION
 * This function is hooked to the 'admin_post_nopriv_scf_handle_form_submission'
 * and 'admin_post_scf_handle_form_submission' actions.
 */
function scf_handle_form_submission() {
    // 1. NONCE and SECURITY CHECK
    // Check if the nonce field is present and valid
    if (
        ! isset( $_POST['scf_nonce_field'] ) ||
        ! wp_verify_nonce( $_POST['scf_nonce_field'], 'scf_contact_form_nonce' )
    ) {
        wp_die( 'Security check failed. Please return to the form and try again.' );
    }

    // 2. SANITIZE AND VALIDATE INPUT
    $name    = isset( $_POST['scf_name'] )    ? sanitize_text_field( $_POST['scf_name'] ) : '';
    $email   = isset( $_POST['scf_email'] )   ? sanitize_email( $_POST['scf_email'] )    : '';
    $subject = isset( $_POST['scf_subject'] ) ? sanitize_text_field( $_POST['scf_subject'] ) : 'New Contact Form Submission';
    $message = isset( $_POST['scf_message'] ) ? sanitize_textarea_field( $_POST['scf_message'] ) : '';

    // Simple validation
    if ( empty( $name ) || ! is_email( $email ) || empty( $message ) ) {
        // Redirect back to the page with an error status
        wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
        exit;
    }

    // 3. COMPILE AND SEND EMAIL

    // To: the specified email address
    // --- TARGET EMAIL ADDRESS UPDATED HERE ---
    $to = 'shawntaurai16@gmail.com';

    // Body of the email
    $email_body = "You have received a new contact form submission:\n\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Subject: " . $subject . "\n";
    $email_body .= "Message:\n" . $message . "\n";

    // Headers (to ensure replies go to the user's email)
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: {$name} <{$email}>"
    );

    // Send the email
    $mail_sent = wp_mail( $to, $subject, $email_body, $headers );

    // 4. REDIRECT USER

    // Determine the redirect URL (use the page the user was on)
    $redirect_url = esc_url_raw( $_SERVER['HTTP_REFERER'] );

    if ( $mail_sent ) {
        // Append success status to the URL query string
        $redirect_url = add_query_arg( 'scf_status', 'success', $redirect_url );
    } else {
        // Optional: Append failure status
        $redirect_url = add_query_arg( 'scf_status', 'error', $redirect_url );
    }

    // Redirect the user back to the form page
    wp_safe_redirect( $redirect_url );
    exit;
}

// Hook the handler function to process the form submission
// 'admin_post_nopriv_ACTION_NAME' handles submissions from non-logged-in users
add_action( 'admin_post_nopriv_scf_handle_form_submission', 'scf_handle_form_submission' );
// 'admin_post_ACTION_NAME' handles submissions from logged-in users

add_action( 'admin_post_scf_handle_form_submission', 'scf_handle_form_submission' );
