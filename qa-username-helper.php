<?php

if (!defined('QA_VERSION')) {
	header('Location: ../../');
	exit;
}

/**
 * Shared validation for username changes.
 *
 * @param mixed  $userid     The user ID attempting the change.
 * @param string $new_handle The desired new username.
 * @return array Keys: status ('ok'|'taken'|'limit'), remaining, allowed, used.
 */
function qa_username_change_check($userid, $new_handle)
{
	require_once QA_INCLUDE_DIR . 'db/metas.php';
	require_once QA_INCLUDE_DIR . 'db/users.php';

	$allowed   = (int)qa_opt('allowed_username_changes');
	$used      = (int)qa_db_usermeta_get($userid, 'username_change_count');
	$remaining = max(0, $allowed - $used);

	// Limit check first (covers both disabled and exhausted)
	if ($allowed == 0 || $remaining <= 0) {
		return array('status' => 'limit', 'remaining' => 0, 'allowed' => $allowed, 'used' => $used);
	}

	// Duplicate handle check
	$handleusers = qa_db_user_find_by_handle($new_handle);
	if (count($handleusers) && array_search($userid, $handleusers) === false) {
		return array('status' => 'taken', 'remaining' => $remaining, 'allowed' => $allowed, 'used' => $used);
	}

	return array('status' => 'ok', 'remaining' => $remaining, 'allowed' => $allowed, 'used' => $used);
}
