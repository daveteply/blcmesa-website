<?php
/**
 * Contact Form Submission Handler & Spam Shield
 * Bethlehem Lutheran Church (blcmesa.org)
 *
 * Provides sanitized server-side processing, CSRF protection,
 * non-spoofable recipient routing, honeypot spam detection, and local dev mocking.
 *
 * @package blcmesa
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Optional SMTP Support for Local Development / Testing.
 * If SMTP_USER and SMTP_PASS are set in .env, configure PHPMailer to send real mail.
 */
function blcmesa_configure_dev_smtp( $phpmailer ) {
    $smtp_user = getenv( 'SMTP_USER' ) ?: ( defined( 'SMTP_USER' ) ? SMTP_USER : '' );
    $smtp_pass = getenv( 'SMTP_PASS' ) ?: ( defined( 'SMTP_PASS' ) ? SMTP_PASS : '' );

    if ( ! empty( $smtp_user ) && ! empty( $smtp_pass ) ) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = getenv( 'SMTP_HOST' ) ?: 'smtp.gmail.com';
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = (int) ( getenv( 'SMTP_PORT' ) ?: 587 );
        $phpmailer->SMTPSecure = getenv( 'SMTP_SECURE' ) ?: 'tls';
        $phpmailer->Username   = $smtp_user;
        $phpmailer->Password   = $smtp_pass;
        $phpmailer->From       = $smtp_user;
        $phpmailer->FromName   = 'BLC Website';
    }
}
add_action( 'phpmailer_init', 'blcmesa_configure_dev_smtp' );

/**
 * Returns the server-side whitelist mapping recipient keys to church mailboxes.
 * Real email addresses are defined strictly here and are NEVER exposed to client-side markup.
 *
 * @return array<string, array{title: string, email: string}>
 */
function blcmesa_get_contact_recipient_map() {
    return array(
        'office'   => array(
            'title' => 'Church Office & General Inquiries',
            'email' => 'office@blcmesa.org',
        ),
        'pastor'   => array(
            'title' => 'Pastor & Pastoral Care',
            'email' => 'pastor@blcmesa.org',
        ),
        'noahsark' => array(
            'title' => "Noah's Ark Preschool",
            'email' => 'director@noahsarkblc.org',
        ),
        'music'    => array(
            'title' => 'Music Ministry',
            'email' => 'music@blcmesa.org',
        ),
        'care'     => array(
            'title' => 'Fellowship & Care / Stephen Ministry',
            'email' => 'care@blcmesa.org',
        ),
        'nurse'    => array(
            'title' => 'Faith Community Nurse',
            'email' => 'nurse@blcmesa.org',
        ),
        'youth'    => array(
            'title' => 'Children & Youth Ministries',
            'email' => 'youth@blcmesa.org',
        ),
        'outreach' => array(
            'title' => 'Community Outreach & I-HELP',
            'email' => 'outreach@blcmesa.org',
        ),
    );
}

/**
 * Processes contact form submissions with full sanitization and local testing support.
 */
