<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

return array(
    'profile_not_visible' => 'Profile unavailable — this account does not yet meet the visibility criteria.',
	'reorder_ask' => 'Reorder Ask form fields (Question Description → Question Title → Similar Questions.)',
	'redirection_fav' => 'Display lists redirection link at the questions section in the favorites page.',
	'minimum_active_profile_visible' => 'Minimum active days before profile is viewable for public (zero - for no restriction)',
	'minimum_active_profile_editable' => 'Minimum active days before user can edit their profile (zero - for no restriction)',
	'profile_not_editable' => 'Your account isn’t mature enough to edit the profile yet.',
	'redirection_fav_content' => 'Click here to browse your favorite questions by category',
);
