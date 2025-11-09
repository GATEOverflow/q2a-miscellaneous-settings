<?php
class qa_username_filter
{
    function filter_profile(&$profile, &$errors, $useraccount, $userprofile)
    {
        $userid = qa_get_logged_in_userid();
        if (!$userid) return;

        $allowed   = (int)qa_opt('allowed_username_changes');
        $used      = (int)qa_db_usermeta_get($userid, 'username_change_count');
        $remaining = max(0, $allowed - $used);

        $posted_handle = trim(qa_post_text('handle'));
        if (!$posted_handle || $posted_handle === $useraccount['handle'])
            return; // nothing changed

        // ---- 2. duplicate check ----
        require_once QA_INCLUDE_DIR . 'db/users.php';
        $handleusers = qa_db_user_find_by_handle($posted_handle);
        if (count($handleusers) && array_search($userid, $handleusers) === false)
            $errors['handle'] = qa_lang('users/handle_exists');

        // ---- 3. limit check ----
        if ($remaining <= 0 || $allowed == 0)
            $errors['handle'] = ' Can\'t change the username as (limit reached)';

        // If we produced any errors, Q2A will automatically
        // redisplay the Account form with the message inline.
    }
}
