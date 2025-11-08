<?php

class qa_username_change_event
{
    function process_event($event, $userid, $handle, $cookieid, $params)
    {
        if ($event == 'u_save') {            
            // Load meta info
            $used = (int)qa_db_usermeta_get($userid, 'username_change_count');
            $allowed = (int)qa_opt('allowed_username_changes');

            // If first save, we need to compare with pre-save handle
            // So we can look at $_POST['handle'] (since event runs within same POST)
            $posted_handle = qa_post_text('handle');
            $original_handle = $params['handle'] ?? $handle;

            if ($posted_handle && $posted_handle != $original_handle) {
                $remaining = max(0, $allowed - $used);
                if ($remaining > 0) {
					qa_db_user_set($userid, 'handle', $posted_handle);
                    qa_db_usermeta_set($userid, 'username_change_count', $used + 1);
                    //error_log("Username of userid '$userid' changed from $original_handle to $posted_handle (used: ".($used+1).")");
                } else {
                    // Block it immediately
                    qa_db_user_set($userid, 'handle', $original_handle);
                    error_log("Username change blocked for userid '$userid' - as limit reached");
                }
            }
        }
    }
}