function blcmesa_handle_contact_submission() {
    $is_ajax = wp_doing_ajax();

    // 1. Verify CSRF Nonce
    $nonce = $_POST['blc_nonce'] ?? $_POST['security'] ?? '';
    if ( ! wp_verify_nonce( $nonce, 'blc_contact_nonce' ) ) {
        $error_msg = 'Security check failed. Please refresh the page and try again.';
        if ( $is_ajax ) {
            wp_send_json_error( array( 'message' => $error_msg ), 403 );
        } else {
            wp_die( esc_html( $error_msg ) );
        }
    }

    // 2. Anti-Spam Honeypot Verification
    if ( ! empty( $_POST['blc_hp_check'] ) ) {
        // Honeypot filled: silently pretend success to discard bot submissions
        if ( $is_ajax ) {
            wp_send_json_success( array( 'message' => 'Thank you for reaching out!' ) );
        } else {
            wp_safe_redirect( home_url( '/contact/?status=sent' ) );
            exit;
        }
    }

    // 3. Input Sanitization
    $name      = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
    $email     = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $phone     = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
    $recipient = sanitize_key( wp_unslash( $_POST['recipient'] ?? 'office' ) );
    $subject   = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
    $message   = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

    // 4. Input Validation
    if ( empty( $name ) ) {
        $error = 'Please provide your name.';
    } elseif ( empty( $email ) || ! is_email( $email ) ) {
        $error = 'Please provide a valid email address.';
    } elseif ( empty( $message ) ) {
        $error = 'Please write a message before submitting.';
    } else {
        $error = '';
    }

    if ( ! empty( $error ) ) {
        if ( $is_ajax ) {
            wp_send_json_error( array( 'message' => $error ), 400 );
        } else {
            wp_die( esc_html( $error ) );
        }
    }

    // 5. Strict Recipient Whitelist Lookup
    $recipient_map = blcmesa_get_contact_recipient_map();
    if ( ! isset( $recipient_map[ $recipient ] ) ) {
        $recipient = 'office'; // Default fallback if key was manipulated
    }

    $dest_title = $recipient_map[ $recipient ]['title'];
    $dest_email = $recipient_map[ $recipient ]['email'];

    // 6. Build Email Subject & Render HTML Template
    $mail_subject = ! empty( $subject )
        ? "[BLC Website] {$subject} ({$dest_title})"
        : "[BLC Website Inquiry] Message for {$dest_title}";

    // Base64 encode Bethlehem Star logo for self-contained email branding
    $logo_path = get_template_directory() . '/assets/images/bethlehem-star.png';
    $logo_data_uri = '';
    if ( file_exists( $logo_path ) ) {
        $logo_data = base64_encode( (string) file_get_contents( $logo_path ) );
        $logo_data_uri = 'data:image/png;base64,' . $logo_data;
    }

    // Set template variables
    $sender_name     = $name;
    $sender_email    = $email;
    $sender_phone    = $phone;
    $recipient_title = $dest_title;
    $message_body    = $message;
    $submitted_date  = current_time( 'F j, Y, g:i a' );

    // Render modular email template
    $template_file = get_template_directory() . '/templates/emails/contact-notification.php';
    if ( file_exists( $template_file ) ) {
        ob_start();
        include $template_file;
        $mail_body = ob_get_clean();
    } else {
        $mail_body = nl2br( esc_html( $message ) );
    }

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        "From: Bethlehem Lutheran Church <office@blcmesa.org>",
        "Reply-To: {$name} <{$email}>",
    );

    // 7. Local Testing & Mocking Mode Detection
    $test_override_email = getenv( 'BLC_TEST_EMAIL' ) ?: ( defined( 'BLC_TEST_EMAIL' ) ? BLC_TEST_EMAIL : '' );
    $is_local_env = ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'local' )
        || ( isset( $_SERVER['HTTP_HOST'] ) && ( str_contains( $_SERVER['HTTP_HOST'], 'localhost' ) || str_contains( $_SERVER['HTTP_HOST'], '127.0.0.1' ) ) )
        || ( defined( 'WP_DEBUG' ) && WP_DEBUG );

    $sent = false;
    $log_notice = '';

    // Always prepare the structured mock payload for browser console.info inspection
    $mock_email = array(
        'to'          => "{$dest_title} <{$dest_email}>",
        'from'        => "{$name} <{$email}>",
        'phone'       => $phone ?: '(None)',
        'subject'     => $mail_subject,
        'message'     => $message,
        'submittedAt' => $submitted_date,
    );

    if ( ! empty( $test_override_email ) && is_email( $test_override_email ) ) {
        // Direct all outgoing test emails to the configured test mailbox
        $test_subject = "[DEV TEST - Intended for: {$dest_email}] " . $mail_subject;
        $mock_email['testOverride'] = $test_override_email;

        // Catch any PHPMailer / wp_mail errors gracefully
        $mail_error = '';
        $mail_failed_handler = function( $wp_error ) use ( &$mail_error ) {
            if ( is_wp_error( $wp_error ) ) {
                $mail_error = $wp_error->get_error_message();
            }
        };
        add_action( 'wp_mail_failed', $mail_failed_handler );

        $sent = wp_mail( $test_override_email, $test_subject, $mail_body, $headers );
        remove_action( 'wp_mail_failed', $mail_failed_handler );

        if ( $sent ) {
            $log_notice = "Sent to test override mailbox: {$test_override_email}";
        } elseif ( $is_local_env ) {
            // Local environment has no outbound mailer daemon configured
            $sent = true;
            $log_notice = 'Local Docker has no SMTP server configured. Form output logged to browser console.info instead.';
            $mock_email['mailError'] = $mail_error ?: 'No local mail transport configured in Docker container';
        }
    } elseif ( $is_local_env ) {
        // Local testing environment: mock email and provide payload for browser console.info
        $sent = true;
        $log_notice = 'Local Test Mode: Submission logged to browser console (no external email sent).';
    } else {
        // Production: send through standard WordPress mailer / SMTP
        $sent = wp_mail( $dest_email, $mail_subject, $mail_body, $headers );
    }

    if ( $sent ) {
        $success_msg = 'Thank you for reaching out to Bethlehem Lutheran Church! Your message has been received.';
        if ( ! empty( $log_notice ) ) {
            $success_msg .= ' (' . $log_notice . ')';
        }

        if ( $is_ajax ) {
            wp_send_json_success( array(
                'message'    => $success_msg,
                'recipient'  => $dest_title,
                'testNotice' => $log_notice,
                'mockEmail'  => $mock_email,
            ) );
        } else {
            wp_safe_redirect( home_url( '/contact/?status=sent' ) );
            exit;
        }
    } else {
        $fail_msg = 'There was a temporary problem sending your message. Please call the church office at (480) 832-0462.';
        if ( $is_ajax ) {
            wp_send_json_error( array( 'message' => $fail_msg ) );
        } else {
            wp_die( esc_html( $fail_msg ) );
        }
    }
}

// Hook into WordPress AJAX and standard admin-post actions for both logged-in and guest visitors
add_action( 'wp_ajax_blc_submit_contact', 'blcmesa_handle_contact_submission' );
add_action( 'wp_ajax_nopriv_blc_submit_contact', 'blcmesa_handle_contact_submission' );
add_action( 'admin_post_blc_submit_contact', 'blcmesa_handle_contact_submission' );
add_action( 'admin_post_nopriv_blc_submit_contact', 'blcmesa_handle_contact_submission' );
