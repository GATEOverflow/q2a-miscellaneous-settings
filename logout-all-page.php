<?php
if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_logout_all_page
{
    public function match_request($request) {
        return $request === 'logout-all-devices';
    }

    public function process_request($request) {
        if (!qa_is_logged_in()) {
            qa_redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            qa_redirect('');
            return;
        }

        if (!qa_check_form_security_code('logout-all-devices', qa_post_text('code'))) {
            qa_redirect('');
            return;
        }

        $userid = qa_get_logged_in_userid();

        if (qa_clicked('do_logout_all')) {
            // Logout from ALL devices including current
            $this->logout_all_devices($userid);

        } elseif (qa_clicked('do_logout_others')) {
            // Logout from all OTHER devices, keep current alive
            $this->logout_other_devices($userid);
        }

        qa_redirect('');
        exit;
    }

    private function logout_all_devices($userid) {
        require_once QA_INCLUDE_DIR . 'util/string.php';

        // Rotate sessioncode — kills all cookies on all devices
        qa_db_query_sub(
            'UPDATE ^users SET sessioncode=$ WHERE userid=#',
            qa_random_alphanum(8),
            $userid
        );

        // Clear current device cookie
        if (isset($_COOKIE['qa_session'])) {
            setcookie('qa_session', '', time() - 3600, '/');
        }

        // Destroy current session
        $_SESSION = array();
        session_destroy();

        qa_redirect('login');
        exit;
    }

    private function logout_other_devices($userid) {
        require_once QA_INCLUDE_DIR . 'util/string.php';

        $new_sessioncode = qa_random_alphanum(8);

        qa_db_query_sub(
            'UPDATE ^users SET sessioncode=$ WHERE userid=#',
            $new_sessioncode,
            $userid
        );

        $username = qa_db_read_one_value(
            qa_db_query_sub(
                'SELECT handle FROM ^users WHERE userid=#',
                $userid
            )
        );

        $cookie_value = $username . '/' . $new_sessioncode . '/0';

        /* error_log('=== logout_other_devices ===');
        error_log('New sessioncode: ' . $new_sessioncode);
        error_log('Username: '        . $username);
        error_log('Cookie value set: '. $cookie_value); */

        setcookie(
            'qa_session',
            $cookie_value,
            time() + (60 * 60 * 24 * 365),
            '/',
            '',
            qa_is_https_probably(),
            true
        );

        $_COOKIE['qa_session'] = $cookie_value;

        error_log('$_COOKIE after set: ' . $_COOKIE['qa_session']);

        qa_redirect('');
        exit;
    }
}