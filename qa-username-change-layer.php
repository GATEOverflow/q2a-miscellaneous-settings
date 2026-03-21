<?php

class qa_html_theme_layer extends qa_html_theme_base
{
    function form($form)
    {
        if (qa_request_part(0) === 'account' && isset($form['fields']['handle'])) {
            require_once QA_INCLUDE_DIR . 'db/metas.php';

            $userid    = qa_get_logged_in_userid();
            $allowed   = (int)qa_opt('allowed_username_changes');
            $used      = (int)qa_db_usermeta_get($userid, 'username_change_count');
            $remaining = max(0, $allowed - $used);

            if ($remaining <= 0 || $allowed == 0) {
                // Hard-lock: no AJAX needed, just static display
                $form['fields']['handle']['type'] = 'static';
                $form['fields']['handle']['note'] =
                    '<span style="color:gray">' . qa_lang('qa_misc_lang/username_locked') . '</span>';
            } else {
                $form['fields']['handle']['type'] = 'text';

                // CSRF token for the AJAX call
                $security_code = qa_get_form_security_code('account');
                $ajax_url      = qa_path_html('ajax/username-check');

                $form['fields']['handle']['note'] =
                    qa_lang_sub('qa_misc_lang/username_changes_remaining', '<strong id="uc-remaining">' . $remaining . '</strong>') .
                    ' <span id="uc-status" style="margin-left:8px;font-weight:bold"></span>';

                // Inject the JS once via a hidden note on the same field.
                // We append it as raw HTML so Q2A renders it inside the form.
                $form['fields']['handle']['note'] .= $this->username_check_script($ajax_url, $security_code, $remaining);
            }
        }

        parent::form($form);
    }

    /* ------------------------------------------------------------------ */
    private function username_check_script($ajax_url, $security_code, $remaining)
    {
        $ajax_url_js      = json_encode($ajax_url);
        $security_code_js = json_encode($security_code);

        return <<<HTML
    <script>
    (function () {
    if (window._ucInit) return;
    window._ucInit = true;

    var ajaxUrl  = {$ajax_url_js};
    var qaKey    = {$security_code_js};
    var statusEl = document.getElementById('uc-status');

    function setStatus(text, color) {
        if (!statusEl) return;
        statusEl.textContent = text;
        statusEl.style.color = color;
    }

    function check(value) {
        if (!value) { setStatus('', ''); return; }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            var r = xhr.responseText.trim();
            if      (r === 'ok')    setStatus('✓ Available', 'green');
            else if (r === 'taken') setStatus('✗ Already taken', 'red');
            else if (r === 'limit') setStatus('✗ Change limit reached', 'red');
            else                    setStatus('', '');
        };
        xhr.send('handle=' + encodeURIComponent(value) + '&qa_key=' + encodeURIComponent(qaKey));
    }

    function attach() {
        var input = document.querySelector('input[name="handle"]');
        if (!input) return;
        var timer;
        input.addEventListener('keyup', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { check(input.value.trim()); }, 300);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attach);
    } else {
        attach();
    }
    }());
    </script>
    HTML;
    }
}