<?php

class qa_username_change_event
{
    function process_event($event, $userid, $handle, $cookieid, $params)
    {
        if ($event == 'u_save') {
            $posted_handle = qa_post_text('handle');
            $original_handle = $params['handle'] ?? $handle;

            if ($posted_handle && $posted_handle != $original_handle) {
                require_once dirname(__FILE__) . '/qa-username-helper.php';
                $check = qa_username_change_check($userid, $posted_handle);

                if ($check['status'] === 'ok') {
                    qa_db_user_set($userid, 'handle', $posted_handle);
                    qa_db_usermeta_set($userid, 'username_change_count', $check['used'] + 1);
                    qa_report_event(
                        'u_handle_change',
                        $userid,
                        $posted_handle,
                        qa_cookie_get(),
                        array(
                            'old_handle' => $original_handle,
                            'new_handle' => $posted_handle,
                        )
                    );
                }
            }
        }
    }
}
