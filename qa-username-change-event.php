<?php

class qa_username_change_event
{
    function process_event($event, $userid, $handle, $cookieid, $params)
    {
        if ($event == 'u_save') {
            // Load meta info
            $used = (int)qa_db_usermeta_get($userid, 'username_change_count');
            $allowed = (int)qa_opt('allowed_username_changes');

            // If first save, we need to compare with pre-save handle. So we can look at $_POST['handle'] (since event runs within same POST)
            $posted_handle = qa_post_text('handle');
            $original_handle = $params['handle'] ?? $handle;
		
            if ($posted_handle && $posted_handle != $original_handle) {
				
                // As it is already checked whether the username is duplicate or not, allowed to change username or not through filter module, we can't directly update the username due to u_save event is triggered even there is no change in the username. So, we need to add conditions here again.
				
				require_once QA_INCLUDE_DIR . 'db/users.php';
				$handleusers = qa_db_user_find_by_handle($posted_handle);
				if (!(count($handleusers) && array_search($userid, $handleusers) === false)){			
					$remaining = max(0, $allowed - $used);
					if ($remaining > 0) {
						qa_db_user_set($userid, 'handle', $posted_handle);
						qa_db_usermeta_set($userid, 'username_change_count', $used + 1);
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
					} else {
						// Block it immediately
						qa_db_user_set($userid, 'handle', $original_handle);
						//error_log("Username change blocked for userid '$userid' - as limit reached");
					}
				}
            }
        }
    }
}
