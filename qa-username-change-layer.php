<?php

class qa_html_theme_layer extends qa_html_theme_base
{
    function form($form)
    {
        // Add note or lock field UI side
        if (qa_request_part(0) == 'account' && isset($form['fields']['handle'])) {
            $userid = qa_get_logged_in_userid();
            $allowed = (int)qa_opt('allowed_username_changes');
            $used = (int)qa_db_usermeta_get($userid, 'username_change_count');
            $remaining = max(0, $allowed - $used);

            if ($remaining <= 0 || $allowed == 0) {
                $form['fields']['handle']['type'] = 'static';
                $form['fields']['handle']['note'] =
                    '<span style="color:gray">Username locked — no more changes allowed.</span>';
            } else {
                $form['fields']['handle']['type'] = 'text';
                $form['fields']['handle']['note'] =
                    "You can change your username <strong>$remaining</strong> more time(s).";
            }
        }

        parent::form($form);
    }
}
