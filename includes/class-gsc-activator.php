<?php

/**
 * Fired during plugin activation
 *
 * @link       https://anasnagati.com
 * @since      1.0.0
 *
 * @package    Gsc
 * @subpackage Gsc/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gsc
 * @subpackage Gsc/includes
 * @author     Anas
 */
class Gsc_Activator {

    /**
     * Run code on plugin activation.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        // ✅ Create custom table
        $table_name = $wpdb->prefix . 'gsc_sheets';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id VARCHAR(100) NOT NULL,
            sheet_id VARCHAR(255) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY form_id (form_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Send all CF7 forms to external API.
     *
     * @since    1.0.0
     */
    public static function send_all_forms() {
        if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
            return; // CF7 not installed or active
        }

        $forms = WPCF7_ContactForm::find();

        foreach ( $forms as $form ) {
            $tags = $form->scan_form_tags();
            $fields = [];

            foreach ( $tags as $tag ) {
                // Skip submit buttons
                if ( $tag->basetype === 'submit' ) {
                    continue;
                }

                $fields[] = [
                    'name' => $tag->name,
                    'type' => $tag->basetype,
                ];
            }

            error_log( print_r( $fields, true ) );
            error_log("form_id". $form->id());
            $response = wp_remote_post( 'http://127.0.0.1:8000/api/forms', [
                'body'    => wp_json_encode([
                    'form_id' => $form->id(),
                    'title'   => $form->title(),
                    'fields'  => $fields,
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-API-TOKEN'  => 'Bearer 8b5196c6-6c1f-439c-ba45-e1204c207d188b5196c6-6c1f-439c-ba45-e120',
                ],
                'timeout' => 15,
            ]);

            error_log( print_r( $response, true ) );

        }
    }

    /**
     * Extract form fields from CF7 markup.
     *
     * @since    1.0.0
     */
    private static function extract_form_fields( $form_markup ) {
        preg_match_all(
            '/\[(?:text|email|textarea|tel|url|number|date|checkbox|radio|select)\s+([^\s\]]+)/',
            $form_markup,
            $matches
        );
        return $matches[1] ?? [];
    }
}
