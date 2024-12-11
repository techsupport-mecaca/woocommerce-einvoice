<?php
/**
 * WooCommerce eInvoice Admin Class.
 *
 * @package WooCommerce eInvoice
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_eInvoice_Admin class.
 */
class WC_eInvoice_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'woocommerce_admin_field_einvoice_settings', array( $this, 'render_einvoice_settings' ), 10, 1 );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'woocommerce_page_wc-settings' === $screen->id ) {
			wp_enqueue_script( 'wc-einvoice-admin', WC_EINVOICE_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), WC_EINVOICE_VERSION, true );
			wp_localize_script(
				'wc-einvoice-admin',
				'wc_einvoice_admin_params',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wc-einvoice-admin-nonce' ),
				)
			);
		}
	}

	/**
	 * Add WooCommerce eInvoice menu page.
	 */
	public function add_menu_page() {
		add_submenu_page(
			'woocommerce',
			__( 'eInvoice Settings', 'woocommerce-einvoice' ),
			__( 'eInvoice', 'woocommerce-einvoice' ),
			'manage_woocommerce',
			'wc-einvoice',
			array( $this, 'render_einvoice_settings_page' )
		);
	}

	/**
	 * Render eInvoice settings page.
	 */
	public function render_einvoice_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WooCommerce eInvoice Settings', 'woocommerce-einvoice' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wc_einvoice_settings' );
				do_settings_sections( 'wc-einvoice' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render eInvoice settings section.
	 *
	 * @param array $field Field data.
	 */
	public function render_einvoice_settings( $field ) {
		$settings = get_option( 'wc_einvoice_settings', array() );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( $field['type'] ); ?>">
				<?php
				switch ( $field['type'] ) {
					case 'text':
						?>
						<input name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" style="<?php echo esc_attr( $field['css'] ); ?>" value="<?php echo isset( $settings[ $field['id'] ] ) ? esc_attr( $settings[ $field['id'] ] ) : ''; ?>" class="regular-text" />
						<?php
						break;
					case 'textarea':
						?>
						<textarea name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" style="<?php echo esc_attr( $field['css'] ); ?>" class="large-text" rows="3"><?php echo isset( $settings[ $field['id'] ] ) ? esc_textarea( $settings[ $field['id'] ] ) : ''; ?></textarea>
						<?php
						break;
					case 'checkbox':
						?>
						<input name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="checkbox" value="1" <?php checked( isset( $settings[ $field['id'] ] ) && $settings[ $field['id'] ] === '1', true ); ?> />
						<?php
						break;
					case 'select':
						?>
						<select name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" style="<?php echo esc_attr( $field['css'] ); ?>">
							<?php foreach ( $field['options'] as $key => $value ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $settings[ $field['id'] ] ) ? $settings[ $field['id'] ] : '', $key ); ?>><?php echo esc_html( $value ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php
						break;
					default:
						do_action( 'woocommerce_admin_field_' . $field['type'], $field, $settings );
						break;
				}

				if ( ! empty( $field['description'] ) ) {
					?>
					<p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>
					<?php
				}
				?>
			</td>
		</tr>
		<?php
	}
}

new WC_eInvoice_Admin();
