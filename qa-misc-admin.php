<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_misc_admin {

    function option_default($option) {
        if ($option == 'misc_tweaks_ask_reorder')
            return 0;
		if($option == 'add_list_link')
            return 0;
		if($option == 'misc_min_active_days_profile_visible')
            return 0;
		if($option == 'misc_min_active_days_profile_editable')
            return 0;
    }

    function admin_form(&$qa_content) {

        $saved = false;
        if (qa_clicked('misc_tweaks_save')) {
            qa_opt('misc_tweaks_ask_reorder', (int)qa_post_text('misc_tweaks_ask_reorder'));
			qa_opt('add_list_link', (int)qa_post_text('add_list_link'));
			qa_opt('misc_min_active_days_profile_visible', (int)qa_post_text('misc_min_active_days_profile_visible'));
			qa_opt('misc_min_active_days_profile_editable', (int)qa_post_text('misc_min_active_days_profile_editable'));
            $saved = true;
        }

        return array(
            'ok' => $saved ? 'Settings saved' : null,

            'fields' => array(
                array(
                    'label' => qa_lang_html('qa_misc_lang/reorder_ask'),
                    'type' => 'checkbox',
                    'value' => qa_opt('misc_tweaks_ask_reorder'),
                    'tags'  => 'name="misc_tweaks_ask_reorder"',
                ),
				array(
                    'label' => qa_lang_html('qa_misc_lang/redirection_fav'),
                    'type' => 'checkbox',
                    'value' => qa_opt('add_list_link'),
                    'tags'  => 'name="add_list_link"',
                ),
				array(
					'type'  => 'custom',
					'html'  => '<label>'
						. qa_lang_html('qa_misc_lang/minimum_active_profile_visible') . ' '
						. '<input type="number" name="misc_min_active_days_profile_visible" min="0" value="' . (int)qa_opt('misc_min_active_days_profile_visible') . '" />'
						. '</label>',
				),
				array(
					'type'  => 'custom', 
					'html'  => '<label>'
						. qa_lang_html('qa_misc_lang/minimum_active_profile_editable') . ' '
						. '<input type="number" name="misc_min_active_days_profile_editable" min="0" value="' . (int)qa_opt('misc_min_active_days_profile_editable') . '" />'
						. '</label>',
				),
            ),

            'buttons' => array(
                array(
                    'label' => 'Save',
                    'tags' => 'name="misc_tweaks_save"',
                ),
            ),
        );
    }
}
