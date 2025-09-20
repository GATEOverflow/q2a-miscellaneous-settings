<?php
if (!defined('QA_VERSION')) {
	header('Location: ../../');
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
	private $cached_user_stats = [];
	private function get_user_activity_stats($userid)
	{
		// If already cached, just return it.
		if (isset($this->cached_user_stats[$userid])) {
			return $this->cached_user_stats[$userid];
		}

		// Otherwise, fetch from DB once.
		$row = qa_db_read_one_assoc(
			qa_db_query_sub('SELECT content FROM ^usermetas WHERE userid=# AND title="activedays"', $userid),
			true
		);
		$activedays = isset($row['content']) ? (int)$row['content'] : 0;

		$approved_content_count = (int)qa_db_read_one_value(
			qa_db_query_sub(
				'SELECT COUNT(*) FROM ^posts WHERE userid=# AND type IN ("Q","A","C") AND flagcount=0',
				$userid
			),
			true
		);
		//error_log("hello");

		// Save in cache for reuse
		$this->cached_user_stats[$userid] = [$activedays, $approved_content_count];
		return $this->cached_user_stats[$userid];
	}

	public function main_parts($content) {
		if($this ->  template != 'user') {
			parent::main_parts($content);
			return;
		}
		$limitdays_profile_edit = (int)qa_opt('misc_min_active_days_profile_editable');
		if (($this->template === 'user' || $this->template === 'account') && $limitdays_profile_edit>0 ) {
			// Hide the Edit button and My Account link on user pages, and block access to the account page if restrictions are not met.

			$userid = qa_get_logged_in_userid();
			if ($userid){

				list($activedays, $approved_content_count) = $this->get_user_activity_stats($userid);

				if ($approved_content_count < 2 && $activedays < $limitdays_profile_edit) {
					if (isset($this->content['form_profile']['buttons']['account'])) {
						unset($this->content['form_profile']['buttons']['account']);
					}
					if (isset($this->content['navigation']['sub']['account'])) {
						unset($this->content['navigation']['sub']['account']);
					}
					if($this->template === 'account'){
						$this->content = qa_content_prepare();
						$this->content['error'] = qa_lang_html('qa_misc_lang/profile_not_editable');
					}						
				}
				else {
					parent::main_parts($content);
					return;
				}
			}
		}

		$limitdays_profile_hide = (int)qa_opt('misc_min_active_days_profile_visible');
		if ($this->template === 'user' && $limitdays_profile_hide>0 ) {
			$handle = qa_request_part(1);
			if (!$handle) return;

			$currentuserid = qa_get_logged_in_userid();
			$currentlevel  = qa_get_logged_in_level();

			// if admin/moderator etc. always allow
			if ($currentlevel >= QA_USER_LEVEL_MODERATOR) {
				parent::main_parts($content);
			}

			$userid = qa_handle_to_userid($handle);
			if (!$userid || $userid === $currentuserid) {
				parent::main_parts($content);
			}

			list($activedays, $approved_content_count) = $this->get_user_activity_stats($userid);

			if ($approved_content_count < 2 && $activedays < $limitdays_profile_hide) {
			//	$content = qa_content_prepare();
				$content['error'] = qa_lang_html('qa_misc_lang/profile_not_visible');
				return;
			}
			else {
				parent::main_parts($content);
				return;
			}
		}
		parent::main_parts($content);
	}
}
