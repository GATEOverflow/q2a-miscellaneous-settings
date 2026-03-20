<?php
if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
    public function initialize() {
        $check_interval = (int)qa_opt('misc_enable_logout_all');
        if ($check_interval > 0) {
            $this->validate_session($check_interval);
        }

        // Add logout-all form to account page
        if (qa_request_part(0) === 'account' && qa_is_logged_in()) {
            $csrf_code = qa_get_form_security_code('logout-all-devices');

            $this->content['form_logout_all'] = array(
                'tags' => 'method="post" action="' . qa_path_html('logout-all-devices') . '"',
                'style' => 'wide',
                'title' => qa_lang_html('qa_misc_lang/logout_all_title'),
                'fields' => array(
                    'info' => array(
                        'type' => 'static',
                        'value' => qa_lang_html('qa_misc_lang/logout_all_message'),
                    ),
                ),
                'buttons' => array(
                    'logout_all' => array(
                        'tags' => 'name="do_logout_all" id="qa-logout-all-btn"',
                        'label' => qa_lang_html('qa_misc_lang/logout_all_button'),
                    ),
                    'logout_others' => array(
                        'tags' => 'name="do_logout_others" id="qa-logout-others-btn"',
                        'label' => qa_lang_html('qa_misc_lang/logout_others_button'),
                    ),
                ),
                'hidden' => array(
                    'code' => $csrf_code,
                ),
            );
        }

        parent::initialize();
    }

    public function head_css() {
        if (qa_request_part(0) === 'account' && qa_is_logged_in()) {
            echo '
            <style>
            #qa-logout-all-btn {
                background: #e74c3c !important;
                color: #fff !important;
                border: none !important;
            }
            #qa-logout-all-btn:hover {
                background: #c0392b !important;
            }
            #qa-logout-others-btn {
                background: #0b7cff !important;
                color: #fff !important;
                border: none !important;
            }
            #qa-logout-others-btn:hover {
                background: #3d9aff !important;
            }
            html[data-theme="dark"] #qa-logout-all-btn {
                background: #c0392b !important;
                box-shadow: 0 4px 14px rgba(192,57,43,0.25);
            }
            html[data-theme="dark"] #qa-logout-all-btn:hover {
                background: #e74c3c !important;
            }
            html[data-theme="dark"] #qa-logout-others-btn {
                background: #0b7cff !important;
                box-shadow: 0 4px 14px rgba(11,124,255,0.25);
            }
            html[data-theme="dark"] #qa-logout-others-btn:hover {
                background: #3d9aff !important;
            }
            </style>';
        }
        parent::head_css();
    }

    private function validate_session($check_interval) {
        if (!qa_is_logged_in()) return;
        if (empty($_COOKIE['qa_session'])) return;

        $cookie_parts = explode('/', $_COOKIE['qa_session']);
        $cookie_code  = $cookie_parts[1] ?? '';

        if (empty($cookie_code)) {
            $this->force_logout();
            return;
        }

        // Cache the verified sessioncode in the PHP session to avoid a DB
        // query on every single page load.  Re-validate against the database
        // only when the cached entry is missing or older than the configured interval.
        $now = time();

        if (
            isset($_SESSION['qa_sc_code'], $_SESSION['qa_sc_time']) &&
            $_SESSION['qa_sc_code'] === $cookie_code &&
            ($now - $_SESSION['qa_sc_time']) < $check_interval
        ) {
            return; // recently verified — skip DB query
        }

        $userid = qa_get_logged_in_userid();

        $db_code = qa_db_read_one_value(
            qa_db_query_sub(
                'SELECT sessioncode FROM ^users WHERE userid=#',
                $userid
            )
        );

        if ($cookie_code !== $db_code) {
            $this->force_logout();
            return;
        }

        // Store verified result so subsequent requests skip the DB query
        $_SESSION['qa_sc_code'] = $cookie_code;
        $_SESSION['qa_sc_time'] = $now;
    }

    private function force_logout() {
        $_SESSION = array();
        if (isset($_COOKIE['qa_session'])) {
            setcookie('qa_session', '', time() - 3600, '/');
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        qa_redirect('login');
        exit;
    }
}