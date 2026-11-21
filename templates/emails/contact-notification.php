<?php
/**
 * HTML Email Notification Template for BLC Contact Form
 *
 * Available variables:
 * - $sender_name: string
 * - $sender_email: string
 * - $sender_phone: string
 * - $recipient_title: string
 * - $subject: string
 * - $message_body: string
 * - $submitted_date: string
 * - $logo_data_uri: string (Base64 data URI of Bethlehem star logo)
 *
 * @package blcmesa
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo esc_html( $subject ); ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1E293B; -webkit-font-smoothing: antialiased;">
  <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; padding: 24px 12px;">
    <tr>
      <td align="center">
        <!-- Main Email Container -->
        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E2E8F0;">
          
          <!-- Header Banner -->
          <tr>
            <td style="background-color: #0C1017; padding: 24px 28px; text-align: left; border-bottom: 3px solid #D49B28;">
              <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <?php if ( ! empty( $logo_data_uri ) ) : ?>
                  <td style="padding-right: 14px; vertical-align: middle;">
                    <img src="<?php echo esc_attr( $logo_data_uri ); ?>" alt="Bethlehem Star" width="38" height="38" style="display: block; border: 0;">
                  </td>
                  <?php endif; ?>
                  <td style="vertical-align: middle;">
                    <span style="display: block; color: #FFFFFF; font-size: 16px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">Bethlehem</span>
                    <span style="display: block; color: #D49B28; font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase;">Lutheran Church</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Notification Title -->
          <tr>
            <td style="padding: 28px 28px 12px 28px;">
              <span style="display: inline-block; background-color: rgba(212, 155, 40, 0.12); color: #B47814; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em;">
                Website Contact Form
              </span>
              <h1 style="margin: 12px 0 6px 0; font-size: 20px; font-weight: 700; color: #0C1017; line-height: 1.3;">
                New Message for <?php echo esc_html( $recipient_title ); ?>
              </h1>
              <p style="margin: 0; font-size: 13px; color: #64748B;">
                Received on <?php echo esc_html( $submitted_date ); ?>
              </p>
            </td>
          </tr>

          <!-- Contact Details Table -->
          <tr>
            <td style="padding: 12px 28px;">
              <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 14px 18px;">
                <tr>
                  <td style="padding: 6px 0; font-size: 13px; color: #64748B; width: 110px;"><strong>From:</strong></td>
                  <td style="padding: 6px 0; font-size: 14px; color: #0F172A; font-weight: 600;"><?php echo esc_html( $sender_name ); ?></td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; font-size: 13px; color: #64748B;"><strong>Email:</strong></td>
                  <td style="padding: 6px 0; font-size: 14px; color: #0F172A;">
                    <a href="mailto:<?php echo esc_attr( $sender_email ); ?>" style="color: #2563EB; text-decoration: none;"><?php echo esc_html( $sender_email ); ?></a>
                  </td>
                </tr>
                <?php if ( ! empty( $sender_phone ) ) : ?>
                <tr>
                  <td style="padding: 6px 0; font-size: 13px; color: #64748B;"><strong>Phone:</strong></td>
                  <td style="padding: 6px 0; font-size: 14px; color: #0F172A;"><?php echo esc_html( $sender_phone ); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ( ! empty( $subject ) ) : ?>
                <tr>
                  <td style="padding: 6px 0; font-size: 13px; color: #64748B;"><strong>Subject:</strong></td>
                  <td style="padding: 6px 0; font-size: 14px; color: #0F172A;"><?php echo esc_html( $subject ); ?></td>
                </tr>
                <?php endif; ?>
              </table>
            </td>
          </tr>

          <!-- Message Body -->
          <tr>
            <td style="padding: 16px 28px 24px 28px;">
              <h2 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.05em;">Message Content:</h2>
              <div style="background-color: #FFFFFF; border-left: 4px solid #D49B28; padding: 14px 18px; border: 1px solid #E2E8F0; border-left: 4px solid #D49B28; border-radius: 4px; font-size: 14px; line-height: 1.6; color: #1E293B; white-space: pre-wrap;">
<?php echo esc_html( $message_body ); ?>
              </div>
            </td>
          </tr>

          <!-- Reply CTA -->
          <tr>
            <td style="padding: 0 28px 28px 28px;" align="left">
              <a href="mailto:<?php echo esc_attr( $sender_email ); ?>?subject=Re:%20<?php echo rawurlencode( $subject ?: 'Your message to Bethlehem Lutheran Church' ); ?>" style="display: inline-block; background-color: #D49B28; color: #FFFFFF; font-size: 14px; font-weight: 600; text-decoration: none; padding: 10px 20px; border-radius: 6px;">
                Reply Directly to <?php echo esc_html( $sender_name ); ?> &rarr;
              </a>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #F1F5F9; padding: 18px 28px; border-top: 1px solid #E2E8F0; font-size: 12px; color: #64748B; text-align: center; line-height: 1.5;">
              This notification was generated automatically by the contact form on <a href="https://blcmesa.org" style="color: #64748B; text-decoration: underline;">blcmesa.org</a>.<br>
              Bethlehem Lutheran Church &bull; 2745 N 32nd St, Mesa, AZ 85213 &bull; (480) 832-0462
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
