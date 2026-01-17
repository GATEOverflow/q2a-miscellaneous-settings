<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

return array(
	// Admin page - Section titles
	'admin_section_title_general' => 'General:', // In case general options will be added later
	'admin_section_title_sidepanel' => 'Sidepanel:',
	'admin_section_title_profile' => 'Profile:',
	'admin_section_title_other' => 'Other:',
	
	// Admin options
	'opt_hide_sidepanel' => 'Add "Hide Sidepanel" toggle to the User Navigation.',
	
	'minimum_active_profile_visible' => 'Minimum active days before profile is viewable for public (zero - for no restriction)',
	'minimum_active_profile_editable' => 'Minimum active days before user can edit their profile (zero - for no restriction)',
	'opt_username_change' => 'Allowed number of username changes per user:',
	'opt_username_change_description' => 'Set to 0 to disable username change completely.',
	
	'reorder_ask' => 'Reorder Ask form fields (Question Description → Question Title → Similar Questions.)',
	'redirection_fav' => 'Display lists redirection link at the questions section in the favorites page.',
	'print_enable' => 'Enable Print Button on Question/Blog',
	'print_css' => 'Custom Print CSS',
	'print_css_description' => 'Full print stylesheet. Modify to change layout, fonts, etc.',
	
	// Frontend descriptions
	'profile_not_visible' => 'Profile unavailable — this account does not yet meet the visibility criteria.',
	'profile_not_editable' => 'Your account isn’t mature enough to edit the profile yet.',
	
	'redirection_fav_content' => 'Click here to browse your favorite questions by category',
	
	'print_popup' => 'Print',
	'print_label' => 'Print',
	'note_heading' => 'Self Note:',
);
