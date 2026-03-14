<?php
if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
   public function nav_user_search() {
        // Add "Logout All" button to user nav — only if logged in
        if (qa_is_logged_in()) {
            $this->content['navigation']['user']['logout-all'] = array(
                'url'   => '#',
                'label' => qa_lang('qa_misc_lang/logout_all_nav'),
                'popup' => 'Logout from all devices',
                'tags' => 'id="qa-logout-all-trigger"',
            );
        }
        parent::nav_user_search();
    }
    public function svg_nav_main($class, $label) {
        // Inject our custom icon for "Logout All"
        if ($label === 'Logout All') {
            return '<svg xmlns="http://www.w3.org/2000/svg" class="qa-svg" height="24" width="24" viewBox="0 0 50 50">
    <path fill="currentColor" d="M22.5 26.5h3v-15h-3ZM24 42q-3.75 0-7.05-1.425-3.3-1.425-5.75-3.875-2.45-2.45-3.825-5.725Q6 27.7 6 23.9q0-4.05 1.65-7.625Q9.3 12.7 12.4 10.25l2.1 2.1q-2.6 2.1-4.05 5.075Q9 20.4 9 23.9q0 6.25 4.375 10.675Q17.75 39 24 39q6.3 0 10.65-4.425Q39 30.15 39 23.9q0-3.55-1.45-6.525-1.45-2.975-4.1-5.025l2.1-2.1q3.1 2.45 4.775 6.025Q42 19.85 42 23.9q0 3.8-1.4 7.075-1.4 3.275-3.85 5.725-2.45 2.45-5.725 3.875Q27.75 42 24 42Z"/>
</svg>';
        }

        // Fall through to Polaris's original svg_nav_main for everything else
        return parent::svg_nav_main($class, $label);
    }

    public function head_css() {
        echo '
        <style>
        /* ── SnowFlat icon for Logout All ── */
        /* Uses same Fontello icon font as SnowFlat logout icon */
        .qa-nav-user-logout-all:before {
            content: "\e824";        /* same icon as logout */
            font-family: "fontello";
            display: inline-block;
            width: 1em;
            background-color: #471a15;  /* red to distinguish from normal logout */
            padding: 5px;
            margin: 0 10px 0 0;
            text-align: center;
            line-height: normal;
            border-radius: 1em;
        }
        /* ── Modal Base ── */
        #qa-logout-modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
        }
        #qa-logout-modal-box {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            z-index: 9999;
            min-width: 320px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        #qa-logout-modal-box h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #111;
        }
        #qa-logout-modal-box p {
            color: #555;
            margin: 0 0 20px 0;
            font-size: 14px;
        }
        #qa-logout-modal-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }
        .qa-logout-modal-btn {
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            color: #fff;
            width: 100%;
        }
        #qa-logout-all-btn    { background: #e74c3c; }
        #qa-logout-all-btn:hover { background: #c0392b; }
        #qa-logout-others-btn { background: #0b7cff; }
        #qa-logout-others-btn:hover { background: #3d9aff; }
        #qa-logout-modal-cancel {
            color: #999;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
        }
        #qa-logout-modal-cancel:hover { color: #333; }

        /* ── Dark Mode ── */
        html[data-theme="dark"] #qa-logout-modal-overlay {
            background: rgba(0,0,0,0.75);
        }
        html[data-theme="dark"] #qa-logout-modal-box {
            background: #1a1a1a;
            box-shadow:
                0 4px 6px rgba(0,0,0,0.3),
                0 20px 60px rgba(0,0,0,0.5);
        }
        html[data-theme="dark"] #qa-logout-modal-box h3 {
            color: #f0f0f0;
        }
        html[data-theme="dark"] #qa-logout-modal-box p {
            color: #aaa;
        }
        html[data-theme="dark"] #qa-logout-all-btn {
            background: #c0392b;
            box-shadow: 0 4px 14px rgba(192,57,43,0.25);
        }
        html[data-theme="dark"] #qa-logout-all-btn:hover {
            background: #e74c3c;
            box-shadow: 0 6px 18px rgba(192,57,43,0.35);
        }
        html[data-theme="dark"] #qa-logout-others-btn {
            background: #0b7cff;
            box-shadow: 0 4px 14px rgba(11,124,255,0.25);
        }
        html[data-theme="dark"] #qa-logout-others-btn:hover {
            background: #3d9aff;
            box-shadow: 0 6px 18px rgba(11,124,255,0.35);
        }
        html[data-theme="dark"] #qa-logout-modal-cancel {
            color: #aaa;
        }
        html[data-theme="dark"] #qa-logout-modal-cancel:hover {
            color: #fff;
        }
        </style>';
        parent::head_css();
    }

    public function body_suffix() {
        if (qa_is_logged_in()) {
            $csrf_code = qa_get_form_security_code('logout-all-devices');
            echo '
            <!-- Logout All Modal -->
            <div id="qa-logout-modal" style="display:none;">
                <div id="qa-logout-modal-overlay"></div>
                <div id="qa-logout-modal-box">
                    <h3>' . qa_lang_html('qa_misc_lang/logout_all_title') . '</h3>
                    <p>' . qa_lang_html('qa_misc_lang/logout_all_message') . '</p>
                    <form method="post" action="' . qa_path_html('logout-all-devices') . '">
                        <input type="hidden" name="code" value="' . $csrf_code . '">
                        <div id="qa-logout-modal-buttons">
                            <button type="submit" 
                                    name="do_logout_all" 
                                    class="qa-logout-modal-btn" 
                                    id="qa-logout-all-btn">
                                ' . qa_lang_html('qa_misc_lang/logout_all_button') . '
                            </button>
                            <button type="submit" 
                                    name="do_logout_others" 
                                    class="qa-logout-modal-btn" 
                                    id="qa-logout-others-btn">
                                ' . qa_lang_html('qa_misc_lang/logout_others_button') . '
                            </button>
                        </div>
                    </form>
                    <a href="#" id="qa-logout-modal-cancel">' . qa_lang_html('qa_misc_lang/logout_modal_cancel') . '</a>
                </div>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function() {
                var modal   = document.getElementById("qa-logout-modal");
                var overlay = document.getElementById("qa-logout-modal-overlay");
                var cancel  = document.getElementById("qa-logout-modal-cancel");

                // Find trigger by text content since Q2A ignores attrs/id
                var trigger = null;
                var allLinks = document.querySelectorAll("a");
                for (var i = 0; i < allLinks.length; i++) {
                    if (allLinks[i].textContent.trim() === "Logout All") {
                        trigger = allLinks[i];
                        break;
                    }
                }

                if (!trigger) {
                    //console.log("Logout All trigger not found!");
                    return;
                }

                //console.log("Trigger found:", trigger);

                trigger.addEventListener("click", function(e) {
                    e.preventDefault();
                    modal.style.display = "block";
                });

                cancel.addEventListener("click", function(e) {
                    e.preventDefault();
                    modal.style.display = "none";
                });

                overlay.addEventListener("click", function() {
                    modal.style.display = "none";
                });
            });
            </script>
            ';
        }
        parent::body_suffix();
    }

    public function initialize() {
        if (qa_opt('misc_enable_logout_all')) {
            $this->validate_session();
        }
        parent::initialize();
    }

    private function validate_session() {
        if (!qa_is_logged_in()) return;
        if (empty($_COOKIE['qa_session'])) return;

        $userid = qa_get_logged_in_userid();
        $cookie_parts = explode('/', $_COOKIE['qa_session']);
        $cookie_code  = $cookie_parts[1] ?? '';

        $db_code = qa_db_read_one_value(
            qa_db_query_sub(
                'SELECT sessioncode FROM ^users WHERE userid=#',
                $userid
            )
        );

        /* error_log('=== validate_session ===');
        error_log('Raw cookie: '    . $_COOKIE['qa_session']);
        error_log('Cookie parts: '  . print_r($cookie_parts, true));
        error_log('Cookie code: '   . $cookie_code);
        error_log('DB code: '       . $db_code);
        error_log('Match: '         . ($cookie_code === $db_code ? 'YES' : 'NO'));
        error_log('Referer: '       . ($_SERVER['HTTP_REFERER'] ?? 'none')); */

        if (empty($cookie_code) || $cookie_code !== $db_code) {
            $this->force_logout();
        }
    }
    private function force_logout() {
        // Clear everything
        $_SESSION = array();
        if (isset($_COOKIE['qa_session'])) {
            setcookie('qa_session', '', time() - 3600, '/');
        }
        session_destroy();
        qa_redirect('login');
        exit;
    }
}