<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://anasnagati.com
 * @since      1.0.0
 *
 * @package    Gsc
 * @subpackage Gsc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gsc
 * @subpackage Gsc/admin
 * @author     Anas nagati <Anasnagati@gmail.com>
 */
class Gsc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        add_action('admin_init', [$this, 'register_settings']);

	}

    public function add_menu_page() {
        add_menu_page(
            'Google Sheets Connector',
            'Google Sheets',
            'manage_options',
            'gsc-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-google',
            30
        );
    }

    public function render_settings_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gsc_sheets';

        // Handle form submission
        if ( isset($_POST['gsc_save_sheets']) && check_admin_referer('gsc_save_sheets_action','gsc_save_sheets_nonce') ) {
            foreach ($_POST['sheet_links'] as $form_id => $link) {
                $sheet_id = $this->extract_sheet_id($link);

                if ( ! empty($sheet_id) ) {
                    $wpdb->replace(
                        $table_name,
                        array(
                            'form_id' => $form_id,
                            'sheet_id' => $sheet_id,
                        ),
                        array( '%s', '%s' )
                    );
                }
            }
            echo '<div class="updated"><p>Settings saved.</p></div>';
        }

        // Get all CF7 forms
        $forms = get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'numberposts' => -1
        ));

        // Add WooCommerce checkout as a pseudo-form
        $forms[] = (object) array(
            'ID' => 'woocommerce_checkout',
            'post_title' => 'WooCommerce Checkout'
        );

        // Fetch stored values
        $stored = $wpdb->get_results("SELECT form_id, sheet_id FROM $table_name", OBJECT_K);

        ?>
        <div class="wrap">
            <h1>Google Sheets Connector</h1>
            <form method="post">
                <?php wp_nonce_field('gsc_save_sheets_action','gsc_save_sheets_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th>Form</th>
                        <th>Google Sheet Link</th>
                    </tr>
                    <?php foreach ($forms as $form):
                        $form_id = $form->ID;
                        $sheet_id = isset($stored[$form_id]) ? $stored[$form_id]->sheet_id : '';
                        ?>
                        <tr>
                            <td><?php echo esc_html($form->post_title); ?></td>
                            <td>
                                <input type="text" name="sheet_links[<?php echo esc_attr($form_id); ?>]"
                                       value="<?php echo esc_attr($sheet_id ? 'https://docs.google.com/spreadsheets/d/' . $sheet_id . '/edit' : ''); ?>"
                                       style="width:100%">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p class="submit">
                    <button type="submit" name="gsc_save_sheets" class="button-primary">Save Settings</button>
                </p>
            </form>
        </div>
        <?php
    }

    private function extract_sheet_id($url) {
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gsc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gsc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gsc-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gsc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gsc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gsc-admin.js', array( 'jquery' ), $this->version, false );

	}
    public function register_settings() {
        register_setting('gsc_options', 'gsc_api_key');
        add_settings_section('general', 'General Settings', null, 'gsc');
        add_settings_field('api_key', 'API Key', [$this, 'api_key_field'], 'gsc', 'general');
    }
    public function api_key_field() {
        $api_key = get_option('gsc_api_key');
        echo "<input type='text' name='gsc_api_key' value='" . esc_attr($api_key) . "' size='50'>";
    }
}
