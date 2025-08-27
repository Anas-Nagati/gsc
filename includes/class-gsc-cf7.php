<?php
if (!defined('ABSPATH')) {
    exit;
}

class GSC_CF7 {

    public function __construct() {
        // Send submissions after form is submitted
        add_action('wpcf7_mail_sent', [$this, 'send_to_api']);

        // Send form structure when a form is saved/updated
        add_action('wpcf7_after_save', [$this, 'send_form_to_laravel']);
    }

    /**
     * Send form submission data to Laravel API.
     */
    public function send_to_api($contact_form) {
        $submission = WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }

        $data = $submission->get_posted_data();
        $form_id = $contact_form->id();

        // Lookup sheet_id (replace with DB logic if needed)
        $sheet_id = get_option('sheet_for_form_' . $form_id, null);

        $payload = [
            'form_id'  => $form_id,
            'sheet_id' => $sheet_id,
            'data'     => $data,
        ];

        GSC_API::send($payload);
    }

    /**
     * Send form structure (fields + title) to Laravel API
     * whenever a CF7 form is created or updated.
     */
    public function send_form_to_laravel($form) {
        $fields = $this->extract_form_fields($form->prop('form'));

        wp_safe_remote_post('http://127.0.0.1:8000/api/forms', [
            'body'    => wp_json_encode([
                'form_id' => $form->id(),
                'title'   => $form->title(),
                'fields'  => $fields,
            ]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 15,
        ]);
    }

    /**
     * Extract CF7 field names from form markup.
     */
    private function extract_form_fields($form_markup) {
        preg_match_all(
            '/\[(?:text|email|textarea|tel|url|number|date|checkbox|radio|select)\s+([^\s\]]+)/',
            $form_markup,
            $matches
        );
        return $matches[1] ?? [];
    }
}
