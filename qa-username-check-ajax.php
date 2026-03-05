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
        require_once QA_INCLUDE_DIR . 'db/metas.php';
        $userid = qa_get_logged_in_userid();
        if (!$userid) {
            $this->text('error');
        }

        $key = qa_post_text('qa_key');
        if (!qa_check_form_security_code('account', $key)) {
            $this->text('error');
        }

        $new_handle     = trim(qa_post_text('handle'));
        $current_handle = qa_get_logged_in_handle();

        if ($new_handle === '' || $new_handle === $current_handle) {
            $this->text('same');
        }

        require_once QA_INCLUDE_DIR . 'db/users.php';
        $handleusers = qa_db_user_find_by_handle($new_handle);
        if (count($handleusers) && array_search($userid, $handleusers) === false) {
            $this->text('taken');
        }

        $allowed   = (int)qa_opt('allowed_username_changes');
        $used      = (int)qa_db_usermeta_get($userid, 'username_change_count');
        $remaining = max(0, $allowed - $used);

        if ($allowed > 0 && $remaining <= 0) {
            $this->text('limit');
        }

        $this->text('ok');
    }

    private function text($response)
    {
        header('Content-Type: text/plain');
        echo $response;
        exit;
    }
}
