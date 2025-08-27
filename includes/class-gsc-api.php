<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use WpOrg\Requests\Requests;

class GSC_API {

    /**
     * Send payload to Laravel API
     *
     * @param array $payload
     * @return bool
     */
    public static function send($payload) {
        $url = 'http://127.0.0.1:8000/api/entries';

        try {
            $response = Requests::post(
                $url,
                ['Content-Type' => 'application/json; charset=utf-8'],
                wp_json_encode($payload)
            );

            if ($response->status_code !== 200) {
                error_log("GSC API error: " . $response->status_code . " - " . $response->body);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            error_log("GSC API request failed: " . $e->getMessage());
            return false;
        }
    }

    function send_to_api($endpoint, $data) {
        $api_key = get_option('gsc_api_key');

        $response = wp_safe_remote_post('http://127.0.0.1:8000/api/forms/sync' . $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode($data),
        ]);

        return wp_remote_retrieve_body($response);
    }

}
