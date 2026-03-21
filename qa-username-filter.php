<?php
class qa_username_filter
{
    function filter_profile(&$profile, &$errors, $useraccount, $userprofile)
    {
        $userid = qa_get_logged_in_userid();
        if (!$userid) return;

        $posted_handle = trim(qa_post_text('handle'));
        if (!$posted_handle || $posted_handle === $useraccount['handle'])
            return; // nothing changed

        require_once dirname(__FILE__) . '/qa-username-helper.php';
        $check = qa_username_change_check($userid, $posted_handle);

        if ($check['status'] === 'limit')
            $errors['handle'] = qa_lang('qa_misc_lang/username_limit_reached');
        elseif ($check['status'] === 'taken')
            $errors['handle'] = qa_lang('users/handle_exists');
    }
}
