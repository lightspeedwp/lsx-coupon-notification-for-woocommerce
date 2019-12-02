<?php
namespace lsx_cnw\classes;

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Coupon Notification WooCommerce Email class
 *
 * @since 0.1
 * @extends \WC_Email
 */
class CouponNotificationEmail extends \WC_Email {


	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx_cnw\classes\CouponNotificationEmail()
	 */
	protected static $instance = null;

	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {
		// set ID, this simply needs to be a unique name.
		$this->id = 'lsx_cnw_coupon_notification';

		// Is a customer email
		$this->customer_email = true;

		// this is the title in WooCommerce Email settings.
		$this->title = __( 'Coupon Notification' );

		// this is the description in WooCommerce email settings.
		$this->description = __( 'Coupon Notification Email sent to WooCommerce clients who subscribed to either Monthly or Annual subscription.' );

		// these are the default heading and subject lines that can be overridden using the settings.
		$this->heading = __( 'New Coupon from RW Plus!' );
		$this->subject = __( 'You have a new coupon from RW Plus.' );

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
		$this->template_base  = LSX_CNW_PATH . 'templates/';
		$this->template_html  = 'emails/lsx-coupon-notification.php';
		$this->template_plain = 'emails/plain/lsx-coupon-notification.php';

		// We tap into woocommerce_thankyou because coupon generation happens at woocommerce_before_thankyou.
		add_action( 'woocommerce_thankyou', array( $this, 'lsx_cnw_trigger' ) );

		// Call parent constructor to load any other defaults not explicity defined here.
		parent::__construct();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx_cnw\classes\CouponNotificationEmail()    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 2.0
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable Notification' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification' ),
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => __( 'Email Subject' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading' => array(
				'title'       => __( 'Email Heading' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
		);
	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 0.1
	 * @param int $order_id
	 */
	public static function lsx_cnw_trigger( $order_id ) {
		// bail if no order ID is present
		if ( ! $order_id ) {
			return;
		}

		// Send welcome email only once and not on every order status change
		if ( ! get_post_meta( $order_id, 'lsx_cnw_coupon_notification_sent', true ) ) {
			// setup order object.
			$this->object = new WC_Order( $order_id );

			// setup email recipient.
			$this->recipient = $this->object->billing_email;

			// get order items as array.
			$order_items = $this->object->get_items();

			// replace variables in the subject/headings.
			$this->find[]    = '{order_date}';
			$this->replace[] = date_i18n( woocommerce_date_format(), strtotime( $this->object->order_date ) );

			$this->find[]    = '{order_number}';
			$this->replace[] = $this->object->get_order_number();

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			// woohoo, send the email!
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			// add order note about the same
			$this->object->add_order_note( sprintf( __( 'Coupon notification email for order id %s was sent to the customer.' ), $order_id ) );

			// Set order meta to indicate that the email was sent
			update_post_meta( $this->object->id, 'lsx_cnw_coupon_notification_sent', 1 );
		}
	}

	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		woocommerce_get_template(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			)
		);
		return ob_get_clean();
	}


	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		woocommerce_get_template(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			)
		);
		return ob_get_clean();
	}
} // end \WC_Coupon_Notification_Email class
