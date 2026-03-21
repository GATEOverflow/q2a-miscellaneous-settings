<?php

if (!defined('QA_VERSION')) {
	header('Location: ../../');
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
	function initialize()
	{
		parent::initialize();

		if ($this->template !== 'user')
			return;

		if (qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN)
			return;

		if (!qa_clicked('do_grant_username_change'))
			return;

		$handle = qa_request_part(1);
		if (!$handle)
			return;

		if (!qa_check_form_security_code('user-' . $handle, qa_post_text('code')))
			return;

		$userid = qa_handle_to_userid($handle);
		if (!$userid)
			return;

		require_once QA_INCLUDE_DIR . 'db/metas.php';
		$used = (int)qa_db_usermeta_get($userid, 'username_change_count');
		if ($used > 0) {
			qa_db_usermeta_set($userid, 'username_change_count', $used - 1);
		}

		qa_redirect(qa_request());
	}

	function main_parts($content)
	{
		if ($this->template === 'user' && qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
			$handle = qa_request_part(1);
			if ($handle) {
				$userid = qa_handle_to_userid($handle);
				if ($userid) {
					require_once QA_INCLUDE_DIR . 'db/metas.php';
					$allowed = (int)qa_opt('allowed_username_changes');
					$used    = (int)qa_db_usermeta_get($userid, 'username_change_count');

					if ($allowed > 0 && $used >= $allowed && isset($content['form_profile']['buttons'])) {
						$content['form_profile']['buttons']['grant_username'] = array(
							'tags' => 'name="do_grant_username_change"',
							'label' => qa_lang('qa_misc_lang/username_grant_button'),
						);

						if (!isset($content['form_profile']['hidden']['code'])) {
							$content['form_profile']['hidden']['code'] = qa_get_form_security_code('user-' . $handle);
						}
					}
				}
			}
		}

		parent::main_parts($content);
	}
}
