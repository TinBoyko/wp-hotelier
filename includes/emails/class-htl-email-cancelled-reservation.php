<?php
/**
 * Cancelled Reservation Email (sent to admin).
 *
 * @author   Benito Lopez <hello@lopezb.com>
 * @category Class
 * @package  Hotelier/Classes/Emails
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HTL_Email_Cancelled_Reservation' ) ) :

/**
 * HTL_Email_Cancelled_Reservation Class
 */
class HTL_Email_Cancelled_Reservation extends HTL_Email {

	/**
	 * Constructor
	 */
	function __construct() {

		$this->id               = 'cancelled_reservation';
		$this->title            = esc_html__( 'Cancelled reservation', 'hotelier' );

		$this->heading          = htl_get_option( 'emails_cancelled_reservation_heading', __( 'Cancelled reservation', 'hotelier' ) );
		$this->subject          = htl_get_option( 'emails_cancelled_reservation_subject', __( '{site_title} - Cancelled reservation #{reservation_number}', 'hotelier' ) );

		$this->template_html    = 'emails/admin-cancelled-reservation.php';
		$this->template_plain   = 'emails/plain/admin-cancelled-reservation.php';
		$this->enabled          = htl_get_option( 'emails_cancelled_reservation_enabled', true );

		// Triggers for this email
		add_action( 'hotelier_reservation_status_pending_to_cancelled_notification', array( $this, 'trigger' ) );
		add_action( 'hotelier_reservation_status_on-hold_to_cancelled_notification', array( $this, 'trigger' ) );
		add_action( 'hotelier_reservation_status_confirmed_to_cancelled_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();

		// Recipient
		$this->recipient = htl_get_option( 'emails_admin_notice' );

		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}

	/**
	 * Trigger.
	 */
	function trigger( $reservation_id ) {

		if ( $reservation_id ) {
			$this->object                          = htl_get_reservation( $reservation_id );
			$this->find[ 'reservation-number' ]    = '{reservation_number}';
			$this->replace[ 'reservation-number' ] = $this->object->get_reservation_number();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		htl_get_template( $this->template_html, array(
			'reservation'   => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false
		) );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		htl_get_template( $this->template_plain, array(
			'reservation'   => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => true
		) );
		return ob_get_clean();
	}
}

endif;

return new HTL_Email_Cancelled_Reservation();