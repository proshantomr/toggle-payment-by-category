<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Toggle_Payments_By_Shipping_Admin
 *
 * This class handles the admin functionalities for the Toggle Payments by Shipping plugin.
 * It includes methods for enqueuing admin scripts, managing the admin menu, displaying
 * the admin page, and updating plugin settings.
 *
 * @since 1.0.0
 */
class Toggle_Payments_By_Category_Admin {

	/**
	 * Toggle_Payments_By_Shipping_Admin constructor.
	 *
	 * Initializes the admin class and hooks necessary actions.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_tpbc_update_settings', array( $this, 'update_settings' ) );
	}

	/**
	 * Enqueues admin-specific styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style( 'tpbc_admin_style', TPBC_PLUGIN_URL . 'assets/css/admin.css', array(), TPBC_VERSION );
		wp_enqueue_script( 'tpbc_admin_scripts', TPBC_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), TPBC_VERSION, true );
	}

	/**
	 * Adds an item to the WordPress admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_menu_page(
			'Toggle Payments by Category',
			'Toggle Payments by Category',
			'manage_options',
			'toggle-payments-by-category',
			array( $this, 'admin_page' ),
			'dashicons-money-alt',
			'58'
		);
	}

	/**
	 * Displays the admin page for configuring payment settings.
	 *
	 * This method renders the HTML for the settings page, including forms
	 * for managing payment visibility based on shipping regions.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h2>Toggle Payments by Category</h2>
			<button type="button" class="add-new-button">Add New</button>
			<form class="product-catalog-mode-form" method="POST"
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php
				// Fetch product categories and payment gateways.
				$product_categories = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false, // You can change this to true if you want to hide empty categories.
					)
				);

				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

				// Load saved settings.
				$saved_payment_settings = get_option( 'tpbc_payment_settings', array() );

				// Collect already selected gateways.
				$selected_gateways = array();
				if ( ! empty( $saved_payment_settings ) ) {
					foreach ( $saved_payment_settings as $payment_setting ) {
						if ( isset( $payment_setting['method'] ) ) {
							$selected_gateways[] = $payment_setting['method'];
						}
					}
				}

				// Extract the list of available gateway IDs from the $available_gateways array.
				$available_gateway_ids = array_map(
					function ( $gateway ) {
						return $gateway->id;
					},
					$available_gateways
				);

				// Compare the available gateways with the selected gateways from the database.
				$unselected_gateways = array_diff( $available_gateway_ids, $selected_gateways );

				$count_unselected_getways = count( $unselected_gateways );
				if ( $count_unselected_getways > 0 ) {
					$unselected_gateway_names = implode( ', ', $unselected_gateways );
					echo '<div class="gateway-message">';
					echo "The following payment gateways are still available for selection: <span class='gateway-name'>" . esc_html( $unselected_gateway_names ) . '</span>.';
					echo '</div>';
				}

				?>
				<div class="form-container">
					<table id="payment-table" class="form-table">
						<thead class="heading">
						<tr>
							<th style="text-align: center">Select Category</th>
							<th style="text-align: center">Payment Method</th>
							<th style="text-align: center">Payment Visibility</th>
							<th style="text-align: center">Action</th>
						</tr>
						</thead>

						<tbody class="row">
						<!-- Template Row (Hidden). -->
						<tr class="template-row" style="display:none;">
							<td>
								<!-- Updated dropdown to show categories -->
								<select name="tpbc_category[]">
									<?php
									// Loop through product categories and populate the dropdown.
									if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
										foreach ( $product_categories as $category ) {
											?>
											<option value="<?php echo esc_attr( $category->term_id ); ?>">
												<?php echo esc_html( $category->name ); ?>
											</option>
											<?php
										}
									} else {
										?>
										<option value=""><?php esc_html_e( 'No Categories Found', 'toggle-payment-by-category' ); ?></option>
									<?php } ?>
								</select>
							</td>
							<td>
								<select name="tpbc_payment_method[]">
									<?php
									foreach ( $available_gateways as $gateway ) {
										// Check if the current gateway is in the unselected gateways and if there's exactly 1 unselected gateway.
										// phpcs:ignore
										$disabled = ( $count_unselected_getways === 1 && in_array( $gateway->id, $unselected_gateways ) ) ? 'disabled' : '';
										?>
										<option value="<?php echo esc_attr( $gateway->id ); ?>"
											<?php selected( $gateway->id, $payment_setting['method'] ); ?>
											<?php echo esc_html( $disabled ); ?>>
											<?php echo esc_html( $gateway->get_title() ); ?>
										</option>
									<?php } ?>
								</select>
							</td>
							<td>
								<label class="toggle-switch">
									<input type="hidden" name="payment_visibility_h[]" id="payment_visibility_h" value="show">

									<!-- Checkbox with inline onchange logic -->
									<input type="checkbox" name="payment_visibility[]" value="show"
											onchange="this.value = this.checked ? 'hide' : 'show';
												this.previousElementSibling.value = this.value;
												this.parentElement.nextElementSibling.textContent = this.value == 'show' ? 'Show Payment' : 'Hide Payment';">

									<!-- Slider -->
									<span class="slider"></span>
								</label>
								<span class="toggle-comment">Show Payment</span>
							</td>
							<td>
								<button type="button" class="delete-button">Delete</button>
							</td>
						</tr>
						<?php
						// Load saved settings.
						$saved_payment_settings = get_option( 'tpbc_payment_settings', array() );

						if ( ! empty( $saved_payment_settings ) ) {
							foreach ( $saved_payment_settings as $key => $payment_setting ) {
								// Assuming category and payment method have the same index.
								$category = isset( $saved_payment_settings[ $key ]['category'] ) ? $saved_payment_settings[ $key ]['category'] : array();
								?>
								<tr>
									<td>
										<select name="tpbc_category[]">
											<?php
											foreach ( $product_categories as $category_obj ) {
												?>
												<option value="<?php echo esc_attr( $category_obj->term_id ); ?>"
													<?php selected( $category_obj->term_id, $category ); ?>>
													<?php echo esc_html( $category_obj->name ); ?>

												</option>
											<?php } ?>
										</select>
									</td>
									<td>
										<select name="tpbc_payment_method[]">
											<?php
											foreach ( $available_gateways as $gateway ) {
												// Check if the current gateway is in the unselected gateways and if there's exactly 1 unselected gateway.
												// phpcs:ignore
												$disabled = ( $count_unselected_getways === 1 && in_array( $gateway->id, $unselected_gateways ) ) ? 'disabled' : '';
												?>
												<option value="<?php echo esc_attr( $gateway->id ); ?>"
													<?php selected( $gateway->id, $payment_setting['method'] ); ?>
													<?php echo esc_html( $disabled ); ?>>
													<?php echo esc_html( $gateway->get_title() ); ?>
												</option>
											<?php } ?>
										</select>
									</td>
									<td>
										<label class="toggle-switch">
											<input type="hidden" name="payment_visibility_h[]" id="<?php echo esc_attr( 'payment_visibility_h_' . $key ); ?>"
													value="<?php echo esc_attr( ( 'hide' === $payment_setting['visibility'] ) ? 'hide' : 'show' ); ?>">
											<input type="checkbox" name="payment_visibility[]"
													value="<?php echo esc_attr( ( 'hide' === $payment_setting['visibility'] ) ? 'hide' : 'show' ); ?>"
												<?php checked( 'hide', $payment_setting['visibility'] ); ?>
													id="<?php echo esc_attr( 'payment_visibility_' . $key ); ?>"
													onchange="toogleSwitch(this.value, <?php echo esc_attr( $key ); ?>)">
											<span class="slider"></span>
										</label>
										<span class="toggle-comment">
											<?php echo esc_html( ( 'hide' === $payment_setting['visibility'] ) ? 'Hide Payment' : 'Show Payment' ); ?>
										</span>
									</td>
									<td>
										<button type="button" class="delete-button">Delete</button>
									</td>
								</tr>
								<?php
							}
						}
						?>
						</tbody>
					</table>

					<!-- Hidden Inputs for form submission -->
					<input type="hidden" name="action" value="tpbc_update_settings">
					<?php wp_nonce_field( 'tpbc_update_settings_nonce', 'tpbc_nonce_field' ); ?>
					<button type="submit" class="save-button">Save Changes</button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Updates the plugin's settings when the form is submitted.
	 *
	 * This method processes the form submission, sanitizes and validates the input,
	 * and updates the plugin's settings in the database.
	 *
	 * @since 1.0.0
	 */
	public function update_settings() {
		// Check the nonce for security.
		if ( ! isset( $_POST['tpbc_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tpbc_nonce_field'] ) ), 'tpbc_update_settings_nonce' ) ) {
			wp_die( 'Invalid nonce.' );
		}

		// Validate and sanitize the input data.
		$categories         = isset( $_POST['tpbc_category'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['tpbc_category'] ) ) : array();
		$payment_methods    = isset( $_POST['tpbc_payment_method'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['tpbc_payment_method'] ) ) : array();
		$payment_visibility = isset( $_POST['payment_visibility_h'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['payment_visibility_h'] ) ) : array();
		// Update the plugin settings.
		$settings = array();
		for ( $i = 0, $n = count( $categories ); $i < $n; $i++ ) {
			$settings[] = array(
				'category'   => $categories[ $i ],
				'method'     => $payment_methods[ $i ],
				'visibility' => $payment_visibility[ $i ],
			);
		}

		update_option( 'tpbc_payment_settings', $settings );
		wp_safe_redirect( admin_url( 'admin.php?page=toggle-payments-by-category' ) );
		exit;
	}
}
