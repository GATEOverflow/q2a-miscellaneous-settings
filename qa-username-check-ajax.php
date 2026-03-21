<?php
/**
 * AJAX endpoint: check username availability + change-limit in real time.
 * URL: /ajax/username-check
 *
 * POST params:
 *   handle  – the candidate username
 *   qa_key  – CSRF token
 *
 * Returns plain text: one of
 *   ok            – username is available
 *   same          – unchanged from current
 *   taken         – already used by someone else
 *   limit         – user has hit their change limit
 *   error         – not logged in / bad token
 */

if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_username_check_ajax
{
    function match_request($request)
    {
        return $request === 'ajax/username-check';
    }

    function process_request($request)
    {
        $userid = qa_get_logged_in_userid();
        if (!$userid) {
            return $this->text('error');
        }

        $key = qa_post_text('qa_key');
        if (!qa_check_form_security_code('account', $key)) {
            return $this->text('error');
        }

        $new_handle     = trim(qa_post_text('handle'));
        $current_handle = qa_get_logged_in_handle();

        if ($new_handle === '' || $new_handle === $current_handle) {
            return $this->text('same');
        }

        require_once dirname(__FILE__) . '/qa-username-helper.php';
        $check = qa_username_change_check($userid, $new_handle);

        return $this->text($check['status']);
    }

    private function text($response)
    {
        header('Content-Type: text/plain');
        echo $response;
        exit;
    }
}
